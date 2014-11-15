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
        $this->assertLogged('CREATE TABLE IF NOT EXISTS MyTable (' .
            '"id" INTEGER PRIMARY KEY AUTOINCREMENT, ' .
            '"boolean" INTEGER NOT NULL, ' .
            '"integer" INTEGER NOT NULL, ' .
            '"float" FLOAT NOT NULL, ' .
            '"string" TEXT NOT NULL, ' .
            '"dateTime" TEXT(32) NOT NULL, ' .
            '"null" TEXT DEFAULT NULL, ' .
            '"nullDateTime" TEXT(32) DEFAULT NULL, ' .
            '"array" TEXT NOT NULL'.
            '); -- []');
    }

    function testCreatePartialTable() {
        $this->store->createTable(array('boolean', 'dateTime', 'string'));
        $this->assertLogged('CREATE TABLE IF NOT EXISTS MyTable (' .
            '"id" INTEGER PRIMARY KEY AUTOINCREMENT, ' .
            '"boolean" INTEGER NOT NULL, ' .
            '"string" TEXT NOT NULL, ' .
            '"dateTime" TEXT(32) NOT NULL' .
            '); -- []');
    }

    function testCreateColumn() {
        $this->store->createTable(array('boolean'));
        $this->store->createColumn('string');
        $this->assertLogged('ALTER TABLE MyTable ADD COLUMN "string" TEXT NOT NULL DEFAULT \'\' ' .
            '-- []');
    }

    function testDropTable() {
        $this->createFullTable();
        $this->store->dropTable();
        $this->assertLogged('DROP TABLE MyTable; ' .
            '-- []');
    }

    function testCreate() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'), array("some" => array("here", "there"))));
        $this->assertLogged('INSERT INTO MyTable ("boolean", "integer", "float", "string", "dateTime", "null", "nullDateTime", "array") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null, :nullDateTime, :array)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01T00:00:00+00:00","null":null,"nullDateTime":null,' .
            '"array":"{\"some\":[\"here\",\"there\"]}"}');
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
                'array' => "{\"some\":[\"here\",\"there\"]}"
            )
        ));
    }

    function testCreateWithId() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')), 17);
        $this->assertLogged('INSERT INTO MyTable ("boolean", "integer", "float", "string", "dateTime", "null", "nullDateTime", "array", "id") ' .
            'VALUES (:boolean, :integer, :float, :string, :dateTime, :null, :nullDateTime, :array, :id)' .
            ' -- {"boolean":1,"integer":42,"float":1.6,"string":"Hello","dateTime":"2001-01-01T00:00:00+00:00","null":null,"nullDateTime":null,' .
            '"array":"[]","id":17}');
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
                'array' => "[]"
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
        $this->assertLogged('SELECT * FROM MyTable WHERE "id" = ? LIMIT 1 ' .
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

        $this->assertLogged('UPDATE MyTable SET ' .
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
            '"dateTime":"2001-01-01T00:00:00+00:00",' .
            '"null":null,' .
            '"nullDateTime":null,' .
            '"array":"[]",' .
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
                'array' => "[]"
            )
        ));
    }

    function testDelete() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(false, 17, 1.6, 'Hello', new \DateTime()));

        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01'));
        $this->store->create($entity);

        $this->store->delete($this->store->getKey($entity));

        $this->assertLogged('DELETE FROM MyTable WHERE id = ? ' .
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

        $this->assertLogged('SELECT * FROM MyTable WHERE "integer" = ? LIMIT 1 ' .
            '-- [42]');
    }

    function testReadAll() {
        $this->createFullTable();
        $this->store->create(new StoresTestEntity(false, 17, 1.6, 'Hello', new \DateTime()));
        $this->store->create(new StoresTestEntity(true, 42, 1.6, 'Hello', new \DateTime('2001-01-01')));

        /** @var \spec\watoki\stores\fixtures\StoresTestEntity[] $all */
        $all = $this->store->readAll();
        $this->assertCount(2, $all);

        $this->assertLogged('SELECT * FROM MyTable ' .
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

        $this->assertLogged('SELECT * FROM MyTable WHERE "integer" = ? ' .
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

    function testEmbeddedObjects() {
        $embeddedSerializer = new CompositeSerializer(function ($columns) {
            return new \DateTime('@' . $columns['date']);
        });
        $embeddedSerializer->defineChild('date', new IntegerSerializer(), function (\DateTime $d) {
            return $d->getTimestamp();
        });
        $embeddedSerializer->defineChild('timezone', new StringSerializer(), function (\DateTime $d) {
            return $d->getTimezone()->getName();
        });

        $this->entitySerializer->defineChild('nullDateTime', $embeddedSerializer, function (StoresTestEntity $entity) {
            return $entity->nullDateTime;
        }, function (StoresTestEntity $entity, $value) {
            $entity->nullDateTime = $value;
        });

        $this->createFullTable();
        $entity = new StoresTestEntity(true, 42, 1.6, 'Hello World', new \DateTime('2002-03-04'));
        $entity->nullDateTime = new \DateTime('2001-01-01');
        $this->store->create($entity, 12);

        $this->assertTableEquals(array(
            array(
                'id' => "12",
                'boolean' => "1",
                'integer' => "42",
                'float' => "1.6",
                'string' => "Hello World",
                'dateTime' => '2002-03-04T00:00:00+00:00',
                'null' => null,
                'nullDateTime__date' => "978307200",
                'nullDateTime__timezone' => 'UTC',
                'array' => "[]"
            )
        ));

        $this->initStore();
        /** @var StoresTestEntity $read */
        $read = $this->store->read(12);
        $this->assertEquals('2001-01-01T00:00:00+00:00', $read->nullDateTime->format('c'));
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

        $this->entitySerializer = new CompositeSerializer(function ($row) {
            return new StoresTestEntity(
                $row['boolean'],
                $row['integer'],
                $row['float'],
                $row['string'],
                $row['dateTime'],
                $row['array']
            );
        });

        foreach (StoresTestEntity::serializers() as $child => $childSerializer) {
            $this->entitySerializer->defineChild($child, $childSerializer,
                function ($entity) use ($child) {
                    return $entity->$child;
                },
                function ($entity, $value) use ($child) {
                    $entity->$child = $value;
                });
        }

        $this->initStore();
    }

    private function initStore() {
        $this->store = new SqliteStore($this->entitySerializer, 'MyTable', $this->database);
    }

    private function assertLogged($string) {
        $this->assertEquals($string, $this->database->log);
    }

    private function assertTableEquals($array) {
        $this->assertEquals(json_encode($array), json_encode($this->database->readAll('select * from ' . 'MyTable;')));
    }

    private function assertTableSize($int) {
        $this->assertCount($int, $this->database->readAll('select * from ' . 'MyTable;'));
    }

    private function createFullTable() {
        $this->store->createTable(['id', 'boolean', 'integer', 'float', 'string', 'dateTime', 'null', 'nullDateTime', 'array']);
    }
}