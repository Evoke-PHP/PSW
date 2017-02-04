<?php
$container = $app->getContainer();

// Routes
if (PHP_SAPI === 'cli') {
    $app->get('/update/cams', 'PSW\Controller\Cam:updateLocations');
    $app->get('/update', 'PSW\Controller\Update:execute');

    return;
}

$app->get('/', 'PSW\Controller\Weather:show');
$app->get('/measurement/{measurement}', 'PSW\Controller\Weather:showMeasurement');
$app->post('/measurement/{measurement}/comment', 'PSW\Controller\Comment:addComment');