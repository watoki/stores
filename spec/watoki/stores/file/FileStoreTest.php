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
        $dateTime = new \DateTime('2001-01-01');
        $this->store->createAt('here', new TestEntity(true, 42, 1.6, 'Hello', $dateTime));

        /** @var TestEntity $entity */
        $entity = $this->store->read('here');

        $this->assertSame(true, $entity->getBoolean());
        $this->assertSame(42, $entity->getInteger());
        $this->assertSame(1.6, $entity->getFloat());
        $this->assertSame('Hello', $entity->getString());
        $this->assertEquals($dateTime->format('c'), $entity->getDateTime()->format('c'));
        $this->assertNull($entity->getNull());
    }

    function testUpdate() {
        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->createAt('here', $entity);

        $entity->setString('Hello back');
        $this->store->update($entity);

        $this->assertContent('here', '{
            "boolean": true,
            "integer": 42,
            "float": 1.6,
            "string": "Hello back",
            "dateTime": "2001-01-01T00:00:00+01:00",
            "null": null
        }');
    }

    function testDelete() {
        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->createAt('here', $entity);

        $this->store->delete($entity);

        $this->assertNotExists('here');
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

    private function assertNotExists($key) {
        $this->assertFileNotExists($this->tmpDir . DIRECTORY_SEPARATOR . $key);
    }

    private function assertContent($key, $content) {
        $this->assertEquals(json_decode($content, true),
            json_decode(file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . $key), true));
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