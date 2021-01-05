<?php

namespace Tests\Unit;

use IliaKologrivov\RabbitMQGlobalEventBus\Manager;
use IliaKologrivov\RabbitMQGlobalEventBus\Subscriber\Subscriber;
use PHPUnit\Framework\TestCase;

class SubscriberTest extends TestCase
{
    use BaseTest;

    private $subscriber;

    public function setUp(): void
    {
        $config = require('./config/event_bus.php');
        $config['service_name'] = 'test';

        (new Manager($this->getConnection()))->addService('test');

        $this->subscriber = new Subscriber($this->getConnection(), $config);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(Subscriber::class, $this->subscriber);
    }

    public function testGetQueueName()
    {
        $this->assertIsString($this->subscriber->getQueueName());
    }

    public function testGetExchangeName()
    {
        $this->assertIsString($this->subscriber->getExchangeName());
    }

    public function testSubscribe()
    {
        $this->assertNull($this->subscriber->subscribe('test'));
    }

    public function testUnsubscribe()
    {
        $this->assertNull($this->subscriber->unsubscribe('test'));
    }

    public function tearDown(): void
    {
        (new Manager($this->getConnection()))->deleteService('test');
    }
}
