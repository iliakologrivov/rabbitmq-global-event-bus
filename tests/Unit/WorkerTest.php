<?php

namespace Tests\Unit;

use IliaKologrivov\RabbitMQGlobalEventBus\EventsBusConnector;
use IliaKologrivov\RabbitMQGlobalEventBus\Formatters\JsonEventFormatter;
use IliaKologrivov\RabbitMQGlobalEventBus\Manager;
use IliaKologrivov\RabbitMQGlobalEventBus\Subscriber\Subscriber;
use IliaKologrivov\RabbitMQGlobalEventBus\Worker\EventDispatcherContract;
use IliaKologrivov\RabbitMQGlobalEventBus\Worker\EventsMap;
use IliaKologrivov\RabbitMQGlobalEventBus\Worker\HandlerExceptionContract;
use IliaKologrivov\RabbitMQGlobalEventBus\Worker\Worker;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Exception;
use Tests\Events\TestEvent;

class WorkerTest extends TestCase
{
    use BaseTest;

    public function setUp(): void
    {
        $this->getConnection();

        (new Manager($this->connection))->addService('test');
    }

    public function testWorker()
    {
        /**
         * @var EventDispatcherContract $eventDispatcher
         */
        $eventDispatcher = \Mockery::mock(EventDispatcherContract::class);
        $eventDispatcher->shouldReceive('dispatch')->andReturn(null);

        /**
         * @var HandlerExceptionContract $exceptionHandler
         */
        $exceptionHandler = \Mockery::mock(HandlerExceptionContract::class);
        $exceptionHandler->shouldReceive('handle')->andReturn(null);

        /**
         * @var EventsBusConnector $connectionMock
         */
        $subscriber = new Subscriber($this->connection, [
            'service_name' => 'test',
        ]);

        $formatter = new JsonEventFormatter();
        $map = new EventsMap([]);
        $map->add('test', TestEvent::class);

        $this->connection->connect()->channel()->basic_publish(new AMQPMessage('{test}'), '', 'test');

        $worker = new Worker($this->connection, $eventDispatcher, $exceptionHandler, $formatter, $map, $subscriber, 'test');

        $this->assertNull($worker->daemon(3, 30, null, true));
    }

    public function testWorkerException()
    {
        /**
         * @var EventDispatcherContract $eventDispatcher
         */
        $eventDispatcher = \Mockery::mock(EventDispatcherContract::class);
        $eventDispatcher->shouldReceive('dispatch')->andThrow(new Exception('server has gone away'));

        /**
         * @var HandlerExceptionContract $exceptionHandler
         */
        $exceptionHandler = \Mockery::mock(HandlerExceptionContract::class);
        $exceptionHandler->shouldReceive('handle')->andReturn(null);

        /**
         * @var EventsBusConnector $connectionMock
         */
        $subscriber = new Subscriber($this->connection, [
            'service_name' => 'test',
        ]);

        $formatter = new JsonEventFormatter();
        $map = new EventsMap([]);
        $map->add('test', TestEvent::class);

        $event = [
            'payload' => [],
            'date' => date(DATE_RFC3339_EXTENDED),
        ];

        $this->connection->connect()->channel()->basic_publish(new AMQPMessage(json_encode($event)), '', 'test');

        $worker = new Worker($this->connection, $eventDispatcher, $exceptionHandler, $formatter, $map, $subscriber, 'test');

        $this->assertNull($worker->daemon());
    }

    public function tearDown(): void
    {
        \Mockery::close();

        (new Manager($this->connection))->deleteService('test');
    }
}
