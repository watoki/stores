<?php
namespace watoki\stores\sql\serializers;

class IntegerSerializer extends ColumnSerializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return intval($serialized);
    }

    public function getColumnDefinition() {
        return 'INTEGER';
    }
}