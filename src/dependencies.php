<?php
// DIC configuration

$container = $app->getContainer();

$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

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

$container['PDO'] = function ($c) {
    $settings = $c->get('settings')['db'];
    return new PDO($settings['dsn'], $settings['username'], $settings['passwd'], $settings['options']);
};

$container['WeatherFeeds'] = function ($c) {
    return [
        new PSW\Model\Feed\OpenWeatherMapFeed($c->get('settings')['weather_api_key']),
        new PSW\Model\Feed\YahooFeed
    ];
};

$container['PSW\Model\WeatherReading'] = function ($c) {
    return new PSW\Model\WeatherReading($c->get('PDO'));
};

$container['PSW\Controller\Comment'] = function ($c) {
    return new PSW\Controller\Comment(new PSW\Model\Comment($c->get('PDO')), $c->get('renderer'));
};

$container['PSW\Controller\Update'] = function ($c) {
    $pdo = $c->get('PDO');

    return new PSW\Controller\Update(
        new PSW\Model\Feed\CamFeed,
        new PSW\Model\Cam($pdo),
        new PSW\Model\Location($pdo),
        $c->get('WeatherFeeds'),
        $c->get('PSW\Model\WeatherReading')
    );
};


$container['PSW\Controller\Cam'] = function ($c) {
    return new \PSW\Controller\Cam(
        new \PSW\Model\Feed\CamFeed,
        new \PSW\Model\Location($c->get('PDO'))
    );
};

$container['PSW\Controller\Weather'] = function ($c) {
    return new PSW\Controller\Weather(
        $c->get('csrf'),
        $c->get('PSW\Model\WeatherReading'),
        $c->get('renderer')
    );
};