<?php
namespace watoki\stores\serializers;

use watoki\stores\Serializer;

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