<?php

namespace Tests\Unit;

use IliaKologrivov\RabbitMQGlobalEventBus\Manager;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    use BaseTest;

    private $manager;

    public function setUp(): void
    {
        $this->manager = new Manager($this->getConnection());
    }

    public function testCreateGeneralExchange()
    {
        $this->assertNull($this->manager->createGeneralExchange());
    }

    public function testAddService()
    {
        $this->assertNull($this->manager->addService('test'));
    }

    public function testDeleteService()
    {
        $this->assertNull($this->manager->deleteService('test'));
    }
}
