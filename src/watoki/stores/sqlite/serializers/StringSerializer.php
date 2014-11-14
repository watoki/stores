<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\DefinedSerializer;

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