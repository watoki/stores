<?php
namespace spec\watoki\stores\pdo;

use watoki\stores\pdo\Store;

class TestStore extends Store {

    protected function getEntityClass() {
        return TestEntity::$CLASS;
    }
}