<?php
namespace spec\watoki\stores\memory;

use spec\watoki\stores\TestEntity;
use watoki\stores\memory\Store;

class TestStore extends Store {

    protected function getEntityClass() {
        return TestEntity::$CLASS;
    }
}