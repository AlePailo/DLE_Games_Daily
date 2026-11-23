<?php declare(strict_types = 1);

namespace App\Core;

use Dotenv\Dotenv;

final class Environment {
    public static function load(string $basePath) : Dotenv {
        $dotEnv = Dotenv::createImmutable($basePath);
        $dotEnv->load();

        $timezone = $_ENV['APP_TIMEZONE'] ?? 'UTC';
        if(!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            throw new \RuntimeException("Invalid APP_TIMEZONE: $timezone (e.g. Europe/Rome)");
        }
        date_default_timezone_set($timezone);

        return $dotEnv;
    }
}