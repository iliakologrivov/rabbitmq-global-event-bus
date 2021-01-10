<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

/**
 * Interface EventMiddlewareContract
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Sender
 */
interface EventMiddlewareContract
{
    public function handler(string $eventName, $payload): array;
}
