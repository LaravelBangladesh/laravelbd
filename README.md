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

Production uses `IMAGE_DRIVER=r2`, a public Cloudflare R2 bucket served directly from its
domain. Create the bucket, an API token scoped to it (R2 → Manage API Tokens, Object Read &
Write), then expose it publicly — either the bucket's `r2.dev` URL or a custom domain — and
set that as `R2_URL`. `R2_ENDPOINT` is `https://<account-id>.r2.cloudflarestorage.com`.

## Production

Copy `docker/env.production.example` to `.env` on the server and `chmod 600` it. It lists only the keys production needs; every value marked `CHANGE ME` must be filled in:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, and a generated `APP_KEY`
- `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` (required by Compose)
- `SESSION_SECURE_COOKIE=true` and `SESSION_DOMAIN` behind HTTPS
- `ORIGIN_CERT_PATH` (defaults to `/etc/ssl/cloudflare`), plus `HTTP_PORT` and `HTTPS_PORT` if the defaults of 80 and 443 do not suit
- Real SMTP values (`MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`)

Do not commit secrets.

### TLS

Traffic terminates at Cloudflare and is re-encrypted to the origin, so no plaintext
application traffic crosses the network. Set the domain's DNS record to **Proxied**
(orange cloud) and SSL/TLS mode to **Full (strict)**.

Create a Cloudflare Origin Certificate (Cloudflare dashboard → SSL/TLS → Origin
Server → Create Certificate) and place it on the server as `origin.pem` and
`origin.key` inside `ORIGIN_CERT_PATH`:

```sh
sudo mkdir -p /etc/ssl/cloudflare
sudo install -m 600 /dev/null /etc/ssl/cloudflare/origin.key
# paste the private key into the file above, then the certificate:
sudo install -m 644 /dev/null /etc/ssl/cloudflare/origin.pem
```

The private key is secret and must never be committed. Origin certificates are
trusted only by Cloudflare, but that does not stop a client that ignores
certificate trust: a scanner hitting the server's IP directly still completes
the handshake. nginx therefore only answers for `laravelbd.com` and
`www.laravelbd.com`; any other `Host`, including the bare IP, gets the connection
closed (`return 444`).

As defence in depth, restrict ports 80 and 443 to Cloudflare at the firewall so
the origin is unreachable except through the edge. Docker publishes ports through
its own iptables chain and skips ufw's `INPUT` rules, so `ufw allow from ...` has
no effect on them. Filter in the `DOCKER-USER` chain instead:

```sh
sudo ufw allow OpenSSH
sudo ufw --force enable

for v in "" 6; do
    ipt="ip${v}tables"
    sudo "$ipt" -N CLOUDFLARE 2>/dev/null || sudo "$ipt" -F CLOUDFLARE
    for ip in $(curl -s "https://www.cloudflare.com/ips-v${v:-4}"); do
        sudo "$ipt" -A CLOUDFLARE -s "$ip" -j RETURN
    done
    sudo "$ipt" -A CLOUDFLARE -j DROP
    sudo "$ipt" -C DOCKER-USER -p tcp -m multiport --dports 80,443 -m conntrack --ctstate NEW -j CLOUDFLARE 2>/dev/null \
        || sudo "$ipt" -I DOCKER-USER -p tcp -m multiport --dports 80,443 -m conntrack --ctstate NEW -j CLOUDFLARE
done

sudo apt-get install -y iptables-persistent
sudo netfilter-persistent save
```

Docker leaves `DOCKER-USER` alone across restarts, and `netfilter-persistent`
restores the rules on boot. Re-run the loop when Cloudflare publishes new ranges.

Laravel trusts these same ranges (`config/cloudflare-proxies.php`) so that
`X-Forwarded-Proto` and the visitor's real IP are honoured from Cloudflare and
ignored from anyone else. Refresh that list if Cloudflare publishes new ranges.

### Server layout

Images are built by CI and pulled from the registry, so the server holds no
checkout of this repository. It needs only three things:

```
/opt/laravelbd/compose.prod.yml        # refreshed from the tag on each release; holds no secrets
/opt/laravelbd/.env                    # secrets, chmod 600, never written by CI
/etc/ssl/cloudflare/origin.{pem,key}   # Cloudflare origin certificate
```

Keep an encrypted copy of `.env` off the server (a password manager is fine). It
is the only unversioned state in the deployment, so losing the disk means
reconstructing every secret by hand.

### Releasing

Deploys happen on version tags. Merging to `main` runs the test suite and ships
nothing.

```sh
git tag v1.2.3
git push origin v1.2.3
```

That builds the `app`, `ssr`, and `nginx` images, pushes them to
`ghcr.io/laravelbangladesh/laravelbd-*:1.2.3`, then over SSH replaces the
server's `compose.prod.yml` with the one from the tag, rewrites `APP_IMAGE_TAG`
and `APP_VERSION` in `.env`, and runs:

```sh
docker compose -f compose.prod.yml pull
docker compose -f compose.prod.yml up -d
docker compose -f compose.prod.yml exec -T app php artisan migrate --force
```

Once the deploy succeeds, a GitHub release is created for the tag with generated
notes.

Every other line of `.env` is left alone, so secrets edited on the server survive
a deploy. Config, route, and view caches are rebuilt, `public/` is refreshed from
the image, and `storage:link` is recreated by the container entrypoint on each
start.

To roll back, set `APP_IMAGE_TAG` to the previous version and re-run the pull and
up commands above. Images are immutable, so a given tag always resolves to the
same build.

Changing a secret is a manual step: edit `/opt/laravelbd/.env` on the server and
run `docker compose -f compose.prod.yml up -d` to restart with the new values.

The release workflow needs these repository secrets: `DEPLOY_HOST`, `DEPLOY_USER`,
`DEPLOY_SSH_KEY` (a private key whose public half is in the deploy user's
`authorized_keys`), and `DEPLOY_PATH` (the directory holding `compose.prod.yml`
and `.env`).

### First deploy

The server has no `.env` yet, so the first release needs it in place beforehand:

```sh
sudo mkdir -p /opt/laravelbd
# copy compose.prod.yml from this repo, then create .env from docker/env.production.example
sudo chmod 600 /opt/laravelbd/.env
```

Set `APP_KEY` (`php artisan key:generate --show` locally), the database
credentials, `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN`, and the SMTP
values. Then push a tag.

## License

Released under the [GNU Affero General Public License v3.0](LICENSE). If you run a modified version as a network service, you must make its source available to your users under the same license.
