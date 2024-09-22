<a id="readme-top"></a>

<div align="center">

<h1>Fin-Tracker</h1>
<h3>Self-hosted Household Finance Manager</h3>

<!-- Badges -->
<p>
  <a href="https://hub.docker.com/r/barthjs/fin-tracker/tags" title="Docker image">
  <img src="https://img.shields.io/docker/v/barthjs/fin-tracker?label=Docker&logo=docker&style=for-the-badge&style=flat" alt="Docker image">
  </a>
  <a href="https://github.com/barthjs/fin-tracker/blob/main/LICENSE">
    <img alt="Static Badge" src="https://img.shields.io/github/license/barthjs/fin-tracker"/>
  </a>
  <a href="https://github.com/barthjs/fin-tracker/issues/">
    <img src="https://img.shields.io/github/issues/barthjs/fin-tracker" alt="open issues"/>
  </a>
  <a href="https://filamentphp.com/"><img alt="Filament v3.x" src="https://img.shields.io/badge/Filament-v3.x-e9b228?style=for-the-badge&style=flat">
  </a>
</p>

<h4>
    <a href="https://github.com/barthjs/fin-tracker/issues/">Report Bug</a>
  <span> · </span>
    <a href="https://github.com/barthjs/fin-tracker/issues/">Request Feature</a>
</h4>
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
        <li><a href="#how-to-update">How to Update</a></li>
        <li><a href="#how-to-backup">How to Backup</a></li>
      </ul>
    </li>
    <li><a href="#faq">FAQ</a></li>
    <li><a href="#screenshots">Screenshots</a></li>
    <li>
      <a href="#development">Development</a>
      <ul>
        <li><a href="#configuration">Configuration</a></li>
        <li><a href="#requirements">Requirements</a></li>
        <li><a href="#building">Building</a></li>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#acknowledgements">Acknowledgements</a></li>
  </ol>
</details>

## About

Fin-Tracker is a self-hosted household finance manager that helps you track and categorize expenses across multiple bank
accounts. Its user-friendly interface allows you to easily log and categorize expenses, providing valuable insights into
your spending habits. Designed for multi-user access, you can host the app for family and friends. Additionally,
Fin-Tracker supports CSV imports and exports, along with Excel exports, making it simple to manage and analyze your
financial data.

## Getting Started

### Prerequisites

The only supported installation method is with Docker Compose. Building it manually is possible but requires
understanding of the [Laravel](https://larvel.com) framework.

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

It is highly recommended to run it behind a reverse proxy and bind the port to `localhost`. A good and easy solution
is [Nginx Proxy Manager](https://nginxproxymanager.com/guide/). Avoid exposing it to the public internet unless
absolutely necessary. Instead, use a VPN, such as [WireGuard](https://www.wireguard.com/), to securely access it from
outside your home network.

### Installation

```shell
mkdir fin-tracker && cd ./fin-tracker
```

Create a `.env` file using the values from the [.env.example](.env.example) and set your preferred configurations. If
you plan to use your
own database, ensure you specify the correct `DB_CONNECTION` in the `.env` file. Note that the only officially supported
database is MariaDB. While MySQL may work, compatibility with future updates is not guaranteed

```shell
curl https://raw.githubusercontent.com/barthjs/fin-tracker/main/.env.example -o .env
```

Download the [compose.yaml](compose.yaml) file and optionally customize it by adding your desired volumes and networks.

```shell
curl https://raw.githubusercontent.com/barthjs/fin-tracker/main/compose.yaml -o compose.yaml
```

Start the app:

```shell
docker compose up -d
```

Login at: [http://localhost:8080](http://localhost:8080) or your custom host/port from the `.env` file.

Use the following credentials:

- Username: `admin`
- Password: `admin`

Make sure to change these values after the installation is complete.

### How to Update

Please use the CSV exporter to back up your data before updating to a new version.

```shell
cd fin-tracker
docker compose pull && docker compose up -d
```

### How to Backup

If you are using the unmodified compose.yaml file, simply back up the `./db-data directory` You will need administrative
privileges to access this directory. If you created your own database volume, refer
to [Back up a volume](https://docs.docker.com/engine/storage/volumes/#back-up-a-volume) for more
information.

## FAQ

- Why can't I register as a user?
    - Fin-Tracker is designed for home and group use, with user registration managed by the admin.

- Who manages the users?
    - One user is designated as the admin and is responsible for managing all other users

- Is native installation supported?
    - Only Docker with the prebuilt image is supported, and native installation support will not be available in the
      future.

- Are mobile clients available?
    - Mobile clients are planned for the future. If you're interested in contributing, please refer to the contributing
      guidelines.

## Screenshots

## Development

### Configuration

A Linux environment with a [Dockerfile-dev](.docker/Dockerfile-dev) is recommended for development. For the best
experience use [PHP Storm](https://www.jetbrains.com/de-de/phpstorm/) as your IDE. Create a new server configuration
in `Settings > PHP > Servers` for debugging, using the following values:

- Name: `fin-tracker`
- host:port: `localhost:80`
- Debugger: `Xdebug`
- Absolute path on the server: `/app`

### Requirements

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)
- Optional: PHP 8.3
- Optional: NPM

### Building

Clone the repo:

```shell
git clone https://github.com/barthjs/fin-tracker
cd fin-tracker
```

Use the installation script:

```shell
./setup-dev.sh
```

This will build a Docker image with a development environment and create a database with a demo user. Check
the [.env.development](.env.development) for customization.

Login at: [http://localhost:80](http://localhost:80)

- Username: admin
- Password: admin

### Built With

- <a href="https://tabler.io/icons"><img alt="Tabler Icons" src="https://img.shields.io/badge/Tabler_Icons-grey?style=flat-square">
  </a>
- <a href="https://filamentphp.com/"><img alt="Filament v3.x" src="https://img.shields.io/badge/Filament-v3.x-e9b228?style=flat-square">
  </a>
- <a href="https://laravel.com"><img alt="Laravel v9.x" src="https://img.shields.io/badge/Laravel-v11.x-FF2D20?style=flat-square&logo=laravel">
  </a>
- <a href="https://hub.docker.com/r/barthjs/fin-tracker/tags" title="Docker image">
  <img src="https://img.shields.io/docker/v/barthjs/fin-tracker?label=Docker&logo=docker&style=flat-square" alt="Docker image">
  </a>
- <a href="https://php.net"><img alt="PHP 8.3" src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php">
  </a>

## Contributing

If you have a suggestion that would make this project better, please fork the repo and create a pull request.

See [CONTRIBUTING.md](CONTRIBUTING.md) for ways to get started.

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Acknowledgements

- This app was built using [Filament](https://filamentphp.com/) and [Laravel](https://laravel.com)
- Inspiration for this app came from various expense tracker apps available in the market

<p align="right">(<a href="#readme-top">back to top</a>)</p>
