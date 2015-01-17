<?php
namespace spec\watoki\stores;

use spec\watoki\stores\fixtures\StoresTestDatabase;
use watoki\scrut\Specification;
use watoki\stores\exception\NotFoundException;
use watoki\stores\sql\serializers\CallbackSqlSerializer;
use watoki\stores\sql\serializers\CompositeSerializer;
use watoki\stores\sql\serializers\IntegerSerializer;
use watoki\stores\sql\serializers\NullableSerializer;
use watoki\stores\sql\serializers\StringSerializer;
use watoki\stores\sqlite\SqliteStore;

/**
 * @property \watoki\scrut\ExceptionFixture try <-
 */
class SqliteStoreTest extends Specification {

    function testCreateTable() {
        $this->givenASerializerWithTheDefinition(array('one' => 'FOO', 'two' => 'BAR', 'three' => 'BAZ'));
        $this->whenICreateTheTableFor(array('one', 'two'));

        $this->then_ShouldBeExecuted('CREATE TABLE IF NOT EXISTS MyTable (' .
            '"id" INTEGER PRIMARY KEY AUTOINCREMENT, ' .
            '"one" FOO, ' .
            '"two" BAR); ' .
            '-- []');
    }

    function testCreateDefaultNullColumn() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new NullableSerializer(new StringSerializer()),
        ));
        $this->whenICreateTheTableFor(array('one'));
        $this->whenICreateTheColumn('two');

        $this->then_ShouldBeExecuted('ALTER TABLE MyTable ADD COLUMN "two" TEXT DEFAULT NULL ' .
            '-- []');
    }

    function testCreateNonNullColumn() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->whenICreateTheTableFor(array('one'));
        $this->whenICreateTheColumn_WithDefault('two', 0);

        $this->then_ShouldBeExecuted('ALTER TABLE MyTable ADD COLUMN "two" INTEGER NOT NULL DEFAULT \'0\' ' .
            '-- []');
    }

    function testDropTable() {
        $this->givenASerializerWithTheDefinition(array('one' => 'FOO'));
        $this->givenICreatedTheFullTable();

        $this->whenIDropTheTable();
        $this->then_ShouldBeExecuted('DROP TABLE MyTable; ' .
            '-- []');
    }

    function testCreate() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity(array('one' => 'foo', 'two' => 'bar'));

        $this->then_ShouldBeExecuted('INSERT INTO MyTable ("one", "two") VALUES (:one, :two) ' .
            '-- {"one":"foo","two":"bar"}');

        $this->whenICreateTheEntity(array('one' => 'me', 'two' => 'you'));
        $this->thenTheTable_ShouldContain("MyTable", array(array(
            'id' => '1',
            'one' => 'foo',
            'two' => 'bar'
        ), array(
            'id' => '2',
            'one' => 'me',
            'two' => 'you'
        )));
    }

    function testCreateWithId() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer()
        ));
        $this->givenICreatedTheFullTable();

        $this->whenICreateTheEntity_At(array('one' => 'foo'), 42);
        $this->thenTheKeyOfTheEntityShouldBe(42);

        $this->then_ShouldBeExecuted('INSERT INTO MyTable ("one", "id") VALUES (:one, :id) ' .
            '-- {"one":"foo","id":42}');
        $this->thenTheTable_ShouldContain("MyTable", array(array(
            'id' => '42',
            'one' => 'foo',
        )));
    }

    function testRead() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity_at(array('one' => 'foo', 'two' => 42), 73);
        $this->whenICreateTheEntity_at(array('one' => 'bar', 'two' => 12), 42);

        $this->whenIRead(73);
        $this->then_ShouldBeExecuted('SELECT * FROM MyTable WHERE "id" = ? LIMIT 1 -- [73]');

        $this->thenTheKeyOfTheEntityShouldBe(73);
        $this->thenTheProperty_ShouldBe('one', 'foo');
        $this->thenTheProperty_ShouldBe('two', 42);
    }

    function testReadNonExistingKey() {
        $this->givenACompositeSerializerWith(array());
        $this->givenICreatedTheFullTable();
        $this->whenITryToRead(42);
        $this->try->thenTheException_ShouldBeThrown('Empty result for [SELECT * FROM MyTable WHERE "id" = ? LIMIT 1] [42]');
        $this->try->thenA_ShouldBeThrown(NotFoundException::$CLASS);
    }

    function testUpdate() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity(array('one' => 'foo', 'two' => 42));

        $this->givenISetTheProperty_To('one', 'bar');
        $this->givenISetTheProperty_To('two', 73);

        $this->whenIUpdateTheEntity();
        $this->then_ShouldBeExecuted('UPDATE MyTable SET "one" = :one, "two" = :two WHERE id = :id ' .
            '-- {"one":"bar","two":73,"id":1}');
        $this->thenTheTable_ShouldContain('MyTable', array(array(
            'id' => '1',
            'one' => 'bar',
            'two' => '73'
        )));
    }

    function testDelete() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 33), 3);
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 42), 12);

        $this->whenIDelete(12);
        $this->then_ShouldBeExecuted('DELETE FROM MyTable WHERE id = ? -- [12]');

        $this->thenTheTable_ShouldContain('MyTable', array(array(
            'id' => '3',
            'one' => 'bar',
            'two' => '33'
        )));
    }

    function testReadBy() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 33), 3);
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 42), 12);
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 23), 7);

        $this->entity = $this->createStore()->readBy('two', 42);

        $this->then_ShouldBeExecuted('SELECT * FROM MyTable WHERE "two" = ? LIMIT 1 -- [42]');
        $this->thenTheProperty_ShouldBe('one', 'foo');
    }

    function testReadAll() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 33), 3);
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 42), 12);
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 23), 7);

        $all = $this->createStore()->readAll();
        $this->assertCount(3, $all);

        $this->then_ShouldBeExecuted('SELECT * FROM MyTable -- []');
    }

    function testReadAllBy() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 33), 3);
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 42), 12);
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 23), 7);

        $all = $this->createStore()->readAllBy('one', 'foo');
        $this->assertCount(2, $all);

        $this->then_ShouldBeExecuted('SELECT * FROM MyTable WHERE "one" = ? -- ["foo"]');
    }

    function testListKeys() {
        $this->givenACompositeSerializerWith(array(
            'one' => new StringSerializer(),
            'two' => new IntegerSerializer(),
        ));
        $this->givenICreatedTheFullTable();
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 33), 3);
        $this->whenICreateTheEntity_At(array('one' => 'foo', 'two' => 42), 12);
        $this->whenICreateTheEntity_At(array('one' => 'bar', 'two' => 23), 7);

        $keys = $this->store->keys();
        sort($keys);

        $this->assertEquals(array(3, 7, 12), $keys);
    }

    ####################### SET-UP #####################

    /** @var StoresTestDatabase */
    private $database;

    /** @var SqliteStore */
    private $store;

    /** @var \watoki\stores\sql\SqlSerializer */
    private $serializer;

    private $entity;

    protected function setUp() {
        parent::setUp();
        $this->database = new StoresTestDatabase(new \PDO('sqlite::memory:'));
    }

    private function createStore() {
        $this->store = new SqliteStore($this->serializer, 'MyTable', $this->database);
        return $this->store;
    }

    private function givenASerializerWithTheDefinition($definition) {
        $empty = function () {
        };
        $this->serializer = new CallbackSqlSerializer($empty, $empty, $definition);
    }

    private function givenACompositeSerializerWith($children) {
        $this->serializer = new CompositeSerializer(function () {
            return new \StdClass;
        });
        foreach ($children as $child => $serializer) {
            $this->serializer->defineChild($child, $serializer,
                function ($object) use ($child) {
                    return $object->$child;
                },
                function ($object, $value) use ($child) {
                    $object->$child = $value;
                }
            );
        }
    }

    private function givenAnEntityWith($fields) {
        $this->entity = new \StdClass;
        foreach ($fields as $key => $value) {
            $this->entity->$key = $value;
        }
    }

    private function givenICreatedTheFullTable() {
        $this->whenICreateTheTableFor(array_keys($this->serializer->getDefinition()));
    }

    private function givenISetTheProperty_To($property, $value) {
        $this->entity->$property = $value;
    }

    private function whenICreateTheTableFor($fields) {
        $this->createStore()->createTable($fields);
    }

    private function whenICreateTheColumn($column) {
        $this->createStore()->createColumn($column);
    }

    private function whenICreateTheColumn_WithDefault($column, $default) {
        $this->createStore()->createColumn($column, $default);
    }

    private function whenIDropTheTable() {
        $this->createStore()->dropTable();
    }

    private function whenICreateTheEntity($fields) {
        $this->givenAnEntityWith($fields);
        $this->createStore()->create($this->entity);
    }

    private function whenICreateTheEntity_At($fields, $id) {
        $this->givenAnEntityWith($fields);
        $this->createStore()->create($this->entity, $id);
    }

    private function whenIRead($id) {
        $this->entity = $this->createStore()->read($id);
    }

    private function whenITryToRead($id) {
        $store = $this->createStore();
        $this->try->tryTo(function () use ($store, $id) {
            $store->read($id);
        });
    }

    private function whenIUpdateTheEntity() {
        $this->store->update($this->entity);
    }

    private function whenIDelete($key) {
        $this->store->delete($key);
    }

    private function then_ShouldBeExecuted($string) {
        $this->assertEquals($string, $this->database->log);
    }

    private function thenTheTable_ShouldContain($table, $array) {
        $this->assertEquals(json_encode($array), json_encode($this->database->readAll("select * from $table;")));
    }

    private function thenTheKeyOfTheEntityShouldBe($key) {
        $this->assertEquals($key, $this->store->getKey($this->entity));
    }

    private function thenTheProperty_ShouldBe($property, $value) {
        $this->assertEquals($value, $this->entity->$property);
    }
}