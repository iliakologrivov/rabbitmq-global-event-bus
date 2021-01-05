<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Formatters;

interface EventFormatterInterface
{
    /**
     * @param string $json
     * @return mixed
     */
    public function decode(string $json);

    /**
     * @param mixed $data
     * @return string
     */
    public function encode($data): string;
}
