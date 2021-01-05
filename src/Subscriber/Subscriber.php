<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Subscriber;

use IliaKologrivov\RabbitMQGlobalEventBus\EventsBusConnector;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Class Subscriber
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Subscriber
 */
class Subscriber
{
    /**
     * @var EventsBusConnector
     */
    private $connection;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $exchangeName;

    /**
     * Subscriber constructor.
     *
     * @param EventsBusConnector $connection
     * @param array $options
     * - @var string exchange_name: Set exchange name if not appropriate by default.
     * - @var string queue_name: Set queue name if not appropriate by default.
     * - @var string service_name: Set exchange name and queue name default values.
     */
    public function __construct(EventsBusConnector $connection, array $options)
    {
        $this->connection = $connection;

        $this->setExchangeName(!empty($options['exchange_name']) ? $options['exchange_name'] : $options['service_name']);
        $this->setQueueName(!empty($options['queue_name']) ? $options['queue_name'] : $options['service_name']);
    }

    /**
     * @param string $name
     */
    public function setQueueName(string $name): void
    {
        $this->queueName = $name;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @param string $name
     */
    public function setExchangeName(string $name): void
    {
        $this->exchangeName = $name;
    }

    /**
     * @return string
     */
    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }

    /**
     * @param string $eventName
     * @throws \Exception
     */
    public function subscribe(string $eventName): void
    {
        $this->getConnection()
            ->channel()
            ->queue_bind($this->getQueueName(), $this->getExchangeName(), $eventName);
    }

    /**
     * @param string $eventName
     * @throws \Exception
     */
    public function unsubscribe(string $eventName): void
    {
        $this->getConnection()
            ->channel()
            ->queue_unbind($this->getQueueName(), $this->getExchangeName(), $eventName);
    }

    /**
     * @return AbstractConnection
     * @throws \Exception
     */
    private function getConnection(): AbstractConnection
    {
        return $this->connection->connect();
    }
}
