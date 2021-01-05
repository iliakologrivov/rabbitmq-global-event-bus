<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

/**
 * Class AbstractEvent
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Pusher
 */
abstract class AbstractEvent
{
    /**
     * @return string
     */
    abstract public function getName():string;

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return \DateTimeImmutable
     *
     * @throws \Exception
     */
    public function getTimestamp(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
