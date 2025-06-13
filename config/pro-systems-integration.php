<?php

return [

    /**
     *  Пишем логи в лог файл или отправляем на почту
     */
    'error_log' => env('PRO_SYSTEMS_ERROR_LOG', false),

    'error_mail' => env('PRO_SYSTEMS_ERROR_MAIL', false),


    /**
     * Если лог отправка на почту, то нужно указать кому отправить и заголовок письма для сортировки в почте.
     */
    'mail_to' => env('PRO_SYSTEMS_MAIL_TO', 'mail@localhost.lan'),

    'mail_subject' => env('PRO_SYSTEMS_MAIL_SUBJECT', 'ProSystemsFetchData Job Failed'),


    /**
     * Данные для авторизации в pro systems
     */
    'login' => env('PRO_SYSTEMS_LOGIN', ''),

    'password' => env('PRO_SYSTEMS_PASSWORD', ''),

    'base_url' => env('PRO_SYSTEMS_URL', 'https://test.softkkm.kz:5001/PROSYSTEMS-TEST202/FSCDataProvider/TEST/INSTANCE-A/INSTANCE-A.asmx'),

    'base_url_all' => env('PRO_SYSTEMS_URL_ALL', 'https://test.softkkm.kz:5001/PROSYSTEMS-TEST202/FSCDataProvider/TEST/DETAIL/INSTANCE-A.asmx'),

    /**
     * API-ключ для получения данных в веб сервисе
     */
    'api_key_data' => env('PRO_SYSTEMS_API_KEY_DATA', ''),
];