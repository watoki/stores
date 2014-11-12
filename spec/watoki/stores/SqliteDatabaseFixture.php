<?php
namespace spec\watoki\stores;

use spec\watoki\stores\lib\TestDatabase;
use watoki\scrut\Fixture;
use watoki\stores\sqlite\Database;

class SqliteDatabaseFixture extends Fixture {

    /**
     * @var TestDatabase
     */
    public $database;

    public function setUp() {
        parent::setUp();

        $this->database = new TestDatabase(new \PDO('sqlite::memory:'));
        $this->spec->factory->setSingleton(Database::$CLASS, $this->database);
    }
}