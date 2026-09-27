# Laravel Bangladesh

[![Tests](https://github.com/LaravelBangladesh/laravelbd/actions/workflows/tests.yml/badge.svg)](https://github.com/LaravelBangladesh/laravelbd/actions/workflows/tests.yml)
[![Release](https://img.shields.io/github/v/release/LaravelBangladesh/laravelbd?include_prereleases&sort=semver)](https://github.com/LaravelBangladesh/laravelbd/releases)
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](LICENSE)
[![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![React 19](https://img.shields.io/badge/React-19-20232A?logo=react&logoColor=61DAFB)](https://react.dev/)

Community platform for the Laravel Bangladesh user group. Docker, Laravel 13, Inertia + React + Catalyst, bilingual UI, staff roles, passwordless accounts, events, resources, and a directory.

## Stack

- Laravel 13, PHP 8.5, PostgreSQL 18, Redis 8
- Inertia 3, React 19, TypeScript, Tailwind CSS 4, Catalyst UI
- Auth: magic link, email login code, and passkeys (no passwords)
- Locales: English and Bangla
- Images: Cloudflare R2, Cloudflare Images, or the local `public` disk, via `IMAGE_DRIVER`
- Videos: YouTube URLs only

## Installation

Docker is required. Do not install or run PHP, Composer, or Node on the host.

### Requirements

- [Docker](https://docs.docker.com/get-docker/) with Compose
- Git

### Local install

```sh
git clone https://github.com/LaravelBangladesh/laravelbd.git
cd laravelbd
chmod +x bin/setup
./bin/setup
```

`bin/setup` copies `docker/env.example` to `.env` if needed, builds the images, installs PHP and Node dependencies, generates the app key, migrates, seeds, and starts the stack.

Then open:

| Service                | URL                                            |
| ---------------------- | ---------------------------------------------- |
| App                    | [http://localhost:8080](http://localhost:8080) |
| Mailpit (login emails) | [http://localhost:8025](http://localhost:8025) |

The first seeded staff user is `admin@example.com`. There is no password. Request a login code or magic link on `/login`, then open the message in Mailpit.

### Useful commands

```sh
docker compose up -d
docker compose down
docker compose exec app php artisan test
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose logs -f nginx app vite
```

Leave `IMAGE_DRIVER=local` so uploads stay on disk during development.

## License

Released under the [GNU Affero General Public License v3.0](LICENSE). If you run a modified version as a network service, you must make its source available to your users under the same license.
