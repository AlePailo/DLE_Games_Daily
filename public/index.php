<?php declare(strict_types = 1);

use Dotenv\Dotenv;

session_start();

define ('BASE_PATH', dirname(__DIR__) . '/');
define('BASE_URL', '/DLE_Games_Daily');

require BASE_PATH . 'vendor/autoload.php';

App\Core\Application::run();