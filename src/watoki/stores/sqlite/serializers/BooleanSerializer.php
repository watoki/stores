<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

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