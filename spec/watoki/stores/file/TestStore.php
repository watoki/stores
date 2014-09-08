<?php
namespace spec\watoki\stores\file;

use spec\watoki\stores\TestEntity;
use watoki\stores\file\Store;

class TestStore extends Store {

    protected function getEntityClass() {
        return TestEntity::$CLASS;
    }
}