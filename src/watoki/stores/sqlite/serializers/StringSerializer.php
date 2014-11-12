<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

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