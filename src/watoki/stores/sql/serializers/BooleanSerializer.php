<?php
namespace watoki\stores\sql\serializers;

class BooleanSerializer extends ColumnSerializer {

    public function serialize($inflated) {
        return $inflated ? 1 : 0;
    }

    public function inflate($serialized) {
        return !!$serialized;
    }

    public function getColumnDefinition() {
        return 'INTEGER';
    }
}