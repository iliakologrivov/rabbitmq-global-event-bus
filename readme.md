# Событийный механизм на основе RabbitMQ
Когда возникает событие о котором необходимо уведомить другие сервисы, событие необходимо передать через "шину событий", шина основана на RabbitMQ.

### Схема взаимодействия:
При возникновении события сервис отправляет его в общий exchange который доступен ему только на запись, 
далее событие с помощью bindings (роутеров) расходится на все exchange сервисов которые доступны сервису для чтения и конфигурации, 
в зависимости от установленных bindings событие отправляется в требуемую queue или exchange для дальнейшей обработки.

![](schema.png)
## Name format
* events_bus - general exchange, write only for services.
* <service_name> - exchange for service, all access for service.
* <service_name> - queue for service.
* <service_name>.<event.name> - full event name.

[Для создания exchange в rabbit треюуется в начале создать exchange с типом fanout (с тестовым сообщением) после чего переделать его в тип direct.](https://www.rabbitmq.com/blog/2010/10/19/exchange-to-exchange-bindings/) 
 
### Events format
Сообщения передаются в формате json (по умолчанию)
Поля объектов должны именоваться с использованием camelCase и находится в свойстве payload объекта события.
В объекте события так-же должно присутствовать свойство date, в котором находится дата и время события в формате YYYY-MM-DDThh:mm:ssTZD (RFC 3339)

```json
{
    "date": "1997-07-16T19:20:30+01:00",
    "payload": {
	    "id": 10,
	    "isActive": true,
	    "shortName": "KARL"
    }
}
```

## Example using
```php
$config = require('config/event_bus.php');
$serviceName = $config['service_name'];

$connection = new IliaKologrivov\RabbitMQGlobalEventBus\EventsBusConnector($config['connection']['hosts'], $config['connection']['options'], $config['connection']['connection']);

//Example push event
$testMiddleware = new class implements \IliaKologrivov\RabbitMQGlobalEventBus\Sender\EventMiddlewareContract
{
    public function handler(string $eventName, $payload): array
    {
        $payload['content'] = strtoupper($payload['content'] ?? '');

        return $payload;
    }
};

$eventHandler = new \IliaKologrivov\RabbitMQGlobalEventBus\Sender\EventHandler();
$eventHandler->addMiddleware($testMiddleware);

$eventFormatter = new \IliaKologrivov\RabbitMQGlobalEventBus\Formatters\JsonEventFormatter();

$pusher = new \IliaKologrivov\RabbitMQGlobalEventBus\Sender\Sender(
    $connection, 
    $eventHandler,
    $eventFormatter,
    [
        'service_name' => $serviceName,
        //'general_exchange' => 'events_bus_exchange',
    ]
);

$testEvent = new class (10, true, 'KARL') extends \IliaKologrivov\RabbitMQGlobalEventBus\Sender\AbstractEvent
{
    public $id;
    public $isActive;
    public $shortName;

    public function getName():string
    {
        return 'test.event';
    }

    public function __construct(int $id, bool $isActive, string $shortName)
    {
        $this->id = $id;
        $this->isActive = $isActive;
        $this->shortName = $shortName;
    }
};

$pusher->send($testEvent);

//example subscribe/unsubscribe
$subscriber = new \IliaKologrivov\RabbitMQGlobalEventBus\Subscriber\Subscriber($connection, [
    'service_name' => $serviceName,
    //'exchange_name' => $serviceName . '_v2_exchange',
    //'queue_name' => $serviceName . '_v2_queue',
]);
$fullEventNameForSubscribe = 'test_service_name.tests.event';
$subscriber->subscribe($fullEventNameForSubscribe);
$subscriber->unsubscribe($fullEventNameForSubscribe);

//example listener events
$eventDispatcher = new class implements \IliaKologrivov\RabbitMQGlobalEventBus\Worker\EventDispatcherContract
{
    public function dispatch(object $event) {
        // dispatch event
    }
};

$handlerException = new class implements \IliaKologrivov\RabbitMQGlobalEventBus\Worker\HandlerExceptionContract
{
    public function handle(Throwable $exception) {
        //domain logic
    }
};

$eventsMap = new \IliaKologrivov\RabbitMQGlobalEventBus\Worker\EventsMap([
    //'test_service_name.tests.event' => TestEvent::class,
]);

$worker = new \IliaKologrivov\RabbitMQGlobalEventBus\Worker\Worker(
    $connection,
    $eventDispatcher,
    $handlerException,
    $eventFormatter,
    $eventsMap,
    $subscriber,
    $serviceName
);

$worker->daemon();
```
## Libraries for frameworks
 - [laravel](https://github.com/iliakologrivov/rabbitmq-global-event-bus-laravel)
 - Symfony (in developing)
 - Yii (in developing)
