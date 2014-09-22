<?php
namespace spec\watoki\stores;

use spec\watoki\stores\lib\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\pdo\PdoStore;

/**
 * @property PdoDatabaseFixture db <-
 */
class PdoStoreTest extends Specification {

    function testCreateTable() {
        $this->store->createTable();
        $this->assertLog('CREATE TABLE IF NOT EXISTS TestEntity (' .
            '"id" INTEGER NOT NULL, ' .
            '"boolean" INTEGER NOT NULL, ' .
            '"integer" INTEGER NOT NULL, ' .
            '"float" FLOAT NOT NULL, ' .
            '"string" TEXT(255) NOT NULL, ' .
            '"dateTime" TEXT(32) NOT NULL, ' .
            '"null" TEXT(255) DEFAULT NULL, ' .
            '"nullDateTime" TEXT(32) DEFAULT NULL, ' .
            '"array" TEXT(1024) NOT NULL, ' .
            'PRIMARY KEY ("id")' .
            '); -- []');
    }

    function testCreatePartialTable() {
        $this->store->createTable(array('boolean', 'dateTime', 'string'));
        $this->assertLog('CREATE TABLE IF NOT EXISTS TestEntity (' .
            '"id" INTEGER NOT NULL, ' .
            '"boolean" INTEGER NOT NULL, ' .
            '"string" TEXT(255) NOT NULL, ' .
            '"dateTime" TEXT(32) NOT NULL, ' .
            'PRIMARY KEY ("id")' .
            '); -- []');
    }

    function testCreateColumn() {
        $this->store->createTable(array('boolean'));
        $this->store->createColumn('string');
        $this->assertLog('ALTER TABLE TestEntity ADD COLUMN "string" TEXT(255) NOT NULL DEFAULT \'\' ' .
            '-- []');
    }

    function testDropTable() {
        $this->store->createTable();
        $this->store->dropTable();
        $this->assertLog('DROP TABLE TestEntity; ' .
            '-- []');
    }

    function testCreate() {
        $this->store->createTable();
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'), array("some" => array("here", "there"))));
        $this->assertLog('INSERT INTO TestEntity ("boolean", "integer", "float", "string", "dateTime", "null", "nullDateTime", "array") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null, :nullDateTime, :array)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01 00:00:00","null":null,"nullDateTime":null,' .
            '"array":"{\"some\":[\"here\",\"there\"]}"}');
        $this->assertTable(array(
            array(
                'id' => "1",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello",
                'dateTime' => "2001-01-01 00:00:00",
                'null' => null,
                'nullDateTime' => null,
                'array' => "{\"some\":[\"here\",\"there\"]}"
            )
        ));
    }

    function testCreateWithId() {
        $this->store->createTable();
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')), 17);
        $this->assertLog('INSERT INTO TestEntity ("boolean", "integer", "float", "string", "dateTime", "null", "nullDateTime", "array", "id") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null, :nullDateTime, :array, :id)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01 00:00:00","null":null,"nullDateTime":null,' .
            '"array":"[]","id":17}');
        $this->assertTable(array(
            array(
                'id' => "17",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello",
                'dateTime' => "2001-01-01 00:00:00",
                'null' => null,
                'nullDateTime' => null,
                'array' => "[]"
            )
        ));
    }

    function testRead() {
        $this->store->createTable();
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));
        /** @var TestEntity $entity */
        $entity = $this->store->read(1);
        $this->assertLog('SELECT * FROM TestEntity WHERE "id" = ? LIMIT 1 ' .
            '-- [1]');

        $this->assertSame(true, $entity->getBoolean());
        $this->assertSame(42, $entity->getInteger());
        $this->assertSame(1.6, $entity->getFloat());
        $this->assertSame('Hello', $entity->getString());
        $this->assertEquals(new \DateTime('2001-01-01'), $entity->getDateTime());
        $this->assertNull($entity->getNull());
        $this->assertNull($entity->getNullDateTime());
    }

    function testGetKeyOfCreate() {
        $this->store->createTable();
        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);
        $this->assertEquals(1, $this->store->getKey($entity));
    }

    function testGetKeyOfRead() {
        $this->store->createTable();
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));
        /** @var TestEntity $entity */
        $entity = $this->store->read(1);

        $this->assertEquals(1, $this->store->getKey($entity));
    }

    function testUpdate() {
        $this->store->createTable();
        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);

        $entity->setString('Hello World');
        $this->store->update($entity);

        $this->assertLog('UPDATE TestEntity SET ' .
            '"boolean" = :boolean, ' .
            '"integer" = :integer, ' .
            '"float" = :float, ' .
            '"string" = :string, ' .
            '"dateTime" = :dateTime, ' .
            '"null" = :null, ' .
            '"nullDateTime" = :nullDateTime, ' .
            '"array" = :array ' .
            'WHERE id = :id ' .
            '-- {' .
            '"boolean":1,' .
            '"integer":42,' .
            '"float":1.6,' .
            '"string":"Hello World",' .
            '"dateTime":"2001-01-01 00:00:00",' .
            '"null":null,' .
            '"nullDateTime":null,' .
            '"array":"[]",' .
            '"id":1' .
            '}');
        $this->assertTable(array(
            array(
                'id' => "1",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello World",
                'dateTime' => "2001-01-01 00:00:00",
                'null' => null,
                'nullDateTime' => null,
                'array' => "[]"
            )
        ));
    }

    function testDelete() {
        $this->store->createTable();
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello', new \DateTime()));

        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);

        $this->store->delete($entity);

        $this->assertLog('DELETE FROM TestEntity WHERE id = ? ' .
            '-- [2]');
        $this->assertTableSize(1);
    }

    function testReadBy() {
        $this->store->createTable();
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var TestEntity $entity */
        $entity = $this->store->readBy('integer', 42);
        $this->assertSame(42, $entity->getInteger());
        $this->assertSame('Hello', $entity->getString());

        $this->assertLog('SELECT * FROM TestEntity WHERE "integer" = ? LIMIT 1 ' .
            '-- [42]');
    }

    function testReadAll() {
        $this->store->createTable();
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var TestEntity[] $all */
        $all = $this->store->readAll();
        $this->assertCount(2, $all);

        $this->assertLog('SELECT * FROM TestEntity ' .
            '-- []');
    }

    function testReadAllBy() {
        $this->store->createTable();
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello World', new \DateTime()));
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello Me', new \DateTime()));
        $this->store->create(new TestEntity(false, 42, 1.6, 'Hello You', new \DateTime()));

        /** @var TestEntity[] $all */
        $all = $this->store->readAllBy('integer', 42);
        $this->assertCount(2, $all);

        $this->assertLog('SELECT * FROM TestEntity WHERE "integer" = ? ' .
            '-- [42]');
    }
    
    function testListKeys() {
        $this->store->createTable();
        $e = new TestEntity(true, 42, 1.6, 'Hello World', new \DateTime());
        $this->store->create($e, 42);
        $this->store->create($e, 12);
        $this->store->create($e, 6);

        $keys = $this->store->keys();
        sort($keys);

        $this->assertEquals(array(6, 12, 42), $keys);
    }
    
    ####################### SET-UP #####################

    /** @var PdoStore */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = $this->factory->getInstance(PdoStore::$CLASS, array('entityClass' => TestEntity::$CLASS));
    }

    private function assertLog($string) {
        $this->assertEquals($string, $this->db->database->log);
    }

    private function assertTable($array) {
        $this->assertEquals(json_encode($array), json_encode($this->db->database->readAll('select * from ' . 'TestEntity;')));
    }

    private function assertTableSize($int) {
        $this->assertCount($int, $this->db->database->readAll('select * from ' . 'TestEntity;'));
    }
}