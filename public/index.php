<?php

use App\Controller\ApiController;
use App\Controller\DevController;
use App\Controller\TrendController;
use FrameworkX\App;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// Load environment variables
$dotenv = new Dotenv();
$path = __DIR__ . '/../.env';
$dotenv->loadEnv($path);

// Start the application
$app = new App();
$app->get('/', new DevController());
$app->get('/trends', new TrendController());
$app->get('/api', new ApiController());
$app->run();