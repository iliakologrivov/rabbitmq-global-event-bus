<?php

namespace Tests\Unit;

use IliaKologrivov\RabbitMQGlobalEventBus\Exception\SenderException;
use IliaKologrivov\RabbitMQGlobalEventBus\Formatters\JsonEventFormatter;
use IliaKologrivov\RabbitMQGlobalEventBus\Sender\AbstractEvent;
use IliaKologrivov\RabbitMQGlobalEventBus\Sender\EventHandler;
use IliaKologrivov\RabbitMQGlobalEventBus\Sender\EventMiddlewareContract;
use IliaKologrivov\RabbitMQGlobalEventBus\Sender\Sender;
use PHPUnit\Framework\TestCase;

class SenderTest extends TestCase
{
    use BaseTest;

    protected $sender;

    public function setUp(): void
    {
        $eventHandler = new EventHandler();
        $eventFormatter = new JsonEventFormatter();
        $config = require('./config/event_bus.php');

        $middleware = new class implements EventMiddlewareContract {
            public function handler(string $eventName, $payload): array
            {
                $payload['middleware_test'] = 'test';

                return $payload;
            }
        };

        $eventHandler->addMiddleware($middleware);

        $this->sender = new Sender($this->getConnection(), $eventHandler, $eventFormatter, $config);
    }

    public function testSend()
    {
        $event = new class extends AbstractEvent {
            public function getName(): string
            {
                return 'test';
            }
        };

        $this->assertNull($this->sender->send($event));
    }

    public function testException()
    {
        $eventHandler = new EventHandler();
        $eventFormatter = new JsonEventFormatter();
        $config = require('./config/event_bus.php');

        unset($config['service_name']);

        $this->expectException(SenderException::class);

        $sender = new Sender($this->getConnection(), $eventHandler, $eventFormatter, $config);
    }
}
