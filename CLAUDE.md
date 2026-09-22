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
  Infrastructure/     # Secondary/driven adapters — implement Port/Out interfaces
    Persistence/InMemory/ - In-memory repository adapters (Phase 1 persistence per PRD §13)
    Random/                - Random number generation adapter
  UI/                 # Primary/driving adapters — call Port/In interfaces
    Cli/Command/        - Symfony Console command adapters
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
- UI: `GetBalanceCommand` (`php bin/console balance:get <userId>`) — note the PRD (§8)
  names this `user:balance`; the implemented command name diverges from the spec
- Tests exist for the use case and the command

### PRD deviation: Task → Game

The original PRD (§4.2) had an admin-defined static task list ("Daily Login" → 50pts,
etc.) — dropped in favor of a **system-generated number-guessing game**, since there's
no admin in scope to define tasks. `docs/prd.md` has been updated to reflect this
(§3, §4.2, §5 BR-006/BR-007, §6 UC-001, §7, §8, §9, §10, §11, §12). Mechanic:

- System generates 5 random numbers.
- User picks one of the 5 positions and guesses odd or even for the number there.
- Free to play (BR-006) — no points deducted either way.
- Correct guess → fixed reward (BR-007). Incorrect guess → nothing awarded, nothing lost.

Not yet implemented: `Game` domain model, `PlayGameUseCase` port, `PlayGame` use case,
`RandomNumberGenerator` output port, `game:play` CLI command.

### Not yet built (per PRD)

- **PlayGame** use case + `game:play` CLI command (PRD §4.2, §6, §10) — no `Game`
  domain model or `RandomNumberGenerator` output port yet (see deviation note above)
- **Spin recording (BR-005)**: "every spin must be recorded" is *not implemented* —
  `SpinWheel::execute()` doesn't create/persist a `Spin` record. No `Spin` domain model
  or `SpinRepository` port/adapter exists yet.
- **`Wheel` and `Reward` domain models** (PRD §9) — currently `Multiplier::applyTo()`
  on `Point` does the reward calculation inline; there's no explicit `Wheel` or `Reward` object
- HTTP adapters (controllers for `POST /games/play`, `POST /wheel/spin`, `GET /users/{id}/balance`) — CLI only so far
- Laravel migration (Phase 3) — not started; still pure Symfony

## Conventions observed in this codebase

- `declare(strict_types=1)` in every file
- Ports named by role: `Port/In/*UseCase` (input/driving), `Port/Out/*` (output/driven)
- Use cases return a DTO (e.g. `SpinWheelResult::success()` / `::failure()`) rather than
  throwing to the caller for expected failure cases (invalid user, insufficient points,
  invalid spin cost) — exceptions are caught inside the use case and converted to a
  failure DTO. Reserve real exceptions for programming errors / truly exceptional paths.
- Domain exceptions extend `DomainException` and carry a fixed, descriptive message
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
| BR-005 | Every spin must be recorded | **Not yet implemented** — no `Spin` model/repository |
| BR-006 | Playing the game is free (no points deducted either way) | **Not yet implemented** — no `Game`/`PlayGame` yet |
| BR-007 | Correct guess → fixed reward; incorrect → nothing | **Not yet implemented** — no `Game`/`PlayGame` yet |

## Commands

```
php bin/console wheel:spin <userId> <cost>    # spin the wheel
php bin/console balance:get <userId>          # view balance (PRD calls this user:balance)
composer install
./bin/phpunit                                 # run tests
```
