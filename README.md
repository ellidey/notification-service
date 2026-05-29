# Notification Service

Сервис уведомлений на Laravel 13, PHP 8.4 и PostgreSQL.

Проект готовится под тестовое задание: REST API для уведомлений, асинхронная доставка через очереди, каналы доставки и отчётность.

## Стек

- PHP 8.4
- Laravel 13
- PostgreSQL 17
- Nginx
- Docker Compose
- Laravel Queue с database driver
- Laravel Pint с preset `psr12`

## Быстрый запуск для разработки

Требования:

- Docker Desktop запущен.
- Порт `80` свободен.
- Команды выполняются из корня проекта.

```bash
cp .env.example .env
docker compose build
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec -T app php artisan migrate --force
```

После запуска приложение доступно по адресу:

```text
http://localhost
```

Swagger UI доступен по адресу:

```text
http://localhost/api/docs
```

OpenAPI JSON:

```text
http://localhost/api/docs/openapi
```

Проверить состояние контейнеров:

```bash
docker compose ps
```

## Повседневная разработка

Запустить проект:

```bash
docker compose up -d
```

Остановить проект без удаления данных:

```bash
docker compose down
```

Выполнить команду Artisan:

```bash
docker compose exec -T app php artisan route:list
```

Выполнить миграции:

```bash
docker compose exec -T app php artisan migrate
```

Запустить тесты:

```bash
docker compose exec -T app php artisan test
```

Запустить форматирование PSR-12:

```bash
docker compose exec -T app composer format
```

Проверить форматирование без изменений файлов:

```bash
docker compose exec -T app composer format:test
```

Посмотреть логи:

```bash
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f postgres
docker compose logs -f queue
```

## Очереди

В `docker-compose.yml` есть отдельный сервис `queue`.

Он запускает:

```bash
php artisan queue:work --queue=notifications,default --tries=3 --backoff=10
```

Для разработки достаточно держать контейнер `queue` запущенным. Если изменялась бизнес-логика job-классов, worker лучше перезапустить:

```bash
docker compose restart queue
```

## База данных

По умолчанию используется PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=notification_service
DB_USERNAME=notification_service
DB_PASSWORD=secret
```

Данные PostgreSQL хранятся в Docker volume `postgres-data`.

Полностью удалить локальную базу:

```bash
docker compose down
docker volume rm notification-service_postgres-data
docker compose up -d
docker compose exec -T app php artisan migrate --force
```

## Docker на Windows

На Windows bind mount проекта может сильно замедлять Laravel, потому что PHP читает много мелких файлов через файловый мост Docker Desktop.

В проекте уже применены оптимизации:

- исходный код подключён как `.:/var/www/html:cached`;
- `vendor` вынесен в Docker volume `vendor-data`;
- Laravel runtime cache, compiled views, sessions, logs и `bootstrap/cache` вынесены в Docker volumes;
- включены OPcache и realpath cache.

После изменения `composer.lock` нужно обновить зависимости внутри volume:

```bash
docker compose run --rm app composer install
```

Если зависимости стали неконсистентными:

```bash
docker compose down
docker volume rm notification-service_vendor-data
docker compose run --rm app composer install
docker compose up -d
```

## Проверки качества

Перед сдачей или pull request нужно выполнить:

```bash
docker compose exec -T app composer validate --strict
docker compose exec -T app composer format:test
docker compose exec -T app php artisan test
```

Форматирование кода выполняется Laravel Pint с preset `psr12`.

Конфигурация находится в:

```text
pint.json
```

## Production

Текущий `docker-compose.yml` предназначен только для локальной разработки.

Для production нужен отдельный Docker/инфраструктурный конфиг с production-переменными окружения, безопасными секретами, другим процессом сборки и отдельными правилами деплоя.

## Полезные команды

Пересобрать образы:

```bash
docker compose build
```

Пересоздать контейнеры:

```bash
docker compose up -d --force-recreate
```

Очистить Laravel cache:

```bash
docker compose exec -T app php artisan optimize:clear
```

Проверить версию Laravel:

```bash
docker compose exec -T app php artisan --version
```

Проверить доступность HTTP:

```bash
curl http://localhost
```

## Архитектурные принципы проекта

- HTTP-слой должен оставаться тонким: контроллеры принимают запросы и делегируют работу сервисам.
- Валидация входящих данных должна выполняться через FormRequest.
- Доставка уведомлений должна выполняться асинхронно через queue jobs.
- Каналы доставки должны подключаться через интерфейс/registry, чтобы новый канал добавлялся без изменения существующей логики отправки.
- Ошибки доставки должны сохраняться в базе и приводить к повторным попыткам через queue retry/backoff.
