<?php

return [
    'connection' => [
        'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,
        'hosts' => [
            [
                'host' => '127.0.0.1',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/',
            ],
        ],
        'options' => [
            'ssl_options' => [
                'cafile' => null,
                'local_cert' => null,
                'local_key' => null,
                'verify_peer' => true,
                'passphrase' => null,
            ],
        ]
    ],
    'service_name' => '<service_name>',
    //'general_exchange' => 'events_bus_exchange',
    'event_formatter' => IliaKologrivov\RabbitMQGlobalEventBus\Formatters\JsonEventFormatter::class,
];
