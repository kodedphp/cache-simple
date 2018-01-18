<?php

namespace Koded\Caching\Serializer;

use Koded\Caching\CacheException;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{

    const SERIALIZED_JSON = '{"php-key-1":{},"normalizer":"php","timeout":2.5}';

    /** @var JsonSerializer */
    private $SUT;

    /** @dataProvider data */
    public function test_serialize($data)
    {
        $this->assertEquals(self::SERIALIZED_JSON, $this->SUT->serialize($data));
    }

    public function test_unserialize()
    {
        $this->assertEquals(json_decode(self::SERIALIZED_JSON, true), $this->SUT->unserialize(self::SERIALIZED_JSON));
    }

    public function test_unserialize_error()
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('[Cache Exception] Syntax error');

        $this->SUT->unserialize('');
    }

    public function data()
    {
        return [
            [
                require __DIR__ . '/../fixtures/test-config.php'
            ]
        ];
    }

    protected function setUp()
    {
        $this->SUT = new JsonSerializer;
    }
}
