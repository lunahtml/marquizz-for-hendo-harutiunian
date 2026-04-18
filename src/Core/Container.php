<?php
//src/Core/Container.php (простой DI)
declare(strict_types=1);

namespace SurveySphere\Core;

use Psr\Container\ContainerInterface;
use SurveySphere\Exceptions\ContainerException;

class Container implements ContainerInterface
{
    private array $services = [];
    private array $factories = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->services[$id]);
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new ContainerException("Service not found: {$id}");
        }

        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->factories[$id]($this);
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]);
    }
}