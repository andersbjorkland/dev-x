<?php

use App\Controller\DevController;
use FrameworkX\App;

require __DIR__ . '/../vendor/autoload.php';

$app = new App();

$app->get('/', new DevController());

$app->run();