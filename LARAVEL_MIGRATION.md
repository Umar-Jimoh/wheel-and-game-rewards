# Migrating this app to Laravel

This document is a guide, not a finished migration — it describes **how** to port
this Symfony app to Laravel without ever touching `src/Domain/` or
`src/Application/`. If you find yourself editing either of those directories while
following this guide, something has gone wrong: the entire premise of hexagonal
architecture is that swapping the framework only touches `Infrastructure/` and `UI/`.

## The one invariant

Copy `src/Domain/` and `src/Application/` into the Laravel app unchanged —
`app/Domain/` and `app/Application/`, same namespaces or renamed via `composer.json`
autoload, doesn't matter, as long as no file's *content* changes. Everything below is
about rebuilding `Infrastructure/` and `UI/` on top of them.

## 1. Output port adapters (`Port/Out/*` → Eloquent)

Every output port gets a Laravel-native adapter implementing the same interface.
Nothing about the interfaces changes — only what's on the other side of them.

| Port | Symfony adapter (Doctrine) | Laravel adapter (Eloquent) |
|---|---|---|
| `UserRepository` | `DoctrineUserRepository` | `EloquentUserRepository` |
| `SpinRepository` | `DoctrineSpinRepository` | `EloquentSpinRepository` |
| `PendingGameRepository` | `DoctrinePendingGameRepository` | `EloquentPendingGameRepository` |
| `RandomMultiplierGenerator` | `RandomMultiplierGenerator` (plain PHP, `random_int`) | same code, no framework dependency to swap |
| `RandomNumberGenerator` | `RandomNumberGenerator` (plain PHP, `random_int`) | same code, no framework dependency to swap |

The two random-generator adapters need **no changes at all** — they were already
framework-agnostic plain PHP classes (`random_int()` is a PHP built-in, not a Symfony
one). Copy them over as-is. Only the three repositories, which depend on an ORM,
need a real rewrite.

### Example: `UserRepository`

Eloquent model (new — Doctrine never needed one, since mapping lived in XML):

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'users';
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'points' => \App\Infrastructure\Persistence\Eloquent\Cast\PointCast::class,
        ];
    }
}
```

Adapter implementing the port, translating between the Eloquent model and the
Domain `User`:

```php
namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Port\Out\UserRepository;
use App\Domain\Model\User;
use App\Models\UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function findById(int $id): ?User
    {
        $row = UserModel::find($id);

        if ($row === null) {
            return null;
        }

        return new User($row->id, $row->name, $row->points);
    }

    public function save(User $user): void
    {
        UserModel::query()->updateOrCreate(
            ['id' => $user->getId()],
            ['name' => $user->getName(), 'points' => $user->getBalance()],
        );
    }
}
```

This is the one structural difference worth calling out: **Doctrine hydrates the
Domain entity directly** (via Reflection, per `User.orm.xml`) — there's no separate
"Eloquent model" class, `User` *is* the persisted object. Eloquent doesn't support
mapping an arbitrary class this way; it needs its own `Model` subclass. So the
Laravel adapter is a genuine translation layer between an `UserModel` (Eloquent) and
a `User` (Domain) — slightly more code than the Doctrine side, but it's exactly the
kind of thing output-port adapters exist to absorb, and `Domain\User` still never
hears about Eloquent.

`EloquentSpinRepository` and `EloquentPendingGameRepository` follow the identical
shape: an `SpinModel`/`PendingGameModel` with a `game`/`spin_cost`/etc. attribute
cast via `GameCast`/`PointCast`/`MultiplierCast` (section 2), and a repository
translating model ↔ domain-object. `takeFor()`'s atomic fetch-and-delete becomes:

```php
public function takeFor(int $userId): ?PendingGame
{
    return \DB::transaction(function () use ($userId) {
        $row = PendingGameModel::where('user_id', $userId)->first();

        if ($row === null) {
            return null;
        }

        // $row->game is already a Game instance here — GameCast converted it on read
        $pendingGame = new PendingGame($row->user_id, $row->game);
        $row->delete();

        return $pendingGame;
    });
}
```

## 2. The value-object casting problem (`PointType`/`MultiplierType`/`GameType` → Eloquent casts)

Doctrine needed `PointType`/`MultiplierType`/`GameType` (custom DBAL types) purely
because `Point`, `Multiplier`, and `Game` aren't scalars — something has to convert
them to/from plain DB columns without `Domain/` importing Doctrine. Eloquent's
equivalent mechanism is **custom casts** (`Illuminate\Contracts\Database\Eloquent\CastsAttributes`).

```php
namespace App\Infrastructure\Persistence\Eloquent\Cast;

use App\Domain\Model\Point;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class PointCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): Point
    {
        return new Point((int) $value);
    }

    public function set($model, string $key, $value, array $attributes): int
    {
        return $value->getAmount();
    }
}
```

Direct parallel: `convertToPHPValue`/`convertToDatabaseValue` on the Doctrine side
become `get`/`set` here. `MultiplierCast` and `GameCast` follow the same shape as
`MultiplierType`/`GameType` — `GameCast::set()` does the same `json_encode($value->getNumbers())`,
`GameCast::get()` the same `new Game(json_decode($value, true))`.

One difference worth knowing: Eloquent casts are declared per-model (in `casts()`,
as shown on `UserModel` above), not registered globally the way `doctrine.yaml`'s
`dbal: types:` section was — there's no central registry to update.

## 3. Wiring (`services.yaml` → a service provider)

Symfony's port→adapter aliases:

```yaml
App\Application\Port\Out\UserRepository: '@App\Infrastructure\Persistence\Doctrine\DoctrineUserRepository'
```

become container bindings in `AppServiceProvider::register()`:

```php
public function register(): void
{
    $this->app->bind(
        \App\Application\Port\Out\UserRepository::class,
        \App\Infrastructure\Persistence\Eloquent\EloquentUserRepository::class,
    );
    $this->app->bind(
        \App\Application\Port\Out\SpinRepository::class,
        \App\Infrastructure\Persistence\Eloquent\EloquentSpinRepository::class,
    );
    $this->app->bind(
        \App\Application\Port\Out\PendingGameRepository::class,
        \App\Infrastructure\Persistence\Eloquent\EloquentPendingGameRepository::class,
    );
    $this->app->bind(
        \App\Application\Port\Out\RandomMultiplierGenerator::class,
        \App\Infrastructure\Random\RandomMultiplierGenerator::class,
    );
    $this->app->bind(
        \App\Application\Port\Out\RandomNumberGenerator::class,
        \App\Infrastructure\Random\RandomNumberGenerator::class,
    );

    $this->app->bind(
        \App\Application\Port\In\SpinWheelUseCase::class,
        \App\Application\UseCase\SpinWheel::class,
    );
    $this->app->bind(
        \App\Application\Port\In\GetBalanceUseCase::class,
        \App\Application\UseCase\GetBalance::class,
    );
    $this->app->bind(
        \App\Application\Port\In\PlayGameUseCase::class,
        \App\Application\UseCase\PlayGame::class,
    );
    $this->app->bind(
        \App\Application\Port\In\StartGameUseCase::class,
        \App\Application\UseCase\StartGame::class,
    );
}
```

Symfony's `App\: resource: '../src/'` autowires every class as a service by default,
which is why `services.yaml` only needed to list the *interface* aliases — concrete
classes like `SpinWheel` were already implicitly registered. Laravel's container
needs every binding stated explicitly like this (there's no equivalent autodiscovery
for arbitrary classes), so the input-port bindings above, which `services.yaml`
never had to spell out, do need to be added here.

## 4. CLI (`Symfony Console` → `Artisan`)

| Symfony command | Artisan equivalent |
|---|---|
| `wheel:spin <userId> <cost>` | `php artisan wheel:spin {userId} {cost}` |
| `user:balance <userId>` | `php artisan user:balance {userId}` |
| `game:play <userId>` | `php artisan game:play {userId}` |

```php
namespace App\Console\Commands;

use App\Application\Port\In\GetBalanceUseCase;
use Illuminate\Console\Command;

class GetBalanceCommand extends Command
{
    protected $signature = 'user:balance {userId}';

    public function __construct(private readonly GetBalanceUseCase $getBalanceUseCase)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->getBalanceUseCase->execute((int) $this->argument('userId'));

        if (! $result->success) {
            $this->error($result->errorMessage);
            return self::FAILURE;
        }

        $this->info("💰 User {$this->argument('userId')} has a balance of {$result->balance} points.");
        return self::SUCCESS;
    }
}
```

The interactive `game:play` command is the one with the most surface area to port —
Symfony's `SymfonyStyle` (`->title()`, `->table()`, `->ask()` with a validator
closure, `->choice()`, `->confirm()`) maps onto Laravel's `Command` helpers
(`$this->table()`, `$this->ask()`, `$this->choice()`, `$this->confirm()`); Artisan's
`$this->ask()` doesn't take a validator closure the way Symfony's does, so the
position-range check has to become a manual loop instead of relying on Symfony's
built-in retry-on-exception behavior.

## 5. HTTP (`Symfony routes/controllers` → `Laravel routes/controllers`)

| Route | Symfony controller | Laravel equivalent |
|---|---|---|
| `POST /users/{userId}/wheel/spin` | `SpinWheelController` | `SpinWheelController@__invoke` |
| `GET /users/{userId}/balance` | `GetBalanceController` | `GetBalanceController@__invoke` |
| `POST /users/{userId}/games/start` | `StartGameController` | `StartGameController@__invoke` |
| `POST /users/{userId}/games/play` | `PlayGameController` | `PlayGameController@__invoke` |

`routes/api.php`:

```php
Route::post('/users/{userId}/wheel/spin', SpinWheelController::class);
Route::get('/users/{userId}/balance', GetBalanceController::class);
Route::post('/users/{userId}/games/start', StartGameController::class);
Route::post('/users/{userId}/games/play', PlayGameController::class);
```

### Request validation: `#[MapRequestPayload]` + `symfony/validator` → Form Requests

`PlayGameInput`'s `#[Assert\Range(min: 0, max: 4)]` on `position` becomes a Laravel
Form Request:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'position' => ['required', 'integer', 'between:0,4'],
            'parity' => ['required', 'in:even,odd'],
        ];
    }
}
```

```php
namespace App\Http\Controllers;

use App\Application\Port\In\PlayGameUseCase;
use App\Domain\Model\Parity;
use App\Http\Requests\PlayGameRequest;

class PlayGameController
{
    public function __construct(private readonly PlayGameUseCase $playGameUseCase) {}

    public function __invoke(int $userId, PlayGameRequest $request)
    {
        $result = $this->playGameUseCase->play(
            $userId,
            $request->integer('position'),
            Parity::from($request->input('parity')),
        );

        if (! $result->success) {
            return response()->json(['error' => $result->errorMessage], 404);
        }

        return response()->json([
            'won' => $result->won,
            'earned' => $result->earned,
            'balance' => $result->balance,
        ]);
    }
}
```

A failed Form Request automatically returns `422` with a JSON body shaped like
`{"message": "...", "errors": {...}}` for JSON requests — conceptually the same
outcome as `JsonExceptionListener` unpacking `ValidationFailedException`, just
Laravel's default behavior rather than something we had to build.

### Exception handling: `JsonExceptionListener` → the exception handler

`JsonExceptionListener` existed because Symfony's default behavior for an uncaught
`HttpExceptionInterface` is an HTML debug page, which doesn't fit a JSON API.
Laravel's default behavior is the opposite — it already returns JSON automatically
for any request where `Accept: application/json` is sent (or any request hitting an
`api.php` route), so **this adapter may not be needed at all**. If you want to force
every error into the exact same `{"error": "..."}` shape regardless of exception
type, `bootstrap/app.php`'s `withExceptions()` callback is the place:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Throwable $e, $request) {
        if ($request->expectsJson()) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
})
```

## 6. Migrations (`doctrine:migrations:*` → Laravel migrations)

Column-for-column, the three tables carry over directly:

```php
Schema::create('users', function (Blueprint $table) {
    $table->integer('id')->primary();   // NONE generator — app-assigned, not auto-increment
    $table->string('name');
    $table->integer('points');
});

Schema::create('spins', function (Blueprint $table) {
    $table->id();                        // AUTO generator — Laravel's auto-increment default
    $table->integer('user_id');
    $table->integer('spin_cost');
    $table->integer('multiplier_rolled');
    $table->integer('rewarded_point');
});

Schema::create('pending_games', function (Blueprint $table) {
    $table->id();
    $table->integer('user_id');
    $table->text('game');               // JSON-encoded numbers, same as GameType
});
```

Worth carrying over deliberately: `User`'s id is `NONE` (app-assigned) in
`User.orm.xml`, which is why its migration uses a plain `integer()->primary()`
rather than `id()` — getting this wrong (defaulting to auto-increment) would
silently break anything that assigns a specific user id.

Note the column names above are snake_case (`user_id`, `spin_cost`, ...) rather than
the Doctrine side's camelCase (`userId`, `spinCost`, ...) — this is a fresh schema
for a fresh database, not a data migration from the existing SQLite file, so there's
no requirement to match column names exactly. snake_case matches Laravel/Eloquent
convention, which is why the adapter examples above (`$row->user_id`, not
`$row->userId`) follow it.

## Suggested porting order

Mirrors how this app was originally built, for the same reason: start with the
slice that has the least surface area, so the approach is proven before tackling
anything stateful or multi-step.

1. **`GetBalance`** — read-only, no write-side adapter complexity. Prove
   `EloquentUserRepository` + the `user:balance` Artisan command + the HTTP
   endpoint all work before anything else.
2. **`SpinWheel`** — introduces the `Spin` recording side and `MultiplierCast`.
3. **`PlayGame`/`StartGame`** — the most involved slice (two use cases, `GameCast`,
   the atomic `takeFor()` semantics). Do this last.

After each slice, run the existing PHPUnit tests for `Domain`/`Application` (they
should pass completely unmodified — any failure there means something in
`Domain`/`Application` changed, which it shouldn't have) and write new tests for the
Laravel-side adapters/controllers, mirroring the existing Symfony-side test
coverage for that slice.
