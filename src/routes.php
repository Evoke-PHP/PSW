<?php
$container = $app->getContainer();

// Routes
if (PHP_SAPI === 'cli') {
    $app->get('/update', function($request, $response) use ($container) {
        $controller = new \PSW\Controller\Update(
            $container->get('ModelDataFeed'),
            $container->get('ModelWeatherReading')
        );
        $controller->execute($request, $response);
    });

    return;
}

$app->get('/', '\PSW\Controller\Weather:show');
$app->get('/measurement/{measurement}', '\PSW\Controller\Weather:showMeasurement');