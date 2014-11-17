<?php
namespace spec\watoki\stores;

use spec\watoki\stores\fixtures\StoresTestDatabase;
use spec\watoki\stores\fixtures\StoresTestEntity;
use watoki\scrut\Specification;
use watoki\stores\sqlite\serializers\CompositeSerializer;
use watoki\stores\sqlite\serializers\IntegerSerializer;
use watoki\stores\sqlite\serializers\StringSerializer;
use watoki\stores\sqlite\SqliteStore;

class SqliteStoreTest extends Specification {

    function testCreateTable() {
        $this->createFullTable();
        $this->assertLogged('CREATE TABLE IF NOT EXISTS StoresTestEntity (' .
            '"id" INTEGER PRIMARY KEY AUTOINCREMENT, ' .
            '"boolean" INTEGER NOT NULL, ' .
            '"integer" INTEGER NOT NULL, ' .
            '"float" FLOAT NOT NULL, ' .
            '"string" TEXT NOT NULL, ' .
            '"dateTime" TEXT(32) NOT NULL, ' .
            '"null" TEXT DEFAULT NULL, ' .
            '"nullDateTime" TEXT(32) DEFAULT NULL, ' .
            '"array" TEXT NOT NULL, ' .
            '"child__one" TEXT DEFAULT NULL, ' .
            '"child__two" INTEGER NOT NULL, ' .
            '"child__child__foo" TEXT DEFAULT NULL'.
            '); -- []');
    }

    function testCreatePartialTable() {
        $this->store->createTable(array('boolean', 'dateTime', 'string'));
        $this->assertLogged('CREATE TABLE IF NOT EXISTS StoresTestEntity (' .
            '"id" INTEGER PRIMARY KEY AUTOINCREMENT, ' .
            '"boolean" INTEGER NOT NULL, ' .
            '"string" TEXT NOT NULL, ' .
            '"dateTime" TEXT(32) NOT NULL' .
            '); -- []');
    }

    function testCreateDefaultNullColumn() {
        $this->store->createTable(array('boolean'));
        $this->store->createColumn('string');
        $this->assertLogged('ALTER TABLE StoresTestEntity ADD COLUMN "string" TEXT DEFAULT NULL ' .
            '-- []');
    }

    function testCreateNonNullColumn() {
        $this->store->createTable(array('boolean'));
        $this->store->createColumn('integer', 0);
        $this->assertLogged('ALTER TABLE StoresTestEntity ADD COLUMN "integer" INTEGER NOT NULL DEFAULT \'0\' ' .
            '-- []');
    }

    function testDropTable() {
        $this->createFullTable();
        $this->store->dropTable();
        $this->assertLogged('DROP TABLE StoresTestEntity; ' .
            '-- []');
    }

    function testCreate() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'), array("some" => array("here", "there"))));
        $this->assertLogged('INSERT INTO StoresTestEntity ("boolean", "integer", "float", "string", "dateTime", "null", "nullDateTime", "array", "child__one", "child__two", "child__child__foo") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null, :nullDateTime, :array, :child__one, :child__two, :child__child__foo)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01T00:00:00+00:00","null":null,"nullDateTime":null,' .
            '"array":"{\"some\":[\"here\",\"there\"]}","child__one":"uno","child__two":"dos","child__child__foo":"bar"}');
        $this->assertTableEquals(array(
            array(
                'id' => "1",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello",
                'dateTime' => "2001-01-01T00:00:00+00:00",
                'null' => null,
                'nullDateTime' => null,
                'array' => "{\"some\":[\"here\",\"there\"]}",
                "child__one" => "uno",
                "child__two" => "dos",
                "child__child__foo" => "bar"
            )
        ));
    }

    function testCreateWithId() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')), 17);
        $this->assertLogged('INSERT INTO StoresTestEntity ("boolean", "integer", "float", "string", "dateTime", "null", "nullDateTime", "array", "child__one", "child__two", "child__child__foo", "id") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null, :nullDateTime, :array, :child__one, :child__two, :child__child__foo, :id)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01T00:00:00+00:00","null":null,"nullDateTime":null,' .
            '"array":"[]","child__one":"uno","child__two":"dos","child__child__foo":"bar","id":17}');
        $this->assertTableEquals(array(
            array(
                'id' => "17",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello",
                'dateTime' => "2001-01-01T00:00:00+00:00",
                'null' => null,
                'nullDateTime' => null,
                'array' => "[]",
                "child__one" => "uno",
                "child__two" => "dos",
                "child__child__foo" => "bar"
            )
        ));
    }

    function testRead() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        $this->initStore();
        $this->createFullTable();
        /** @var StoresTestEntity $entity */
        $entity = $this->store->read(1);
        $this->assertLogged('SELECT * FROM StoresTestEntity WHERE "id" = ? LIMIT 1 ' .
            '-- [1]');

        $this->assertSame(true, $entity->boolean);
        $this->assertSame(42, $entity->integer);
        $this->assertSame(1.6, $entity->float);
        $this->assertSame('Hello', $entity->string);
        $this->assertEquals('2001-01-01', $entity->dateTime->format('Y-m-d'));
        $this->assertNull($entity->null);
        $this->assertNull($entity->nullDateTime);
    }

    function testUpdate() {
        $this->createFullTable();
        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);

        $entity->string = 'Hello World';
        $this->store->update($entity);

        $this->assertLogged('UPDATE StoresTestEntity SET ' .
            '"boolean" = :boolean, ' .
            '"integer" = :integer, ' .
            '"float" = :float, ' .
            '"string" = :string, ' .
            '"dateTime" = :dateTime, ' .
            '"null" = :null, ' .
            '"nullDateTime" = :nullDateTime, ' .
            '"array" = :array, ' .
            '"child__one" = :child__one, ' .
            '"child__two" = :child__two, ' .
            '"child__child__foo" = :child__child__foo ' .
            'WHERE id = :id ' .
            '-- {' .
            '"boolean":1,' .
            '"integer":42,' .
            '"float":1.6,' .
            '"string":"Hello World",' .
            '"dateTime":"2001-01-01T00:00:00+00:00",' .
            '"null":null,' .
            '"nullDateTime":null,' .
            '"array":"[]",' .
            '"child__one":"uno",' .
            '"child__two":"dos",' .
            '"child__child__foo":"bar",' .
            '"id":1' .
            '}');
        $this->assertTableEquals(array(
            array(
                'id' => "1",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello World",
                'dateTime' => "2001-01-01T00:00:00+00:00",
                'null' => null,
                'nullDateTime' => null,
                'array' => "[]",
                "child__one" => "uno",
                "child__two" => "dos",
                "child__child__foo" => "bar"
            )
        ));
    }

    function testDelete() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(false, 17, 1.6, 'Hello', new \DateTime()));

        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);

        $this->store->delete($this->store->getKey($entity));

        $this->assertLogged('DELETE FROM StoresTestEntity WHERE id = ? ' .
            '-- [2]');
        $this->assertTableSize(1);
    }

    function testReadBy() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var \spec\watoki\stores\fixtures\StoresTestEntity $entity */
        $entity = $this->store->readBy('integer', 42);
        $this->assertSame(42, $entity->integer);
        $this->assertSame('Hello', $entity->string);

        $this->assertLogged('SELECT * FROM StoresTestEntity WHERE "integer" = ? LIMIT 1 ' .
            '-- [42]');
    }

    function testReadAll() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var \spec\watoki\stores\fixtures\StoresTestEntity[] $all */
        $all = $this->store->readAll();
        $this->assertCount(2, $all);

        $this->assertLogged('SELECT * FROM StoresTestEntity ' .
            '-- []');
    }

    function testReadAllBy() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello World', new \DateTime()));
        $this->store->create(new StoresTestEntity(false, 17, 1.6, 'Hello Me', new \DateTime()));
        $this->store->create(new StoresTestEntity(false, 42, 1.6, 'Hello You', new \DateTime()));

        /** @var StoresTestEntity[] $all */
        $all = $this->store->readAllBy('integer', 42);
        $this->assertCount(2, $all);

        $this->assertLogged('SELECT * FROM StoresTestEntity WHERE "integer" = ? ' .
            '-- [42]');
    }

    function testListKeys() {
        $this->createFullTable();
        $e = new StoresTestEntity(true, 42, 1.6, 'Hello World', new \DateTime());
        $this->store->create($e, 42);
        $this->store->create($e, 12);
        $this->store->create($e, 6);

        $keys = $this->store->keys();
        sort($keys);

        $this->assertEquals(array(6, 12, 42), $keys);
    }

    ####################### SET-UP #####################

    /** @var SqliteStore */
    private $store;

    /** @var StoresTestDatabase */
    private $database;

    /** @var CompositeSerializer */
    private $entitySerializer;

    protected function setUp() {
        parent::setUp();
        $this->database = new StoresTestDatabase(new \PDO('sqlite::memory:'));
        $this->initStore();
    }

    private function initStore() {
        $this->store = SqliteStore::forClass(StoresTestEntity::$CLASS, $this->database);
    }

    private function assertLogged($string) {
        $this->assertEquals($string, $this->database->log);
    }

    private function assertTableEquals($array) {
        $this->assertEquals(json_encode($array), json_encode($this->database->readAll('select * from ' . 'StoresTestEntity;')));
    }

    private function assertTableSize($int) {
        $this->assertCount($int, $this->database->readAll('select * from ' . 'StoresTestEntity;'));
    }

    private function createFullTable() {
        $this->store->createTable(array('id', 'boolean', 'integer', 'float', 'string', 'dateTime', 'null', 'nullDateTime', 'array', 'child'));
    }
}