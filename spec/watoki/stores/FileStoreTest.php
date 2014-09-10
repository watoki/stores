<?php
namespace spec\watoki\stores;

use spec\watoki\stores\lib\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\file\raw\File;
use watoki\stores\file\SerializerRepository;
use watoki\stores\file\FileStore;
use watoki\stores\file\raw\RawFileStore as RawFileStore;

class FileStoreTest extends Specification {

    function testCreate() {
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')), 'there/here');
        $this->assertExists('there/here');
        $this->assertContent('there/here', '{
            "boolean": true,
            "integer": 42,
            "float": 1.6,
            "string": "Hello",
            "dateTime": "2001-01-01T00:00:00+01:00",
            "null": null
        }');
    }

    function testCreateRawFile() {
        $this->store = new RawFileStore(new SerializerRepository(), $this->tmpDir);
        $this->store->create(new File('Some text'), 'here');
        $this->assertRawContent('here', 'Some text');
    }

    function testRead() {
        $dateTime = new \DateTime('2001-01-01');
        $this->store->create(new lib\TestEntity(true, 42, 1.6, 'Hello', $dateTime), 'here');

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
        $entity = new lib\TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity, 'here');

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
        $this->store->create($entity, 'here');

        $this->store->delete($entity);

        $this->assertNotExists('here');
    }

    function testKeys() {
        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity, 'file');
        $this->store->create($entity, 'some/file');
        $this->store->create($entity, 'some/bar');
        $this->store->create($entity, 'some/deeper/file');

        $keys = $this->store->keys();

        $this->assertEquals(array('file', 'some/bar', 'some/deeper/file', 'some/file'), $keys);
    }

    function testFind() {
        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity, 'some/file.txt');
        $this->store->create($entity, 'some/other.txt');
        $this->store->create($entity, 'some/file.not');

        $matches = $this->store->find('some/*.txt');

        $this->assertEquals(array('some/file.txt', 'some/other.txt'), $matches);
    }

    ############################# SET-UP ##############################

    /** @var FileStore */
    private $store;

    private $tmpDir;

    protected function setUp() {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'watokistores';
        $this->clear($this->tmpDir);
        mkdir($this->tmpDir);
        $this->store = new FileStore(TestEntity::$CLASS, new SerializerRepository(), $this->tmpDir);
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

    private function assertRawContent($key, $content) {
        $this->assertEquals($content, file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . $key));
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