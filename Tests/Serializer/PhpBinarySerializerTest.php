<?php

namespace Koded\Caching\Serializer;

use PHPUnit\Framework\TestCase;

class PhpBinarySerializerTest extends TestCase
{

    /** @var PhpSerializer */
    private $SUT;

    /** @var array */
    private $original;

    /** @var string */
    private $igb;

    public function test_serialize()
    {
        $this->assertEquals(igbinary_serialize($this->original), $this->SUT->serialize($this->original));
    }

    public function test_unserialize()
    {
        $this->assertEquals($this->original, $this->SUT->unserialize($this->igb));
    }

    protected function setUp()
    {
        if (false === function_exists('igbinary_serialize')) {
            $this->markTestSkipped('igbinary extension is not loaded');
        }

        $this->SUT = new PhpSerializer(true);
        $this->original = require __DIR__ . '/../fixtures/test-config.php';
        $this->igb = igbinary_serialize($this->original);
    }
}
