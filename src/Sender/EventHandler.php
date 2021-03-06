<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

/**
 * Class EventHandler
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Sender
 */
class EventHandler
{
    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * @return EventMiddlewareContract[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param EventMiddlewareContract $middleware
     */
    public function addMiddleware(EventMiddlewareContract $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * @param EventContract $event
     * @return array
     *
     * @throws \Exception
     */
    public function handle(EventContract $event): array
    {
        $eventName = $event->getName();

        $data = [
            'payload' => $event->getPayload(),
            'date' => $event->getTimestamp()->format(DATE_RFC3339_EXTENDED),
        ];

        foreach ($this->getMiddleware() as $middleware) {
            $data = $middleware->handle($eventName, $data);
        }

        return $data;
    }
}
