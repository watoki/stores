<?php
namespace spec\watoki\stores\pdo;

use spec\watoki\stores\TestEntity;
use watoki\stores\pdo\Store;

class TestStore extends Store {

    protected function getEntityClass() {
        return TestEntity::$CLASS;
    }
}