<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus;

use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Class RabbitMQConnector
 * @package IliaKologrivov\RabbitMQGlobalEventBus
 */
class EventsBusConnector
{
    private $connection;

    private $class;

    private $hosts;

    private $options;

    /**
     * RabbitMQConnector constructor.
     *
     * @param array $hosts
     * @param array $options
     * @param string|null $class
     */
    public function __construct(array $hosts, array $options = [], ?string $class = null)
    {
        $this->hosts = $hosts;
        $this->options = $options;
        $this->class = $class;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function connect(): AbstractConnection
    {
        if (empty($this->connection)) {
            /**
             * @var $connection AbstractConnection
             */
            $connection = $this->class ?? AMQPLazyConnection::class;

            $this->connection = $connection::create_connection($this->hosts, $this->options);
        }

        return $this->connection;
    }
}
