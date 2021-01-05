<?php

namespace Tests\Unit;

use IliaKologrivov\RabbitMQGlobalEventBus\Formatters\JsonEventFormatter;
use PHPUnit\Framework\TestCase;

class JsonEventFormatterTest extends TestCase
{
    private $formatter;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->formatter = new JsonEventFormatter();
    }

    public function testDecode()
    {
        $result = $this->formatter->decode('[1,2]');

        $this->assertIsArray($result);
        $this->assertTrue($result === [1, 2]);
    }

    public function testEncode()
    {
        $result = $this->formatter->encode([1, 2]);

        $this->assertIsString($result);
        $this->assertTrue($result === '[1,2]');
    }

    public function testExceptionDecode()
    {
        $this->expectException(\RuntimeException::class);
        $this->formatter->decode('[');
    }

    public function testExceptionEncode()
    {
        $this->expectException(\RuntimeException::class);
        $this->formatter->encode("\xB1\x31");
    }
}
