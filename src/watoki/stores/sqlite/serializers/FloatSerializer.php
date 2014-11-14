<?php
namespace watoki\stores\sqlite\serializers;

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