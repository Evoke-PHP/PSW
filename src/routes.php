<?php
// Routes
if (PHP_SAPI === 'cli') {
    $container = $app->getContainer();

    $app->get('/update', function($request, $response) use ($container) {

        $controller = new \PSW\Controller\Update(
            $container->get('ModelDataFeed'),
            $container->get('ModelWeatherReading')
        );
        $controller->execute($request, $response);
    });

    return;
}

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
