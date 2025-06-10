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
```

## 4. Дополнительная информация

Для корректной работы почтовых уведомлений необходимо настроить в Laravel соответствующий драйвер почты (MAIL_MAILER и другие).

Логи ошибок будут писаться, только если в конфиге 'error_log' включено.

Отправка ошибок на почту происходит, только если 'error_mail' включено и указан адрес получателя.





