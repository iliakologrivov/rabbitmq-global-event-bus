<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Manager
{
    /**
     * @var EventsBusConnector
     */
    private $connection;

    public function __construct(EventsBusConnector $connection)
    {
        $this->connection = $connection;
    }

    public function addService(string $serviceName, string $generalExchange = 'general_exchange'): void
    {
        $channel = $this->connection->connect()->channel();

        //create exchange for service https://www.rabbitmq.com/blog/2010/10/19/exchange-to-exchange-bindings/
        $channel->exchange_declare($serviceName, 'fanout', false, true, false);
        $channel->basic_publish(new AMQPMessage('test'), $serviceName, 'test');
        $channel->exchange_delete($serviceName);
        $channel->exchange_declare($serviceName, 'direct', false, true, false);

        //create queue for service
        $channel->queue_declare($serviceName, false, true, false, false);

        //added bindings service in general exchange
        $channel->exchange_bind($serviceName, $generalExchange, '');
    }

    public function createGeneralExchange(string $exchangeName = 'general_exchange'): void
    {
        $this->connection->connect()->channel()->exchange_declare($exchangeName, 'fanout', false, true, false);
    }

    public function deleteService(string $serviceName): void
    {
        $channel = $this->connection->connect()->channel();
        $channel->exchange_delete($serviceName);
        $channel->queue_delete($serviceName);
    }
}
