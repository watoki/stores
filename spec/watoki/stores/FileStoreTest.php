<?php
namespace spec\watoki\stores;

use watoki\reflect\type\ClassType;
use watoki\scrut\Specification;
use watoki\stores\common\NoneSerializer;
use watoki\stores\file\FileStore;
use watoki\stores\file\serializers\DateIntervalSerializer;
use watoki\stores\file\serializers\DateTimeSerializer;
use watoki\stores\file\serializers\JsonSerializer;
use watoki\stores\SerializerRegistry;

/**
 * @property \spec\watoki\reflect\fixtures\ClassFixture class <-
 * @property \watoki\scrut\ExceptionFixture try <-
 */
class FileStoreTest extends Specification {

    function testCreate() {
        $this->givenAnEntityWith('foo', new \DateTime('2001-01-01'));
        $this->whenICreateTheEntityAs('there/here');

        $this->thenThereShouldBeAFile('there/here');
        $this->then_ShouldContain('there/here', '{
            "one": "foo",
            "two": "2001-01-01T00:00:00+00:00"
        }');
    }

    function testRead() {
        $this->givenAFile_Containing('that/file', '{
            "one": "foo",
            "two": "2001-01-01T00:00:00+00:00"
        }');

        $this->whenIRead('that/file');

        $this->then_ShouldBe('one', 'foo');
        $this->then_ShouldBe('two', new \DateTime('2001-01-01 +00:00'));
    }

    function testNotExisting() {
        $this->whenITryToRead('not/existing');
        $this->try->thenTheException_ShouldBeThrown('File [not/existing] does not exist.');
    }

    function testFoldersAreNotFiles() {
        $this->givenAFile_Containing('foo/bar', '');
        $this->whenITryToRead('foo');
        $this->try->thenTheException_ShouldBeThrown('File [foo] does not exist.');
    }

    function testUpdate() {
        $this->givenAFile_Containing('this/file', '{
            "one": "foo",
            "two": "2001-01-01T00:00:00+00:00"
        }');
        $this->whenIRead('this/file');

        $this->givenISet_To('one', 'bar');
        $this->givenISet_To('two', new \DateTime('2002-02-02'));

        $this->whenIUpdateTheEntity();

        $this->then_ShouldContain('this/file', '{
            "one": "bar",
            "two": "2002-02-02T00:00:00+00:00"
        }');
    }

    function testDelete() {
        $this->givenAnEntityWith('foo', new \DateTime('2001-01-01'));
        $this->whenICreateTheEntityAs('file/here');

        $this->whenIDelete('file/here');
        $this->thenThereShouldBeNoFile('here');
    }

    function testKeys() {
        $this->givenAnEntityWith('foo', new \DateTime('2001-01-01'));
        $this->whenICreateTheEntityAs('file');
        $this->whenICreateTheEntityAs('some/file');
        $this->whenICreateTheEntityAs('some/bar');
        $this->whenICreateTheEntityAs('some/deeper/file');

        $this->thenTheKeysShouldBe(array('file', 'some/bar', 'some/deeper/file', 'some/file'));
    }

    function testStandardValues() {
        $this->class->givenTheClass_WithTheBody('FileStore\StandardValues', '
            /** @var string */
            public $string;

            /** @var boolean */
            public $boolean;

            /** @var float */
            public $float;

            /** @var null|string */
            public $null;

            /** @var \DateTime */
            public $date;
        ');
        $this->givenTheEntityIsAnInstanceOf('FileStore\StandardValues');
        $this->givenAStoreFor('FileStore\StandardValues');

        $this->givenISet_To('string', 'Some string');
        $this->givenISet_To('boolean', true);
        $this->givenISet_To('float', 3.1415);
        $this->givenISet_To('date', new \DateTime('2001-02-03'));

        $this->whenICreateTheEntityAs('foo');
        $this->then_ShouldContain('foo', '{
            "string": "Some string",
            "boolean": true,
            "float": 3.1415,
            "null": null,
            "date": "2001-02-03T00:00:00+00:00"
        }');

        $this->givenAStoreFor('FileStore\StandardValues');
        $this->whenIRead('foo');
        $this->thenTheEntityShouldBeAnInstanceOf('FileStore\StandardValues');
        $this->then_ShouldBe('string', 'Some string');
        $this->then_ShouldBe('date', new \DateTime('2001-02-03 +00:00'));
    }

    function testArrayValues() {
        $this->class->givenTheClass_WithTheBody('FileStore\ArrayValues', '
            /** @var array|int[] */
            public $integers;

            /** @var array|string[] */
            public $dictionary;

            /** @var array|\DateTime[] */
            public $dates;

            /** @var array|array[]|int[][] */
            public $deep;
        ');
        $this->givenTheEntityIsAnInstanceOf('FileStore\ArrayValues');
        $this->givenAStoreFor('FileStore\ArrayValues');

        $this->givenISet_To('integers', array(1, 42, 73, 12));
        $this->givenISet_To('dictionary', array('one' => 'uno', 'two' => 'dos'));
        $this->givenISet_To('dates', array(new \DateTime('2001-01-01'), new \DateTime('2002-02-02')));
        $this->givenISet_To('deep', array(1 => array("one"), 2 => array("two")));

        $this->whenICreateTheEntityAs('foo');
        $this->then_ShouldContain('foo', '{
            "integers": [1, 42, 73, 12],
            "dictionary": {
                "one": "uno",
                "two": "dos"
            },
            "dates": [
                "2001-01-01T00:00:00+00:00",
                "2002-02-02T00:00:00+00:00"
            ],
            "deep": {
                "1": ["one"],
                "2": ["two"]
            }
        }');

        $this->givenAStoreFor('FileStore\ArrayValues');
        $this->whenIRead('foo');
        $this->then_ShouldBe('integers', array(1, 42, 73, 12));
        $this->then_ShouldBe('dates', array(new \DateTime('2001-01-01 +00'), new \DateTime('2002-02-02 +00')));
    }

    function testAmbiguousArrayValue() {
        $this->class->givenTheClass_WithTheBody('FileStore\AmbiguousArrayValue', '
            /** @var array|int[]|string[] */
            public $ambiguous;
        ');
        $this->givenTheEntityIsAnInstanceOf('FileStore\AmbiguousArrayValue');
        $this->whenITryToCreateAStoreFor('FileStore\AmbiguousArrayValue');
        $this->try->thenTheException_ShouldBeThrown(
            'Could not infer Serializer of [FileStore\AmbiguousArrayValue::ambiguous]: ' .
            'Ambiguous type.');
    }

    function testEmbeddedObjects() {
        $this->class->givenTheClass_WithTheBody('FileStore\Family', '
            /** @var array|Child[] */
            public $children;

            function __construct() {
                $this->children = array(
                    new Child("Bart", new Pet("Santas little helper", new \DateTime("1990-12-24"))),
                    new Child("Lisa", new Pet("Snowball", new \DateTime("1993-03-15")))
                );
            }
        ');
        $this->class->givenTheClass_WithTheBody('FileStore\Child', '
            /** @var string */
            public $name;

            /** @var Pet */
            public $pet;

            function __construct($name, $pet) {
                $this->name = $name;
                $this->pet = $pet;
            }
        ');
        $this->class->givenTheClass_WithTheBody('FileStore\Pet', '
            /** @var string */
            public $name;

            /** @var \DateTime */
            public $birthDate;

            function __construct($name, $birthDate) {
                $this->name = $name;
                $this->birthDate = $birthDate;
            }
        ');
        $this->givenTheEntityIsAnInstanceOf('FileStore\Family');
        $this->givenAStoreFor('FileStore\Family');

        $this->whenICreateTheEntityAs('simpsons');
        $this->then_ShouldContain('simpsons', '{
            "children": [
                {
                    "name": "Bart",
                    "pet": {
                        "name": "Santas little helper",
                        "birthDate": "1990-12-24T00:00:00+00:00"
                    }
                },
                {
                    "name": "Lisa",
                    "pet": {
                        "name": "Snowball",
                        "birthDate": "1993-03-15T00:00:00+00:00"
                    }
                }
            ]
        }');
    }

    function testSerializeDateTimeImmutable() {
        $registry = new SerializerRegistry();
        FileStore::registerDefaultSerializers($registry);

        $serializer = $registry->get(new ClassType('DateTimeImmutable'));
        $this->assertEquals($serializer, new DateTimeSerializer('DateTimeImmutable'));
        $this->assertEquals($serializer->serialize(new \DateTimeImmutable('2011-12-13 14:15:16')), '2011-12-13T14:15:16+00:00');
        $this->assertEquals($serializer->inflate('2011-12-13T14:15:16+00:00'), new \DateTimeImmutable('2011-12-13 14:15:16 +00:00'));
    }

    function testSerializeDateInterval() {
        $registry = new SerializerRegistry();
        FileStore::registerDefaultSerializers($registry);

        $serializer = $registry->get(new ClassType('DateInterval'));
        $this->assertEquals($serializer, new DateIntervalSerializer());
        $this->assertEquals($serializer->serialize(new \DateInterval('P3DT2H45M')), 'P3DT2H45M');
        $this->assertEquals($serializer->inflate('P3DT2H45M'), new \DateInterval('P3DT2H45M'));
    }

    ############################# SET-UP ##############################

    private $tmpDir;

    private $entity;

    /** @var FileStore */
    private $store;

    protected function setUp() {
        parent::setUp();

        $this->tmpDir = __DIR__ . DIRECTORY_SEPARATOR . '_tmp_';
        $this->clear($this->tmpDir);
        @mkdir($this->tmpDir, 0777, true);

        date_default_timezone_set('UTC');

        $this->initStore();
    }

    protected function initStore() {
        $serializer = new JsonSerializer(function () {
            return new \StdClass();
        });
        $serializer->defineChild('one', new NoneSerializer(),
            function ($object) {
                return $object->one;
            },
            function ($object, $value) {
                $object->one = $value;
            }
        );
        $serializer->defineChild('two', new DateTimeSerializer(),
            function ($object) {
                return $object->two;
            },
            function ($object, $value) {
                $object->two = $value;
            }
        );
        $this->store = new FileStore($serializer, $this->tmpDir);
    }

    protected function tearDown() {
        $this->clear($this->tmpDir);
        parent::tearDown();
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

    private function givenAnEntityWith($one, $two) {
        $this->entity = new \StdClass();
        $this->entity->one = $one;
        $this->entity->two = $two;
    }

    private function givenAFile_Containing($key, $content) {
        $file = $this->tmpDir . DIRECTORY_SEPARATOR . $key;
        @mkdir(dirname($file));
        file_put_contents($file, $content);
    }

    private function givenISet_To($property, $value) {
        $this->entity->$property = $value;
    }

    private function givenAStoreFor($class) {
        $this->store = FileStore::forClass($class, $this->tmpDir);
    }

    private function givenTheEntityIsAnInstanceOf($class) {
        $this->entity = new $class;
    }

    public function whenICreateTheEntityAs($file) {
        $this->store->create($this->entity, $file);
    }

    private function whenITryToCreateAStoreFor($class) {
        $that = $this;
        $this->try->tryTo(function () use ($class, $that) {
            $that->givenAStoreFor($class);
        });
    }

    private function whenITryToRead($key) {
        $this->try->tryTo(function () use ($key) {
            $this->whenIRead($key);
        });
    }

    private function whenIRead($key) {
        $this->entity = $this->store->read($key);
    }

    private function whenIUpdateTheEntity() {
        $this->store->update($this->entity);
    }

    private function whenIDelete($file) {
        $this->store->delete($file);
    }

    private function thenThereShouldBeAFile($key) {
        $this->assertFileExists($this->tmpDir . DIRECTORY_SEPARATOR . $key);
    }

    private function thenThereShouldBeNoFile($key) {
        $this->assertFileNotExists($this->tmpDir . DIRECTORY_SEPARATOR . $key);
    }

    private function then_ShouldContain($key, $content) {
        $this->assertEquals(json_decode($content, true),
            json_decode(file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . $key), true));
    }

    private function then_ShouldBe($property, $value) {
        $this->assertEquals($value, $this->entity->$property);
    }

    private function thenTheKeysShouldBe($keys) {
        $this->assertEquals($keys, $this->store->keys());
    }

    private function thenTheEntityShouldBeAnInstanceOf($class) {
        $this->assertInstanceOf($class, $this->entity);
    }
}