<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Worker;

/**
 * Interface EventDispatcherContract
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Worker
 */
interface EventDispatcherContract
{
    /**
     * @param object $event
     *
     * @return mixed
     */
    public function dispatch(object $event);
}
