<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

/**
 * Interface Event
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Sender
 */
interface EventContract
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getPayload(): array;

    /**
     * @return \DateTimeImmutable
     *
     * @throws \Exception
     */
    public function getTimestamp(): \DateTimeImmutable;
}
