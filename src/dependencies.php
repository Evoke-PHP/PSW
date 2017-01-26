<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// PDO
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    return new PDO($settings['dsn'], $settings['username'], $settings['passwd'], $settings['options']);
};

$container['ModelDataFeed'] = function ($c) {
    return new PSW\Model\OpenWeatherMapFeed($c->get('settings')['weather_api_key']);
};

$container['ModelWeatherReading'] = function ($c) {
    return new PSW\Model\WeatherReading($c->get('db'));
};
