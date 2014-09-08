<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

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