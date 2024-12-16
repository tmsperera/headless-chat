[← 🏠](../../README.md)

# Develop with Docker

> [The beauty of Docker for local Laravel development](https://dev.to/aschmelyun/the-beauty-of-docker-for-local-laravel-development-13c0)

> [aschmelyun/docker-compose-laravel](https://github.com/aschmelyun/docker-compose-laravel).

## How to start?

Run `docker-compose up` or `docker-compose up -d`

## Composer

- `docker-compose exec app composer dump-autoload`

## Artisan

- `docker-compose exec app php artisan optimize:clear`

## Frontend

### Install dependencies

- `docker-compose exec app npm install`

### Start dev server

- `docker-compose exec app npm run dev -- --host`

>https://docs.docker.com/engine/reference/commandline/compose_run/

### Compiling assets for Production

- `docker-compose exec app npm run build`.

### Server side rendering

- `docker-compose exec app php artisan inertia:start-ssr`.
