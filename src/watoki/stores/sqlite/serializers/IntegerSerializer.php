<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

class IntegerSerializer implements Serializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return intval($serialized);
    }

    public function getDefinition() {
        return 'INTEGER';
    }
}