<?php declare(strict_types = 1);

namespace App\Core;

class ContainerFactory {
    public static function build() : \DI\Container {
        $definitions = require BASE_PATH . 'config/container.php';
        return (new \DI\ContainerBuilder())
            ->addDefinitions($definitions)
            ->build();
    }
}