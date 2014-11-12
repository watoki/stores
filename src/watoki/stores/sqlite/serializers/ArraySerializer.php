<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

class ArraySerializer implements Serializer {

    public function serialize($inflated) {
        return json_encode($inflated);
    }

    public function inflate($serialized) {
        return json_decode($serialized, true);
    }

    public function getDefinition() {
        return 'TEXT';
    }
} 
