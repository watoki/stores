<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

class ArraySerializer implements Serializer {

    public function serialize($inflated) {
        return json_encode($inflated);
    }

    public function inflate($serialized) {
        return json_decode($serialized, true);
    }

    public function getDefinition() {
        return 'TEXT(1024)';
    }
} 
