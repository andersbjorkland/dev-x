<?php

use App\Controller\DevController;
use FrameworkX\App;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = new Dotenv();
$path = __DIR__ . '/../.env';
$dotenv->loadEnv($path);

// Start the application
$app = new App();
$app->get('/', new DevController());
$app->run();