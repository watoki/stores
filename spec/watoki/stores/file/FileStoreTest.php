<?php
namespace spec\watoki\stores\file;

use spec\watoki\stores\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\file\SerializerRepository;

class FileStoreTest extends Specification {

    function testCreate() {
        $this->store->createAt('here', new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));
        $this->assertExists('here');
        $this->assertContent('here', '{
            "boolean": true,
            "integer": 42,
            "float": 1.6,
            "string": "Hello",
            "dateTime": "2001-01-01T00:00:00+01:00",
            "null": null
        }');
    }

    function testRead() {
        $this->markTestIncomplete();
    }

    function testUpdate() {
        $this->markTestIncomplete();
    }

    function testDelete() {
        $this->markTestIncomplete();
    }

    /** @var TestStore */
    private $store;

    private $tmpDir;

    protected function setUp() {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'watokistores';
        $this->clear($this->tmpDir);
        mkdir($this->tmpDir);
        $this->store = new TestStore(new SerializerRepository(), $this->tmpDir);
    }

    private function assertExists($key) {
        $this->assertFileExists($this->tmpDir . DIRECTORY_SEPARATOR . $key);
    }

    private function assertContent($key, $content) {
        $this->assertEquals(str_replace(' ', '', $content),
            str_replace(' ', '', file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . $key)));
    }

    private function clear($dir) {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->clear($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }
}