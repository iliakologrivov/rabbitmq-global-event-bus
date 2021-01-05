<?php

declare(strict_types=1);

namespace IliaKologrivov\RabbitMQGlobalEventBus\Formatters;

class JsonEventFormatter implements EventFormatterInterface
{
    /**
     * @param string $json
     * @return array
     */
    public function decode(string $json): array {
        $value = json_decode($json, true);

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('JSON decoding error: ' . json_last_error_msg());
        }

        return $value;
    }

    /**
     * @param $data
     * @return string
     */
    public function encode($data): string
    {
        $value = json_encode($data, JSON_UNESCAPED_UNICODE);

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('JSON decoding error: ' . json_last_error_msg());
        }

        return $value;
    }
}
