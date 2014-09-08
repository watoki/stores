<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

class BooleanSerializer implements Serializer {

    public function serialize($inflated) {
        return $inflated ? 1 : 0;
    }

    public function inflate($serialized) {
        return !!$serialized;
    }

    public function getDefinition() {
        return 'INTEGER';
    }
}