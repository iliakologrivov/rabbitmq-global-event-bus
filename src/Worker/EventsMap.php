<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Worker;

/**
 * Class EventsMap
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Worker
 */
class EventsMap
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * EventsMap constructor.
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $eventName
     * @param string $eventClass
     */
    public function add(string $eventName, string $eventClass): void
    {
        $this->map[$eventName] = $eventClass;
    }

    /**
     * @param string $eventName
     *
     * @return string|null
     */
    public function getByName(string $eventName): ?string
    {
        return $this->map[$eventName] ?? null;
    }

    /**
     * @return string[]
     */
    public function getEventsList(): array
    {
        return array_keys($this->map);
    }
}
