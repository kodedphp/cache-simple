<?php

namespace Koded\Caching\Serializer;

use PHPUnit\Framework\TestCase;

class JsonSerializerEncodingTest extends TestCase
{

    public function test_serialize_without_unicode_option()
    {
        $this->assertEquals(
            '{"diva":"Bj\u00f6rk Gu\u00f0mundsd\u00f3ttir"}',
            (new JsonSerializer(JSON_UNESCAPED_SLASHES))->serialize(require __DIR__ . '/../fixtures/utf8-file.php')
        );
    }

    public function test_serialize_with_unicode_option()
    {
        $this->assertEquals(
            '{"diva":"Björk Guðmundsdóttir"}',
            (new JsonSerializer)->serialize(require __DIR__ . '/../fixtures/utf8-file.php')
        );
    }

    public function test_serialize_when_encoding_fails_because_php_is_retarded()
    {
        $actual = (new JsonSerializer)->serialize(require __DIR__ . '/../fixtures/non-utf8-file.php');
        $this->assertSame('', $actual, 'json_encode() fails when data is not UTF-8');
    }
}
