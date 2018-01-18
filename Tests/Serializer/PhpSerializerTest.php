<?php

namespace Koded\Caching\Serializer;

use PHPUnit\Framework\TestCase;

class PhpSerializerTest extends TestCase
{

    /** @var PhpSerializer */
    private $SUT;

    /** @var array */
    private $original;

    /** @var string */
    private $serialized;

    public function test_serialize()
    {
        $this->assertEquals($this->serialized, $this->SUT->serialize($this->original));
    }

    public function test_unserialize()
    {
        $this->assertEquals($this->original, $this->SUT->unserialize($this->serialized));
    }

    protected function setUp()
    {
        $this->SUT = new PhpSerializer(false);
        $this->original = require __DIR__ . '/../fixtures/test-config.php';
        $this->serialized = serialize($this->original);
    }
}
