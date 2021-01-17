<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

/**
 * Class AbstractEvent
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Sender
 */
abstract class AbstractEvent implements EventContract
{
    /**
     * @return string
     */
    abstract public function getName(): string;

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
