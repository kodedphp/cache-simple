<?php

namespace Koded\Caching;

use Cache\IntegrationTests\SimpleCacheTest;
use Koded\Caching\Tests\Integration\SimpleCacheIntegrationTrait;
use Psr\SimpleCache\CacheInterface;

class MemcachedClientTest extends SimpleCacheTest
{
    use SimpleCacheIntegrationTrait;

    /**
     * @return CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        return simple_cache_factory('memcached');
    }

    protected function setUp()
    {
        if (false === extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extension is not loaded.');
        }

        if (getenv('CI')) {
            putenv('MEMCACHED_POOL=[["127.0.0.1", 11211]]');
        } else {
            putenv('MEMCACHED_POOL=[["memcached", 11211]]');
        }

        parent::setUp();
        $this->cache->clear();

        $this->skippedTests = [
            'testBasicUsageWithLongKey' => 'Memcached max key length is 250 chars',

            'testSet' => '',
            'testSetTtl' => '',
            'testSetExpiredTtl' => '',
            'testGet' => '',
            'testDelete' => '',
            'testClear' => '',

            'testSetMultiple' => '',
            'testSetMultipleWithIntegerArrayKey' => '',
            'testSetMultipleTtl' => '',
            'testSetMultipleExpiredTtl' => '',
            'testSetMultipleWithGenerator' => '',
            'testGetMultiple' => '',

//            'testGetMultipleWithGenerator' => '',
//            'testDeleteMultiple' => '',
//            'testGetMultipleWithGenerator' => '',
//            'testDeleteMultiple' => '',
//            'testDeleteMultipleGenerator' => '',
//            'testHas' => '',
//            'testGetInvalidKeys' => '',
//            'testGetMultipleInvalidKeys' => '',
//            'testGetMultipleNoIterable' => '',
//            'testSetInvalidKeys' => '',
//            'testSetMultipleInvalidKeys' => '',
//            'testSetMultipleNoIterable' => '',
//            'testHasInvalidKeys' => '',
//            'testDeleteInvalidKeys' => '',
//            'testDeleteMultipleInvalidKeys' => '',
//            'testDeleteMultipleNoIterable' => '',
//            'testSetInvalidTtl' => '',
//            'testSetMultipleInvalidTtl' => '',
//            'testNullOverwrite' => '',
//            'testDataTypeString' => '',
//            'testDataTypeInteger' => '',
//            'testDataTypeFloat' => '',
//            'testDataTypeBoolean' => '',
//            'testDataTypeArray' => '',
//            'testDataTypeObject' => '',
//            'testBinaryData' => '',
//            'testSetValidKeys' => '',
//            'testSetMultipleValidKeys' => '',
//            'testSetValidData' => '',
//            'testSetMultipleValidData' => '',
//            'testObjectAsDefaultValue' => '',
//            'testObjectDoesNotChangeInCache' => '',
        ];
    }
}
