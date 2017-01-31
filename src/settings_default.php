<?php
return [
    'settings' => [
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'db' => [
            'dsn'      => DB_DSN,
            'username' => DB_USERNAME,
            'passwd'   => DB_PASSWD,
            'options'  => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        ],
        'displayErrorDetails' => true, // set to false in production
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
        'weather_api_key' => OPEN_WEATHER_MAP_API_KEY,
    ],
];
