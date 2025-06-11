<a id="readme-top"></a>

<div align="center">

<h1>Fin-Tracker</h1>
<h3>Household Finance Manager</h3>

<!-- Badges -->
<p>
  <a href="https://hub.docker.com/r/barthjs/fin-tracker/tags">
    <img src="https://img.shields.io/docker/v/barthjs/fin-tracker?label=Docker&logo=docker&style=for-the-badge&style=flat" alt="Docker image">
  </a>
  <a href="https://github.com/barthjs/fin-tracker/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/barthjs/fin-tracker" alt="License"/>
  </a>
  <a href="https://github.com/barthjs/fin-tracker/issues/">
    <img src="https://img.shields.io/github/issues/barthjs/fin-tracker" alt="open issues"/>
  </a>
</p>

</div>

<!-- Table of Contents -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#about">About</a></li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
        <li><a href="#configuration">Configuration</a></li>
        <li><a href="#updating">Updating</a></li>
        <li><a href="#backup">Backup</a></li>
      </ul>
    </li>
    <li><a href="#screenshots">Screenshots</a></li>
    <li>
      <a href="#contributing">Contributing</a>
      <ul>
        <li><a href="#requirements">Requirements</a></li>
        <li><a href="#building">Building</a></li>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li><a href="#license">License</a></li>
    <li><a href="#acknowledgements">Acknowledgements</a></li>
  </ol>
</details>

## About

**Fin-Tracker** is a self-hostable, web-based household finance manager designed to help you monitor and organize your
financial activity across multiple bank accounts and investment portfolios.

### Features

- Track and categorize expenses and income from multiple bank accounts
- Record and manage trades across multiple investment portfolios
- Import and export data via CSV and Excel files
- Multi-user support with two-factor authentication (2FA)
- Fully self-hostable using Docker

## Getting Started

### Prerequisites

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Installation

Create an app directory and navigate into it:

```shell
mkdir fin-tracker && cd ./fin-tracker
```

Create a `.env` file using the values from the [.env.example](.env.example) and adjust it as needed. If
you plan to use your own external database, ensure you set the correct `DB_CONNECTION` in the `.env` file.
The only supported databases are MariaDB and MySQL.

```shell
curl https://raw.githubusercontent.com/barthjs/fin-tracker/main/.env.example -o .env
```

Download the [compose.yaml](compose.yaml) file.

```shell
curl https://raw.githubusercontent.com/barthjs/fin-tracker/main/compose.yaml -o compose.yaml
```

Start the application:

```shell
docker compose up -d
```

Access the app at [http://localhost:8080](http://localhost:8080) using the default credentials:

- **Username**: `admin`
- **Password**: `admin`

Upon first login, you will be redirected to the profile page to change the default password.

### Configuration

Use the `.env` file to adjust configuration settings:

| Environment variable     | Default          | Description                                                              |
|--------------------------|------------------|--------------------------------------------------------------------------|
| `APP_KEY`                | (required)       | The encryption key for your sessions. Must be a string of 32 characters. |
| `APP_TIMEZONE`           | `UTC`            | Application timezone                                                     |
| `APP_LOCALE`             | `en`             | Supported languages: `en`, `de`                                          |
| `APP_ALLOW_REGISTRATION` | `false`          | Enable/disable user self-registration                                    |
| `DB_CONNECTION`          | `mariadb`        | `mariadb` or `mysql`                                                     |
| `DB_HOST`                | `fin-tracker-db` | Database host                                                            |
| `DB_PORT`                | `3306`           | Database port                                                            |
| `DB_DATABASE`            | `fin-tracker`    | Database name                                                            |
| `DB_PASSWORD`            | (required)       | Database password                                                        |

### Updating

Before updating, export your data using the CSV export feature. Check the changelog for any breaking changes or new
configuration options.

To update:

```shell
cd fin-tracker
docker compose pull && docker compose up -d
```

### Backup

Back up all Docker volumes mentioned in the [compose.yaml](compose.yaml) as well as the `.env`.

## Screenshots

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Contributing

Contributions are welcome. If you encounter a bug, have a feature request, or need support, feel free
to [open an issue](https://github.com/barthjs/fin-tracker/issues/).

### Requirements

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

A Linux environment is recommended for development. Development setup includes:

- [Dockerfile-dev](docker/Dockerfile-dev)
- [compose.dev.yaml](compose.dev.yaml)

For the best experience use [PHP Storm](https://www.jetbrains.com/phpstorm/). Configure the IDE debugger:

- **Name**: `fin-tracker`
- **host:port**: `localhost:80`
- **Debugger**: `Xdebug`
- **Absolute path on the server**: `/app`

### Building

Clone the repo prepare the development environment:

```shell
git clone https://github.com/barthjs/fin-tracker
cd fin-tracker
./setup-dev.sh
```

This script sets up a development container and initializes the database with demo data. Customize
via [.env.development](.env.development).

Default login at [http://localhost:80](http://localhost:80)

- Username: `admin`
- Password: `admin`

### Built With

- <a href="https://php.net">
  <img alt="PHP 8.3" src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php">
  </a>
- <a href="https://laravel.com">
  <img alt="Laravel v12.x" src="https://img.shields.io/badge/Laravel-v12.x-FF2D20?style=flat-square&logo=laravel">
  </a>
- <a href="https://filamentphp.com/">
  <img alt="Filament v3.x" src="https://img.shields.io/badge/Filament-v3.x-e9b228?style=flat-square">
  </a>
- <a href="https://tabler.io/icons">
  <img alt="Tabler Icons" src="https://img.shields.io/badge/Tabler_Icons-grey?style=flat-square">
  </a>
- <a href="https://hub.docker.com/r/barthjs/fin-tracker/tags">
  <img src="https://img.shields.io/docker/v/barthjs/fin-tracker?label=Docker&logo=docker&style=flat-square" alt="Docker image">
  </a>

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Acknowledgements

- Built with [Laravel](https://laravel.com) and [Filament](https://filamentphp.com/)
- Inspired by various household finance and expense tracker tools

<p align="right">(<a href="#readme-top">back to top</a>)</p>
