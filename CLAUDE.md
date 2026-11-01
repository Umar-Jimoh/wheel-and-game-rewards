# Spin Wheel Reward System — Hexagonal Architecture

## Purpose

This is a **learning project**, not a production app. The user is using it to learn
Hexagonal Architecture (Ports & Adapters) by building a small Spin Wheel Reward
System in Symfony, then migrating it to Laravel **without touching Domain or
Application code** — only swapping adapters. Full requirements: [docs/prd.md](docs/prd.md).

**How to collaborate on this repo:** guide and review, don't write implementations
proactively. The user is building this themselves to learn the pattern. Point out
what's missing, explain *why* something belongs in a given layer, and review code
they've written — but let them write it. Only write code if they explicitly ask you to.

## Architecture

```
src/
  Domain/            # Pure business logic. Zero framework/infrastructure dependencies.
    Model/            - Entities (identity, mutable) and Value Objects (immutable, equality by value)
    Exception/         - Domain-specific exceptions (business rule violations)
  Application/        # Orchestrates domain objects to fulfill use cases. Framework-agnostic.
    Port/In/           - Input port interfaces (use case contracts, implemented by UseCase classes)
    Port/Out/           - Output port interfaces (what the use case needs from the outside world)
    UseCase/            - Use case implementations (the application's input ports)
    Dto/                - Data transfer objects returned by use cases (decouples core from adapters)
    Dto/Request/        - Input DTOs populated from HTTP request bodies via #[MapRequestPayload]
  Infrastructure/     # Secondary/driven adapters — implement Port/Out interfaces
    Persistence/InMemory/ - In-memory repository adapters (superseded by Doctrine, Phase 2)
    Persistence/Doctrine/  - Doctrine-backed repository adapters + custom DBAL types
    Random/                - Random number generation adapter
  UI/                 # Primary/driving adapters — call Port/In interfaces
    Cli/Command/        - Symfony Console command adapters
    Http/                - Symfony HTTP controllers (routing attributes directly on the class/method)
    Http/EventListener/   - Kernel event listeners (e.g. mapping exceptions to JSON responses)
```

**Dependency rule:** Domain depends on nothing. Application depends only on Domain.
Infrastructure and UI depend on Application's ports (interfaces) but Application
never depends on Infrastructure or UI. No Symfony/Laravel/Doctrine/Eloquent/HTTP/CLI
types may appear in `Domain/` or `Application/` (PRD §14).

**Entities vs Value Objects:** Entities (`User`) have identity and may be mutable.
Value Objects (`Point`, `SpinCost`, `Multiplier`) have no identity, are immutable,
and are equal purely by value. See the docblock in [User.php](src/Domain/Model/User.php)
for the reasoning — it's a good reference for this distinction elsewhere in the project.

## Current state vs PRD

### Built (Spin Wheel flow, end-to-end)

- Domain: `User`, `Point`, `SpinCost`, `Multiplier` enum, `InsufficientPointsException`, `SpinCostException`
- Application: `SpinWheelUseCase` port, `SpinWheel` use case, `SpinWheelResult` DTO, `UserRepository` / `RandomMultiplierGenerator` output ports
- Infrastructure: `InMemoryUserRepository`, `RandomMultiplierGenerator` (uses `random_int`)
- UI: `SpinWheelCommand` (`php bin/console wheel:spin <userId> <cost>`)
- Tests exist for all of the above (mirrors `src/` structure under `tests/`)

### Built (Get Balance, end-to-end)

- Application: `GetBalanceUseCase` port, `GetBalance` use case, `GetBalanceResult` DTO
- UI: `GetBalanceCommand` (`php bin/console user:balance <userId>`) — matches PRD §8
- UI: `GetBalanceController` (`GET /users/{userId}/balance`)
- Tests exist for the use case, the command, and the controller

### Built (Spin recording — BR-005)

- Domain: `Spin` (userId, `SpinCost`, `Multiplier` rolled, rewarded `Point`)
- Application: `SpinRepository` output port
- Infrastructure: `InMemorySpinRepository`
- `SpinWheel::execute()` now records a `Spin` on every spin via `SpinRepository::save()`
- UI: `SpinWheelController` (`POST /users/{userId}/wheel/spin`)
- Tests exist for `Spin`, the repository, and the controller

### PRD deviation: Task → Game

The original PRD (§4.2) had an admin-defined static task list ("Daily Login" → 50pts,
etc.) — dropped in favor of a **system-generated number-guessing game**, since there's
no admin in scope to define tasks. `docs/prd.md` has been updated to reflect this
(§3, §4.2, §5 BR-006/BR-007, §6 UC-001, §7, §8, §9, §10, §11, §12). Mechanic:

- System generates 5 random numbers.
- User picks one of the 5 positions and guesses odd or even for the number there.
- Free to play (BR-006) — no points deducted either way.
- Correct guess → fixed reward (BR-007). Incorrect guess → nothing awarded, nothing lost.

### Built (Play Game, end-to-end — two-step start/play flow)

Playing is split into two steps so an HTTP client can see the drawn numbers before
guessing, with the server holding the pending draw between requests:

- Domain: `Game` (holds the 5 drawn numbers, validates them, evaluates a guess),
  `Parity` enum, `PendingGame` (userId + a `Game`, the draw awaiting a guess)
- Application:
  - `StartGameUseCase` port, `StartGame` use case, `StartGameResult` DTO — validates
    the user, generates the numbers, persists a `PendingGame`, returns the numbers
  - `PlayGameUseCase` port, `PlayGame` use case, `PlayGameResult` DTO — takes the
    user's `PendingGame` via `PendingGameRepository::takeFor()` and evaluates the guess
    against it (restoring the pending game if the position is out of range, so a bad
    guess doesn't burn the draw)
  - `RandomNumbersResult` DTO, `RandomNumberGenerator` output port,
    `PendingGameRepository` output port
- Infrastructure: `RandomNumberGenerator` adapter (uses `random_int(0, 9)` × 5),
  `InMemoryPendingGameRepository`
- UI: `PlayGameCommand` (`php bin/console game:play <userId>`) — interactive, calls
  `StartGameUseCase` then `PlayGameUseCase` within one process
- UI: `StartGameController` (`POST /users/{userId}/games/start`),
  `PlayGameController` (`POST /users/{userId}/games/play`, body via `PlayGameInput` +
  `#[MapRequestPayload]`, `position` validated 0–4 via `symfony/validator`)
- Tests exist for `Game`, `PendingGame`, `PlayGame`/`StartGame` use cases, the
  `RandomNumberGenerator`/`PendingGameRepository` adapters, `PlayGameCommand`, and
  both controllers

### Built (Phase 2 — Doctrine persistence)

`InMemoryUserRepository`/`InMemorySpinRepository`/`InMemoryPendingGameRepository` are
replaced by Doctrine-backed adapters, with Domain classes staying completely
Doctrine-free (PRD §14) by mapping via XML files, not attributes:

- `doctrine/orm`, `doctrine/doctrine-bundle`, `doctrine/doctrine-migrations-bundle`;
  SQLite for local dev (`DATABASE_URL` in `.env`)
- XML entity mappings under `config/doctrine/` (`User.orm.xml`, `Spin.orm.xml`,
  `PendingGame.orm.xml`) — YAML mapping isn't available (`doctrine/orm` 3.x dropped it)
- Custom DBAL types (`Infrastructure/Persistence/Doctrine/Type/`) convert value objects
  to plain columns without Doctrine ever appearing in Domain: `PointType` (`Point` ↔
  int), `MultiplierType` (`Multiplier` enum ↔ int), `GameType` (`Game`'s 5 numbers ↔
  JSON string)
- `Spin`/`PendingGame` each gained a persistence-only `private ?int $id = null` +
  `getId()` — Doctrine hydrates by Reflection, so the identity property must physically
  exist on the class even though nothing in Domain/Application ever reads it
- `DoctrineUserRepository`, `DoctrineSpinRepository`, `DoctrinePendingGameRepository`
  implement the existing output ports; `services.yaml` now aliases to these instead of
  the in-memory adapters
- Migrations live in `src/Migrations/` (`doctrine:migrations:diff` / `:migrate`) —
  schema changes are no longer applied via `schema:create`/`schema:update`

**This also fixes the earlier known limitation**: `PendingGame` state previously
couldn't survive across two separate HTTP requests (each request boots a fresh
container, so in-memory state was lost between `/games/start` and `/games/play`).
With SQLite-backed persistence this now works correctly — verified by hitting both
endpoints as genuinely separate curl processes.

**Still missing**: there's no `CreateUser` use case or endpoint anywhere, so a real
user has to be seeded directly into the database to test any flow manually.

### Built (HTTP error handling)

- `JsonExceptionListener` (`UI/Http/EventListener`) — catches any
  `HttpExceptionInterface` on `kernel.exception` and returns the same
  `{"error": ...}` JSON shape the controllers use, including unpacking
  `ValidationFailedException` violation messages (e.g. the `position` range check)
  instead of Symfony's default HTML debug page

### Not yet built (per PRD)

- **`Wheel` and `Reward` domain models** (PRD §9) — currently `Multiplier::applyTo()`
  on `Point` does the reward calculation inline; there's no explicit `Wheel` or `Reward` object
- `CreateUser` use case/endpoint — no way to create a real user through the app itself
- Laravel migration (Phase 3) — not started; still pure Symfony

## Conventions observed in this codebase

- `declare(strict_types=1)` in every file
- Ports named by role: `Port/In/*UseCase` (input/driving), `Port/Out/*` (output/driven)
- Use cases return a DTO (e.g. `SpinWheelResult::success()` / `::failure()`) rather than
  throwing to the caller for expected failure cases (invalid user, insufficient points,
  invalid spin cost) — exceptions are caught inside the use case and converted to a
  failure DTO. Reserve real exceptions for programming errors / truly exceptional paths.
- Domain exceptions extend `DomainException` and carry a descriptive message — fixed
  where there's nothing case-specific to add, or built from constructor data when it
  helps (e.g. `SpinCostException` lists the actual allowed spin costs)
- Value objects validate invariants in the constructor and throw immediately (e.g. `Point`
  rejects negative amounts, `SpinCost` rejects amounts outside the allowed list)
- Tests live under `tests/` mirroring `src/` 1:1, use PHPUnit, one `test*` method per behavior

## Business rules (PRD §5) — quick reference

| ID | Rule | Enforced by |
|----|------|-------------|
| BR-001 | Cannot spin with insufficient points | `Point::subtract()` throws `InsufficientPointsException` |
| BR-002 | Spin cost must be 500 / 1000 / 2000 | `SpinCost` constructor throws `SpinCostException` |
| BR-003 | Multiplier must be None/2x/3x/4x | `Multiplier` enum (closed set by construction) |
| BR-004 | Reward added immediately after spin | `SpinWheel::execute()` calls `$user->addPoints($reward)` |
| BR-005 | Every spin must be recorded | `SpinWheel::execute()` calls `SpinRepository::save()` with a `Spin` record |
| BR-006 | Playing the game is free (no points deducted either way) | `PlayGame::play()` never deducts; a loss leaves the balance untouched |
| BR-007 | Correct guess → fixed reward; incorrect → nothing | `PlayGame::play()` awards a fixed `Point(100)` on a win via `Game::evaluate()`, nothing on a loss |

## Commands

```
php bin/console wheel:spin <userId> <cost>    # spin the wheel
php bin/console user:balance <userId>         # view balance
php bin/console game:play <userId>            # play the number-guessing game (interactive)
composer install
./bin/phpunit                                 # run tests
php bin/console doctrine:migrations:migrate   # apply pending DB migrations
```

## HTTP endpoints

```
POST /users/{userId}/wheel/spin    body: {"spinCost": 500|1000|2000}
GET  /users/{userId}/balance
POST /users/{userId}/games/start
POST /users/{userId}/games/play    body: {"position": 0-4, "parity": "even"|"odd"}
```

All return JSON; failures (invalid user, invalid state, validation errors) are
`{"error": "..."}` with a 404/422 status rather than throwing. The two-step game
flow now works correctly across separate requests since Phase 2 persistence landed.
