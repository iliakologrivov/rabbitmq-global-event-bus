<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Worker;

use Exception;
use IliaKologrivov\RabbitMQGlobalEventBus\Formatters\EventFormatterInterface;
use IliaKologrivov\RabbitMQGlobalEventBus\Subscriber\Subscriber;
use IliaKologrivov\RabbitMQGlobalEventBus\EventsBusConnector;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

/**
 * Class Worker
 * @package IliaKologrivov\RabbitMQGlobalEventBus\Worker
 */
class Worker
{
    /**
     * @var EventsBusConnector
     */
    private $connection;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var EventDispatcherContract
     */
    private $eventDispatcher;

    /**
     * @var HandlerExceptionContract
     */
    private $handlerException;

    /**
     * @var EventFormatterInterface
     */
    private $eventFormatter;

    /**
     * @var EventsMap
     */
    private $eventsMap;

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @var int
     */
    private $timeout = 60;

    /**
     * @var int
     */
    private $sleep = 3;

    /**
     * @var string|null
     */
    private $queueName;

    /**
     * @var bool
     */
    private $gotMessage = false;

    /**
     * @var bool
     */
    private $isExit = false;

    /**
     * @var bool
     */
    private $isSupportsAsyncSignals;

    /**
     * Worker constructor.
     *
     * @param EventsBusConnector $connection
     * @param EventDispatcherContract $eventDispatcher
     * @param HandlerExceptionContract $handlerException
     * @param EventFormatterInterface $eventFormatter
     * @param EventsMap $eventsMap
     * @param Subscriber $subscriber
     * @param string $serviceName
     */
    public function __construct(
        EventsBusConnector $connection,
        EventDispatcherContract $eventDispatcher,
        HandlerExceptionContract $handlerException,
        EventFormatterInterface $eventFormatter,
        EventsMap $eventsMap,
        Subscriber $subscriber,
        string $serviceName
    )
    {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->handlerException = $handlerException;
        $this->eventFormatter = $eventFormatter;
        $this->eventsMap = $eventsMap;
        $this->subscriber = $subscriber;
        $this->serviceName = $serviceName;

        $this->isSupportsAsyncSignals = extension_loaded('pcntl');
    }

    /**
     * @param int $sleep
     * @param int $timeout
     * @param string|null $queueName
     * @param bool $once
     * @return void
     * @throws Exception
     */
    public function daemon(int $sleep = 3, int $timeout = 30, ?string $queueName = null, bool $once = false): void
    {
        $this->timeout = $timeout;
        $this->sleep = $sleep;
        $this->queueName = $queueName;

        if ($this->isSupportsAsyncSignals) {
            $this->listenForSignals();
        }

        /**
         * @var AMQPChannel $channel
         */
        $channel = $this->getConnection()->channel();

        $this->subscriber->setQueueName($this->getQueueName());

        foreach ($this->eventsMap->getEventsList() as $eventName) {
            $this->subscriber->subscribe($eventName);
        }

        $callback = function (AMQPMessage $message) use ($channel) {
            $this->gotMessage = true;
            $eventName = $message->getRoutingKey();
            $eventClass = $this->eventsMap->getByName($eventName);

            if ($this->isSupportsAsyncSignals) {
                $this->registerTimeoutHandler();
            }

            if ($eventClass !== null) {
                try {
                    $event = new $eventClass($this->eventFormatter->decode($message->getBody()));
                    $this->eventDispatcher->dispatch($event);
                    $channel->basic_ack($message->getDeliveryTag());
                } catch (Throwable $exception) {
                    $this->handlerException->handle($exception);

                    $channel->basic_reject($message->getDeliveryTag(), false);

                    $this->stopIfLostConnection($exception);
                }
            } else {
                $this->subscriber->unsubscribe($eventName);
            }

            if ($this->isSupportsAsyncSignals) {
                $this->resetTimeoutHandler();
            }
        };

        $channel->basic_consume($this->getQueueName(), '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            try {
                $channel->wait(null, true);
            } catch (AMQPRuntimeException $exception) {
                $this->handlerException->handle($exception);

                $this->kill(1);
            } catch (Throwable $exception) {
                $this->handlerException->handle($exception);

                $this->stopIfLostConnection($exception);
            }

            if ($this->isExit || ($once && $this->gotMessage)) {
                break;
            }

            if (! $this->gotMessage) {
                sleep($this->sleep);
            }

            $this->gotMessage = false;
        }
    }

    /**
     * @return AbstractConnection
     * @throws Exception
     */
    private function getConnection(): AbstractConnection
    {
        return $this->connection->connect();
    }

    protected function stopIfLostConnection(Throwable $exception): void
    {
        if (DetectsLostConnections::causedByLostConnection($exception)) {
            $this->isExit = true;
        }
    }

    public function kill(int $status = 0)
    {
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit($status);
    }

    private function getQueueName(): string
    {
        return $this->queueName ?? $this->serviceName;
    }

    protected function listenForSignals():void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () {
            $this->isExit = true;
        });
    }

    protected function registerTimeoutHandler(): void
    {
        pcntl_signal(SIGALRM, function () {
            $this->kill(1);
        });

        pcntl_alarm($this->timeout);
    }

    protected function resetTimeoutHandler(): void
    {
        pcntl_alarm(0);
    }
}
