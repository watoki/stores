<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

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