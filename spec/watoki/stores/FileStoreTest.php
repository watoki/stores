<?php
namespace spec\watoki\stores;

use spec\watoki\stores\fixtures\StoresTestEntity;
use spec\watoki\stores\fixtures\StoresTestEntity_Child;
use spec\watoki\stores\fixtures\StoresTestEntity_GrandChild;
use watoki\scrut\Specification;
use watoki\stores\common\GenericSerializer;
use watoki\stores\file\FileStore;
use watoki\stores\file\raw\File;
use watoki\stores\file\raw\RawFileStore as RawFileStore;
use watoki\stores\file\serializers\DateTimeSerializer;
use watoki\stores\file\serializers\JsonSerializer;
use watoki\stores\sqlite\serializers\StringSerializer;

class FileStoreTest extends Specification {

    function testCreate() {
        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'), array('some' => array(42, 73)));
        $this->store->create($entity, 'there/here');

        $this->assertExists('there/here');
        $this->assertContent('there/here', '{
            "boolean": true,
            "integer": 42,
            "float": 1.6,
            "string": "Hello",
            "dateTime": "2001-01-01T00:00:00+00:00",
            "null": null,
            "nullDateTime": null,
            "array":{"some":[42, 73]},
            "child": {
                "one": "uno",
                "two": "dos",
                "child": { "foo": "bar" }
            }
        }');
    }

    function testCreateRawFile() {
        $this->store = new RawFileStore($this->tmpDir);
        $this->store->create(new File('Some text'), 'here');
        $this->assertRawContent('here', 'Some text');
    }

    function testRead() {
        $dateTime = new \DateTime('2001-01-01');
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', $dateTime), 'that/file');

        $this->initStore();
        /** @var StoresTestEntity $entity */
        $entity = $this->store->read('that/file');

        $this->assertSame(true, $entity->boolean);
        $this->assertSame(42, $entity->integer);
        $this->assertSame(1.6, $entity->float);
        $this->assertSame('Hello', $entity->string);
        $this->assertEquals($dateTime->format('c'), $entity->dateTime->format('c'));
        $this->assertNull($entity->null);
    }

    function testUpdate() {
        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity, 'here');

        $entity->string = 'Hello back';
        $entity->array = array('foo' => 'bar', array(42, 73));
        $entity->nullDateTime = new \DateTime('2012-12-12 12:12:12');
        $this->store->update($entity);

        $this->assertContent('here', '{
            "boolean": true,
            "integer": 42,
            "float": 1.6,
            "string": "Hello back",
            "dateTime": "2001-01-01T00:00:00+00:00",
            "null": null,
            "nullDateTime": "2012-12-12T12:12:12+00:00",
            "array":{"foo":"bar","0":[42,73]},
            "child": {
                "one": "uno",
                "two": "dos",
                "child": { "foo": "bar" }
            }
        }');
    }

    function testDelete() {
        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity, 'here');

        $this->store->delete('here');

        $this->assertNotExists('here');
    }

    function testKeys() {
        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity, 'file');
        $this->store->create($entity, 'some/file');
        $this->store->create($entity, 'some/bar');
        $this->store->create($entity, 'some/deeper/file');

        $keys = $this->store->keys();

        $this->assertEquals(array('file', 'some/bar', 'some/deeper/file', 'some/file'), $keys);
    }

    function testGenericSerializer() {
        $get = function ($name) {
            return function ($object) use ($name) {
                return $object->$name;
            };
        };
        $set = function ($name) {
            return function ($object, $value) use ($name) {
                $object->$name = $value;
            };
        };

        $innerSerializer = new GenericSerializer(function () { return new \StdClass(); });
        $innerSerializer->defineChild('one', new StringSerializer(), $get('one'), $set('one'));
        $innerSerializer->defineChild('two', new StringSerializer(), $get('two'), $set('two'));

        $serializer = new JsonSerializer(function () { return new \StdClass(); });
        $serializer->defineChild('string', new StringSerializer(), $get('string'), $set('string'));
        $serializer->defineChild('date', new DateTimeSerializer(), $get('date'), $set('date'));
        $serializer->defineChild('inner', $innerSerializer, $get('inner'), $set('inner'));

        $this->store = new FileStore($serializer, $this->tmpDir);

        $entity = new \StdClass();
        $entity->string = "foo";
        $entity->date = new \DateTime('2001-01-01');
        $entity->inner = new \StdClass();
        $entity->inner->one = 'uno';
        $entity->inner->two = 'dos';

        $this->store->create($entity, 'bar');

        $this->assertContent('bar', '{
            "string": "foo",
            "date": "2001-01-01T00:00:00+00:00",
            "inner":{
                "one": "uno",
                "two": "dos"
            }
        }');

        $inflated = $this->store->read('bar');
        $this->assertEquals($entity, $inflated);
    }

    ############################# SET-UP ##############################

    /** @var FileStore */
    private $store;

    private $tmpDir;

    protected function setUp() {
        parent::setUp();

        $this->tmpDir = __DIR__ . DIRECTORY_SEPARATOR . '_tmp_';
        $this->clear($this->tmpDir);
        mkdir($this->tmpDir, 0777, true);

        $this->initStore();

        date_default_timezone_set('UTC');
    }

    private function initStore() {
        $this->store = FileStore::forClass(StoresTestEntity::$CLASS, $this->tmpDir);
    }

    protected function tearDown() {
        $this->clear($this->tmpDir);
        parent::tearDown();
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