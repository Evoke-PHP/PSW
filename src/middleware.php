<?php
// Application middleware
if (PHP_SAPI !== 'cli') {
    $app->add($container->get('csrf'));
}
