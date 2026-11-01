# Spin Wheel Reward System

A small reward system (spin-a-wheel + a number-guessing game) built in Symfony to
learn **Hexagonal Architecture (Ports & Adapters)**, with Phase 3 planned as a
migration to Laravel that only swaps adapters — Domain and Application code stay
untouched. See [CLAUDE.md](CLAUDE.md) for the architecture breakdown and current
build status, and [docs/prd.md](docs/prd.md) for full requirements.

## Setup

```bash
composer install
php bin/console doctrine:migrations:migrate --no-interaction
```

This creates a local SQLite database at `var/data_dev.db` (configured via
`DATABASE_URL` in `.env`).

## Seeding a user

There's no "create user" endpoint — seed one directly with SQL:

```bash
sqlite3 var/data_dev.db "INSERT INTO users (id, name, points) VALUES (1, 'Ada', 500);"
```

## Running the CLI

```bash
php bin/console wheel:spin <userId> <cost>    # spin the wheel (cost: 500, 1000, or 2000)
php bin/console user:balance <userId>         # view balance
php bin/console game:play <userId>            # play the number-guessing game (interactive)
```

Every command supports `--help` for free (built into Symfony Console), e.g.:

```bash
php bin/console wheel:spin --help
```

## Running the HTTP API

```bash
symfony server:start   # or: php -S 127.0.0.1:8000 -t public
```

| Method | Path | Body |
|---|---|---|
| `POST` | `/users/{userId}/wheel/spin` | `{"spinCost": 500\|1000\|2000}` |
| `GET` | `/users/{userId}/balance` | — |
| `POST` | `/users/{userId}/games/start` | — |
| `POST` | `/users/{userId}/games/play` | `{"position": 0-4, "parity": "even"\|"odd"}` |

All responses are JSON. Failures (invalid user, invalid state, validation errors)
return `{"error": "..."}` with a `404` or `422` status instead of throwing.

The number-guessing game is a two-step flow: call `/games/start` first to get the 5
drawn numbers, then `/games/play` with your guess. The pending game is stored
server-side between the two requests (in the database, so it survives across
separate HTTP requests).

## Running tests

```bash
./bin/phpunit
```

## Database changes

Schema changes go through migrations, not `doctrine:schema:create`/`update`:

```bash
php bin/console doctrine:migrations:diff      # generate a migration from mapping changes
php bin/console doctrine:migrations:migrate   # apply pending migrations
php bin/console doctrine:migrations:status    # see what's applied/pending
```
