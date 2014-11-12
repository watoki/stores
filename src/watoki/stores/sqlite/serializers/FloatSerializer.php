<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

class FloatSerializer implements Serializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return floatval($serialized);
    }

    public function getDefinition() {
        return 'FLOAT';
    }
}