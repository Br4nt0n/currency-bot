<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Container\ContainerInterface;

class ContainerHelper
{
    private static ?ContainerInterface $container = null;

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function get(string $id)
    {
        return self::$container->get($id);
    }
}
