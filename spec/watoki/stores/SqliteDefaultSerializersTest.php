<?php
namespace spec\watoki\stores;

use spec\watoki\stores\fixtures\StoresTestDatabase;
use watoki\reflect\type\ArrayType;
use watoki\scrut\Specification;
use watoki\stores\common\factories\SimpleSerializerFactory;
use watoki\stores\SerializerRegistry;
use watoki\stores\sqlite\serializers\CountedArraySerializer;
use watoki\stores\sqlite\SqliteStore;

/**
 * @property \spec\watoki\reflect\fixtures\ClassFixture class <-
 */
class SqliteDefaultSerializersTest extends Specification {

    function testStandardValues() {
        $this->class->givenTheClass_WithTheBody('SqliteStore\StandardValues', '
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
        $this->givenTheEntityIsAnInstanceOf('SqliteStore\StandardValues');
        $this->givenAStoreFor('SqliteStore\StandardValues');

        $this->givenISet_To('string', 'Some string');
        $this->givenISet_To('boolean', true);
        $this->givenISet_To('float', 3.1415);
        $this->givenISet_To('date', new \DateTime('2001-02-03'));

        $this->whenICreateTheEntity();
        $this->thenTable_ShouldContain('StandardValues', '[{
            "id": 1,
            "string": "Some string",
            "boolean": true,
            "float": 3.1415,
            "null": null,
            "date": "2001-02-03T00:00:00+00:00"
        }]');

        $this->givenAStoreFor('SqliteStore\StandardValues');
        $this->whenIRead(1);
        $this->thenTheEntityShouldBeAnInstanceOf('SqliteStore\StandardValues');
        $this->then_ShouldBe('string', 'Some string');
        $this->then_ShouldBe('date', new \DateTime('2001-02-03 +00:00'));
    }

    function testArrayValues() {
        $this->class->givenTheClass_WithTheBody('SqliteStore\ArrayValues', '
            /** @var array|int[] */
            public $integers;

            /** @var array|string[] */
            public $dictionary;

            /** @var array|\DateTime[] */
            public $dates;

            /** @var array|array[]|string[][] */
            public $deep;
        ');
        $this->givenTheEntityIsAnInstanceOf('SqliteStore\ArrayValues');
        $this->givenAStoreFor('SqliteStore\ArrayValues');

        $this->givenISet_To('integers', array(1, 42, 73, 12));
        $this->givenISet_To('dictionary', array('one' => 'uno', 'two' => 'dos'));
        $this->givenISet_To('dates', array(new \DateTime('2001-01-01'), new \DateTime('2002-02-02')));
        $this->givenISet_To('deep', array(1 => array("one"), 2 => array("two")));

        $this->whenICreateTheEntity();
        $this->thenTable_ShouldContain('ArrayValues', '[{
            "id": 1,
            "integers": "[1,42,73,12]",
            "dictionary": "{\"one\":\"uno\",\"two\":\"dos\"}",
            "dates": "[\"2001-01-01T00:00:00+00:00\",\"2002-02-02T00:00:00+00:00\"]",
            "deep": "{\"1\":\"[\\\\\"one\\\\\"]\",\"2\":\"[\\\\\"two\\\\\"]\"}"
        }]');

        $this->givenAStoreFor('SqliteStore\ArrayValues');
        $this->whenIRead(1);
        $this->then_ShouldBe('integers', array(1, 42, 73, 12));
        $this->then_ShouldBe('dates', array(new \DateTime('2001-01-01 +00'), new \DateTime('2002-02-02 +00')));
        $this->then_ShouldBe('deep', array(1 => array("one"), 2 => array("two")));
    }

    function testCountedArrayValues() {
        $this->class->givenTheClass_WithTheBody('SqliteStore\CountedArrayValues', '
            /** @var array|int[] */
            public $integers;

            /** @var array|string[] */
            public $dictionary;
        ');
        $this->givenTheEntityIsAnInstanceOf('SqliteStore\CountedArrayValues');

        $this->givenIRegisteredACountedArraySerializer();
        $this->givenAStoreFor('SqliteStore\CountedArrayValues');

        $this->givenISet_To('integers', array(1, 42, 73, 12));
        $this->givenISet_To('dictionary', array('one' => 'uno', 'two' => 'dos'));

        $this->whenICreateTheEntity();
        $this->thenTable_ShouldContain('CountedArrayValues', '[{
            "id": 1,
            "integers__items": "[1,42,73,12]",
            "integers__count": 4,
            "dictionary__items": "{\"one\":\"uno\",\"two\":\"dos\"}",
            "dictionary__count": 2
        }]');

        $this->givenAStoreFor('SqliteStore\CountedArrayValues');
        $this->whenIRead(1);
        $this->then_ShouldBe('integers', array(1, 42, 73, 12));
        $this->then_ShouldBe('dictionary', array('one' => 'uno', 'two' => 'dos'));
    }

    function testIdentifierTypes() {
        $this->class->givenTheClass_WithTheBody('SqliteStore\Identifiers', '
            /** @var string|\DateTime-ID */
            public $string;

            /** @var identifier\SomeClassId */
            public $object;

            /** @var null|identifier\SomeClassId */
            public $nullable;
        ');
        $this->class->givenTheClass('SqliteStore\identifier\SomeClass');
        $this->class->givenTheClass_WithTheBody('SqliteStore\identifier\SomeClassId',
            'function __toString() { return "foo"; }');
        $this->givenTheEntityIsAnInstanceOf('SqliteStore\Identifiers');
        $this->givenAStoreFor('SqliteStore\Identifiers');

        $this->givenISet_To('string', 'the good old time');
        $this->givenISet_ToAnInstanceOf('object', 'SqliteStore\identifier\SomeClassId');

        $this->whenICreateTheEntity();
        $this->thenTable_ShouldContain('Identifiers', '[{
            "id": 1,
            "string": "the good old time",
            "object": "foo",
            "nullable": null
        }]');

        $this->givenAStoreFor('SqliteStore\Identifiers');
        $this->whenIRead(1);
        $this->then_ShouldBe('string', "the good old time");
        $this->then_ShouldBeAnInstanceOf('object', 'SqliteStore\identifier\SomeClassId');
    }

    function testEmbeddedObjects() {
        $this->class->givenTheClass_WithTheBody('SqliteStore\Family', '
            /** @var Child */
            public $childOne;

            /** @var Child */
            public $childTwo;

            function __construct() {
                $this->childOne = new Child("Bart", new Pet("Santas little helper", new \DateTime("1990-12-24")));
                $this->childTwo = new Child("Lisa", new Pet("Snowball", new \DateTime("1993-03-15")));
            }
        ');
        $this->class->givenTheClass_WithTheBody('SqliteStore\Child', '
            /** @var string */
            public $name;

            /** @var Pet */
            public $pet;

            function __construct($name, $pet) {
                $this->name = $name;
                $this->pet = $pet;
            }
        ');
        $this->class->givenTheClass_WithTheBody('SqliteStore\Pet', '
            /** @var string */
            public $name;

            /** @var \DateTime */
            public $birthDate;

            function __construct($name, $birthDate) {
                $this->name = $name;
                $this->birthDate = $birthDate;
            }
        ');
        $this->givenTheEntityIsAnInstanceOf('SqliteStore\Family');
        $this->givenAStoreFor('SqliteStore\Family');

        $this->whenICreateTheEntity();
        $this->thenTable_ShouldContain('Family', '[{
            "id":"1",
            "childOne__name":"Bart",
            "childOne__pet__name":"Santas little helper",
            "childOne__pet__birthDate":"1990-12-24T00:00:00+00:00",
            "childTwo__name":"Lisa",
            "childTwo__pet__name":"Snowball",
            "childTwo__pet__birthDate":"1993-03-15T00:00:00+00:00"
        }]');
    }

    ################################################################################################################

    private $entity;

    /** @var \watoki\stores\sqlite\Database */
    private $database;

    /** @var SqliteStore */
    private $store;

    /** @var SerializerRegistry */
    private $registry;

    protected function setUp() {
        parent::setUp();
        $this->database = new StoresTestDatabase(new \PDO('sqlite::memory:'));
        $this->registry = new SerializerRegistry();
    }

    private function givenTheEntityIsAnInstanceOf($class) {
        $this->entity = new $class;
    }

    private function givenAStoreFor($class) {
        $this->store = SqliteStore::forClass($class, $this->database, $this->registry);

        $fields = array();
        foreach (new $class as $field => $value) {
            $fields[] = $field;
        }
        $this->store->createTable($fields);
    }

    private function givenISet_To($property, $value) {
        $this->entity->$property = $value;
    }

    private function givenISet_ToAnInstanceOf($property, $class) {
        $this->givenISet_To($property, new $class);
    }

    private function givenIRegisteredACountedArraySerializer() {
        $registry = $this->registry;
        $this->registry->add(new SimpleSerializerFactory(ArrayType::$CLASS,
            function (ArrayType $type) use ($registry) {
                return new CountedArraySerializer($registry->get($type->getItemType()));
        }));
    }

    private function whenICreateTheEntity() {
        $this->store->create($this->entity);
    }

    private function whenIRead($id) {
        $this->entity = $this->store->read($id);
    }

    private function thenTable_ShouldContain($table, $content) {
        $table = $this->database->readAll("SELECT * FROM $table;");
        $this->assertEquals(json_decode($content, true), $table);
    }

    private function thenTheEntityShouldBeAnInstanceOf($class) {
        $this->assertInstanceOf($class, $this->entity);
    }

    private function then_ShouldBe($property, $value) {
        $this->assertEquals($value, $this->entity->$property);
    }

    private function then_ShouldBeAnInstanceOf($property, $class) {
        $this->assertInstanceOf($class, $this->entity->$property);
    }

} 