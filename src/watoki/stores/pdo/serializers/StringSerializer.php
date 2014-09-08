<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

class StringSerializer implements Serializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return $serialized;
    }

    public function getDefinition() {
        return 'TEXT(255)';
    }
}