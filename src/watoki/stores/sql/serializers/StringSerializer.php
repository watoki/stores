<?php
namespace watoki\stores\sql\serializers;

class StringSerializer extends ColumnSerializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return $serialized;
    }

    public function getColumnDefinition() {
        return 'TEXT';
    }
}