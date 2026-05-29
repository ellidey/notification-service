# Notification Service

Сервис уведомлений на Laravel 13, PHP 8.4 и PostgreSQL.

Проект реализует REST API для создания уведомлений, асинхронной доставки через очереди, просмотра истории доставок и формирования отчётов.

## Стек

- PHP 8.4
- Laravel 13
- PostgreSQL 17
- Nginx
- Docker Compose
- Laravel Queue с database driver
- Laravel Pint с preset `psr12`
- L5 Swagger
- Maatwebsite Excel

## Важно

Текущий `docker-compose.yml` предназначен только для локальной разработки.

## Быстрый запуск

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

Проверить контейнеры:

```bash
docker compose ps
```

Swagger UI:

```text
http://localhost/api/docs
```

OpenAPI JSON:

```text
http://localhost/api/docs/openapi
```

## Очереди

В `docker-compose.yml` есть отдельный сервис `queue`.

Он запускает:

```bash
php artisan queue:work --queue=reports,notifications,default --tries=3 --backoff=10
```

Очередь `notifications` обрабатывает доставку уведомлений. Очередь `reports` формирует файлы отчётов.

Если изменялась бизнес-логика job-классов, worker лучше перезапустить:

```bash
docker compose restart queue
```

## API: уведомления

### Создать уведомление

```bash
curl -X POST http://localhost/api/notifications \
  -H "Content-Type: application/json" \
  -d "{\"user_id\":1001,\"message\":\"Тестовое уведомление\",\"channels\":[\"email\",\"telegram\"]}"
```

Ответ `201 Created` вернёт уведомление и доставки по каналам. Сами доставки выполняются асинхронно через очередь.

### Получить уведомление

```bash
curl http://localhost/api/notifications/{notification_id}
```

### Получить историю уведомлений пользователя

```bash
curl "http://localhost/api/users/1001/notifications"
```

Фильтры:

```bash
curl "http://localhost/api/users/1001/notifications?channel=telegram&status=error"
```

Доступные каналы:

- `email`
- `telegram`

Доступные статусы доставок:

- `processing`
- `sent`
- `error`

## API: отчёты

Отчёт создаётся асинхронно:

1. Создать отчёт.
2. Проверять статус отчёта.
3. Когда статус станет `ready`, скачать CSV-файл.

### Создать отчёт

```bash
curl -X POST http://localhost/api/users/1001/notifications/reports \
  -H "Content-Type: application/json" \
  -d "{\"period_from\":\"2026-05-01T00:00:00Z\",\"period_to\":\"2026-05-31T23:59:59Z\"}"
```

Ответ `202 Accepted` вернёт отчёт со статусом `pending`.

### Получить список отчётов пользователя

```bash
curl "http://localhost/api/users/1001/notifications/reports"
```

Фильтр по статусу:

```bash
curl "http://localhost/api/users/1001/notifications/reports?status=ready"
```

Доступные статусы отчётов:

- `pending`
- `processing`
- `ready`
- `error`

### Проверить конкретный отчёт

```bash
curl http://localhost/api/notifications/reports/{report_id}
```

### Скачать готовый отчёт

```bash
curl -L -o report.csv http://localhost/api/notifications/reports/{report_id}/download
```

Если отчёт ещё не готов, endpoint вернёт `409 Conflict`.

## Разработка

Запустить проект:

```bash
docker compose up -d
```

Остановить проект без удаления данных:

```bash
docker compose down
```

Выполнить Artisan-команду:

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

Запустить статический анализ PHPStan/Larastan:

```bash
docker compose exec -T app composer analyse
```

Запустить форматирование PSR-12:

```bash
docker compose exec -T app composer format
```

Проверить форматирование без изменений файлов:

```bash
docker compose exec -T app composer format:test
```

Пересобрать Swagger-документацию:

```bash
docker compose exec -T app php artisan l5-swagger:generate
```

Посмотреть логи:

```bash
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f postgres
docker compose logs -f queue
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

На Windows bind mount проекта может замедлять Laravel, потому что PHP читает много мелких файлов через файловый мост Docker Desktop.

В проекте применены оптимизации:

- исходный код подключён как `.:/var/www/html:cached`;
- `vendor` вынесен в Docker volume `vendor-data`;
- Laravel runtime cache, compiled views, sessions, logs, `storage/api-docs` и `bootstrap/cache` вынесены в Docker volumes;
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

Перед сдачей нужно выполнить:

```bash
docker compose exec -T app composer validate --strict
docker compose exec -T app composer analyse
docker compose exec -T app composer format:test
docker compose exec -T app php artisan test
```

Форматирование кода выполняется Laravel Pint с preset `psr12`.

Конфигурация находится в:

```text
pint.json
```

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

## Архитектурные принципы

- HTTP-слой остаётся тонким: контроллеры принимают запросы и делегируют работу action/job-классам.
- Валидация входящих данных выполняется через FormRequest.
- Доставка уведомлений выполняется асинхронно через queue jobs.
- Формирование отчётов выполняется асинхронно через queue jobs.
- Каналы доставки подключаются через интерфейс и registry.
- Ошибки доставки сохраняются в базе и приводят к повторным попыткам через queue retry/backoff.

## Улучшения перед production

- Добавить аутентификацию и авторизацию для доступа только к своим уведомлениям и отчётам.
- Настроить production-очереди через Supervisor или Horizon с мониторингом failed jobs.
- Подключить реальные email и Telegram провайдеры вместо текущих sender-заглушек.
- Вынести retry/backoff/max attempts в конфигурацию для разных каналов доставки.
- Ограничить максимальный период отчёта и добавить rate limiting для тяжёлых API-запросов.
