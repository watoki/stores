<?php
namespace spec\watoki\stores\pdo;

use spec\watoki\stores\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\pdo\SerializerRepository;
use watoki\stores\pdo\Store;

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
            '"null" TEXT(255), ' .
            'PRIMARY KEY ("id")' .
            '); -- []');
    }

    function testDropTable() {
        $this->store->dropTable();
        $this->assertLog('DROP TABLE TestEntity; -- []');
    }

    function testCreate() {
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));
        $this->assertLog('INSERT INTO TestEntity ("boolean", "integer", "float", "string", "dateTime", "null") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01 00:00:00","null":null}');
        $this->assertTable(array(
            array(
                'id' => "1",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello",
                'dateTime' => "2001-01-01 00:00:00",
                'null' => null
            )
        ));
    }

    function testRead() {
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));
        /** @var TestEntity $entity */
        $entity = $this->store->read(1);
        $this->assertLog('SELECT * FROM TestEntity WHERE "id" = ? LIMIT 1 -- [1]');

        $this->assertSame(true, $entity->getBoolean());
        $this->assertSame(42, $entity->getInteger());
        $this->assertSame(1.6, $entity->getFloat());
        $this->assertSame('Hello', $entity->getString());
        $this->assertEquals(new \DateTime('2001-01-01'), $entity->getDateTime());
        $this->assertNull($entity->getNull());
    }

    function testUpdate() {
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
            '"null" = :null WHERE id = :id ' .
            '-- {' .
            '"boolean":1,' .
            '"integer":42,' .
            '"float":1.6,' .
            '"string":"Hello World",' .
            '"dateTime":"2001-01-01 00:00:00",' .
            '"null":null,' .
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
                'null' => null
            )
        ));
    }

    function testDelete() {
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello', new \DateTime()));

        $entity = new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);

        $this->store->delete($entity);

        $this->assertLog('DELETE FROM TestEntity WHERE id = ? -- [2]');
        $this->assertTableSize(1);
    }

    function testReadBy() {
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var TestEntity $entity */
        $entity = $this->store->readBy('integer', 42);
        $this->assertSame(42, $entity->getInteger());
        $this->assertSame('Hello', $entity->getString());

        $this->assertLog('SELECT * FROM TestEntity WHERE "integer" = ? LIMIT 1 -- [42]');
    }

    function testReadAll() {
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var TestEntity[] $all */
        $all = $this->store->readAll();
        $this->assertCount(2, $all);

        $this->assertLog('SELECT * FROM TestEntity -- []');
    }

    function testReadAllBy() {
        $this->store->create(new TestEntity(true, 42, 1.6, 'Hello World', new \DateTime()));
        $this->store->create(new TestEntity(false, 17, 1.6, 'Hello Me', new \DateTime()));
        $this->store->create(new TestEntity(false, 42, 1.6, 'Hello You', new \DateTime()));

        /** @var TestEntity[] $all */
        $all = $this->store->readAllBy('integer', 42);
        $this->assertCount(2, $all);

        $this->assertLog('SELECT * FROM TestEntity WHERE "integer" = ? -- [42]');
    }

    /** @var Store */
    private $store;

    /** @var TestDatabase */
    private $db;

    protected function setUp() {
        parent::setUp();
        $this->db = new TestDatabase(new \PDO('sqlite::memory:'));
        $this->store = new TestStore(new SerializerRepository(), $this->db);

        $this->store->createTable();
    }

    private function assertLog($string) {
        $this->assertEquals($string, $this->db->log);
    }

    private function assertTable($array) {
        $this->assertEquals(json_encode($array), json_encode($this->db->readAll('select * from TestEntity;')));
    }

    private function assertTableSize($int) {
        $this->assertCount($int, $this->db->readAll('select * from TestEntity;'));
    }
}