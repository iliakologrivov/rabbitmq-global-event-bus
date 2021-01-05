<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Worker;

use Throwable;

/**
 * Interface HandlerExceptionContract
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Worker
 */
interface HandlerExceptionContract
{
    /**
     * @param Throwable $exception
     *
     * @return mixed
     */
    public function handle(Throwable $exception);
}
