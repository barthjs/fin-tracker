# Contributing

This guide shows how to develop Fin‑Tracker and the checks you must pass before opening a PR.

## Project overview

Fin‑Tracker is a Laravel application powered by Filament. The project is fully containerized for a consistent developer
experience. Development and deployment are expected to happen inside the provided Docker images, which bundle PHP, Node
and system dependencies.

Tech stack:

- [PHP 8.5](https://php.net/)
- [Laravel 12](https://laravel.com/)
- [Filament 4](https://filamentphp.com/)
- [PostgreSQL](https://www.postgresql.org/)

## Ways to contribute

- Report bugs and request features via GitHub Issues
- Fix bugs, implement enhancements, add tests
- Add translations

Before starting on larger work, please open an issue to discuss your approach.

## Development setup

Prerequisites:

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

The repository includes a development Dockerfile and a helper script that prepares everything (containers, dependencies,
database, demo data and assets).

Quick start:

```shell
git clone https://github.com/barthjs/fin-tracker
cd fin-tracker
./setup-dev.sh
```

This will:

- Build and start the dev containers defined in [compose.dev.yaml](compose.dev.yaml)
- Install composer and npm dependencies
- Run database migrations and seed demo data
- Publish Livewire/Filament assets

Customize the environment via [.env.development](.env.development).

App URL: http://localhost

Default admin credentials:

- Username: `admin`
- Password: `admin`

Default demo user credentials:

- Username: `user`
- Password: `user`

Common container commands:

```shell
docker compose -f compose.dev.yaml up -d
docker compose -f compose.dev.yaml down
docker compose -f compose.dev.yaml restart
docker compose -f compose.dev.yaml stop

# Shell into the app container as the application user 
docker exec -it -u application fin-tracker bash
```

## Composer scripts

The project defines Composer scripts to standardize quality checks and local workflows.

```shell
# Fix code style with Laravel Pint
composer lint

# Run the full test suite
composer test

# Individual test phases
composer test:type-coverage   # Pest type coverage test
composer test:unit            # Pest feature and unit tests
composer test:lint            # Pint in --test mode
composer test:types           # PHPStan static analysis

# Update PHP & JS dependencies
composer update:requirements
```

Notes:

- After `composer update`, hooks will automatically publish Livewire assets, run Filament upgrade, and clear caches.
- When running commands, prefer executing them inside the container as the application user to avoid permission issues.

## Coding style & conventions

- Follow [.editorconfig](.editorconfig).
- Use Laravel Pint with the rules defined in [pint.json](pint.json).

### Static analysis and tests

- Tests use [Pest](https://pestphp.com/). Always run `composer test` locally before opening a PR. This executes all
  phases defined in [composer.json.](composer.json)
- Static analysis uses PHPStan with the project rules in [phpstan.neon](phpstan.neon). You can run just this phase via
  `composer test:types`.

## Frontend

Filament and Livewire asset publishing is handled by the setup script and after Composer updates. To publish assets
manually:

```shell
php artisan livewire:publish --assets
php artisan filament:assets
```

After making changes to assets in the [resources](resources) directory, rebuild with:

```shell
npm run build
```

For hot reload run the Vite dev server inside the container:

```shell
docker exec -u application -it fin-tracker bash -c "npm run dev"
```

## Translations

- The main language is English.
- Supported locales today: `en`, `de` (see the [lang](lang) directory).
- All UI text must use a translation key via Laravel’s translation system. Don’t hard‑code strings in views, Livewire
  components or Filament resources.
- New features must include translations for all supported locales in the same PR. If you add a new translation key,
  create it in `lang/en/…` and `lang/de/…` with appropriate values before submitting the PR.

## IDE tips

If you use PhpStorm, the repository includes ready‑to‑use run configurations in the [.run](.run) directory. Open the
project in PhpStorm and the configurations will be detected automatically, so you can open a shell with a single click.

## Security

Please do not disclose security issues publicly. See [SECURITY.md](SECURITY.md) for reporting instructions.
