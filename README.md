# ProSystemsIntegration — Инструкция по установке

---

## 1. Запуск миграций базы данных

Для применения миграций, которые идут с пакетом, выполните команду:

```bash
php artisan migrate
```


## 2. Публикация конфигурации и представлений

Для публикации файла конфигурации выполните команду:

```bash
php artisan vendor:publish --tag=pro-systems-integration
```
Для публикации представлений (views) выполните команду:

```bash
php artisan vendor:publish --tag=pro-systems-integration-views
```

## 3. Переменные окружения (env)

В файле .env необходимо добавить или настроить следующие переменные:

```ini
# Включение логирования ошибок (true/false)
PRO_SYSTEMS_ERROR_LOG=true

# Включение отправки ошибок на почту (true/false)
PRO_SYSTEMS_ERROR_MAIL=false

# Кому отправлять письмо при ошибке (если включена отправка почты)
PRO_SYSTEMS_MAIL_TO=mail@localhost.lan

# Тема письма для ошибок
PRO_SYSTEMS_MAIL_SUBJECT="ProSystemsFetchData Job Failed"

# Данные для авторизации в ProSystems API (логин)
PRO_SYSTEMS_LOGIN=""

# Данные для авторизации в ProSystems API (пароль)
PRO_SYSTEMS_PASSWORD=""

# Базовый URL ProSystems (пример тестового адреса)
PRO_SYSTEMS_URL=""

# API-ключ для получения данных из веб сервиса
PRO_SYSTEMS_API_KEY_DATA=""
```

## 4. Дополнительная информация

Для корректной работы почтовых уведомлений необходимо настроить в Laravel соответствующий драйвер почты (MAIL_MAILER и другие).

Логи ошибок будут писаться, только если в конфиге 'error_log' включено.

Отправка ошибок на почту происходит, только если 'error_mail' включено и указан адрес получателя.

## 5. Получение данных из ProSystems (потоковая выгрузка)
### 5.1. Получение данных из ProSystems вручную

Для получения накопленных данных из системы «Программный Фискализатор 3.0.1» используется следующая очередь:
```php
use webdophp\ProSystemsIntegration\Jobs\ProSystemsFetchData;

ProSystemsFetchData::dispatch();
```

### 5.2. Автоматический запуск через планировщик (scheduler)

Для автоматического получения данных из системы «Программный Фискализатор 3.0.1» 
рекомендуется настроить вызов ProSystemsFetchData::dispatch();
через Laravel Scheduler.

Например, в методе schedule() файла app/Console/Kernel.php добавьте:

```php
use webdophp\ProSystemsIntegration\Jobs\ProSystemsFetchData;

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        ProSystemsFetchData::dispatch();
    })->everyFiveMinutes(); // или любое другое расписание
}
```

## 6. Получение данных из ProSystems (детальная информация)
### 6.1. Получение данных из ProSystems вручную

Для получения накопленных данных из системы «Программный Фискализатор 3.0.1» используется следующая очередь:
```php
use webdophp\ProSystemsIntegration\Jobs\ProSystemsFetchAllData;

ProSystemsFetchAllData::dispatch();
```

### 6.2. Автоматический запуск через планировщик (scheduler)

Для автоматического получения данных из системы «Программный Фискализатор 3.0.1»
рекомендуется настроить вызов ProSystemsFetchAllData::dispatch();
через Laravel Scheduler.

Например, в методе schedule() файла app/Console/Kernel.php добавьте:

```php
use webdophp\ProSystemsIntegration\Jobs\ProSystemsFetchAllData;

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        ProSystemsFetchAllData::dispatch();
    })->everyFiveMinutes(); // или любое другое расписание
}
```

Требования
==========================
> Для корректной работы очереди необходимо:
> 
> Убедиться, что очереди настроены в Laravel. Например, в .env указано:
> ```ini
> QUEUE_CONNECTION=database
> ```
> Создать таблицу для хранения очередей (если используется database драйвер):
> ```bash
> php artisan queue:table
> php artisan migrate
> ```
> Запустить обработчик очередей:
> ```bash
> php artisan queue:work
> ```

## 7. Вызовы API
#### 1. Проверка доступности сервиса
```bash
GET http://localhost/api/pro-systems/ping
```
#### 2. Получить данные
```bash
GET http://localhost/api/pro-systems/data
```
#### 3. Подтвердить получение данных
```bash
GET http://localhost/api/pro-systems/confirm
```
Обязательные заголовки
> 
> Каждый запрос к API должен содержать обязательный заголовок API-KEY.
> 
>Пример заголовков:
> 
> API-KEY: PRO_SYSTEMS_API_KEY_DATA (ваш_ключ_доступа) 

Пример с использованием curl:
```bash
curl -X GET http://localhost:8000/api/pro-systems/ping \
  -H "API-KEY: ваш_ключ_доступа"
```




