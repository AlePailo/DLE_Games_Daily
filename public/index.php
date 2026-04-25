<?php declare(strict_types = 1);

use Dotenv\Dotenv;

define ('BASE_PATH', dirname(__DIR__) . '/');

require BASE_PATH . 'vendor/autoload.php';

//.env loading
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

//Critical variables check
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_CHARSET']);