<?php
namespace watoki\stores\sql\serializers;

class FloatSerializer extends ColumnSerializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return floatval($serialized);
    }

    public function getColumnDefinition() {
        return 'FLOAT';
    }
}