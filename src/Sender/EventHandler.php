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
     * @return array
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
     * @param AbstractEvent $event
     * @return array
     *
     * @throws \Exception
     */
    public function handle(AbstractEvent $event): array
    {
        $eventName = $event->getName();

        $data = [
            'payload' => $event->getPayload(),
            'date' => $event->getTimestamp()->format(DATE_RFC3339_EXTENDED),
        ];

        foreach ($this->getMiddleware() as $middleware) {
            $data = $middleware->handler($eventName, $data);
        }

        return $data;
    }
}
