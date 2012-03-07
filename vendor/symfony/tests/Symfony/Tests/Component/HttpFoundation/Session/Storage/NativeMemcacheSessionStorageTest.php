<?php

namespace Symfony\Tests\Component\HttpFoundation\Session\Storage;

use Symfony\Component\HttpFoundation\Session\Storage\NativeMemcacheSessionStorage;

/**
 * Test class for NativeMemcacheSessionStorage.
 *
 * @author Drak <drak@zikula.org>
 *
 * @runTestsInSeparateProcesses
 */
class NativeMemcacheSessionStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveHandlers()
    {
        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('Skipped tests SQLite extension is not present');
        }

        $storage = new NativeMemcacheSessionStorage('tcp://127.0.0.1:11211?persistent=0', array('name' => 'TESTING'));
        $this->assertEquals('memcache', ini_get('session.save_handler'));
        $this->assertEquals('tcp://127.0.0.1:11211?persistent=0', ini_get('session.save_path'));
        $this->assertEquals('TESTING', ini_get('session.name'));
    }
}
