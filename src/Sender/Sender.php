<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Sender;

use IliaKologrivov\RabbitMQGlobalEventBus\EventsBusConnector;
use IliaKologrivov\RabbitMQGlobalEventBus\Exception\SenderException;
use IliaKologrivov\RabbitMQGlobalEventBus\Formatters\EventFormatterInterface;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Pusher
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Sender
 */
class Sender
{
    /**
     * @var EventsBusConnector
     */
    private $connection;

    /**
     * @var EventHandler
     */
    private $handler;

    /**
     * @var string
     */
    private $generalExchange;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $eventNameSeparator;

    /**
     * @var EventFormatterInterface
     */
    private $eventFormatter;

    /**
     * Sender constructor.
     * @param EventsBusConnector $connection
     * @param EventHandler $handler
     * @param EventFormatterInterface $eventFormatter
     * @param $options
     * @throws SenderException
     */
    public function __construct(EventsBusConnector $connection, EventHandler $handler, EventFormatterInterface $eventFormatter, $options)
    {
        if (empty($options['service_name'])) {
            throw new SenderException('service name cannot be empty!');
        }

        $this->connection = $connection;
        $this->handler = $handler;
        $this->eventFormatter = $eventFormatter;
        $this->serviceName = $options['service_name'];
        $this->generalExchange = $options['general_exchange'] ?? 'events_bus';
        $this->eventNameSeparator = (string) ($options['event_name_separator'] ?? '.');
    }

    /**
     * @param AbstractEvent $event
     * @throws \Exception
     */
    public function send(AbstractEvent $event): void
    {
        $this->getConnection()
            ->channel()
            ->basic_publish(
                new AMQPMessage($this->eventFormatter->encode($this->handler->handle($event))),
                $this->getGeneralExchangeName(),
                $this->concatEventName($event->getName())
            );
    }

    private function getConnection(): AbstractConnection
    {
        return $this->connection->connect();
    }

    private function getGeneralExchangeName(): string
    {
        return $this->generalExchange;
    }

    private function concatEventName(string $eventName): string
    {
        return $this->serviceName . $this->eventNameSeparator . $eventName;
    }
}
