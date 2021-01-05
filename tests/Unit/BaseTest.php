<?php

namespace Tests\Unit;

use IliaKologrivov\RabbitMQGlobalEventBus\EventsBusConnector;

trait BaseTest
{
    private $connection;

    public function getConnection(): EventsBusConnector
    {
        if ($this->connection === null) {
            $config = require('./config/event_bus.php');

            $this->connection = new EventsBusConnector($config['connection']['hosts'], $config['connection']['options'], $config['connection']['connection']);
        }

        return $this->connection;
    }
}
