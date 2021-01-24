<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

/**
 * Interface EventMiddlewareContract
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Sender
 */
interface EventMiddlewareContract
{
    public function handle(string $eventName, $payload): array;
}
