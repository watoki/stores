<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

class NullSerializer implements Serializer {

    public function serialize($inflated) {
        return null;
    }

    public function inflate($serialized) {
        return null;
    }

    public function getDefinition() {
        throw new \Exception('Null does not have a definition');
    }
}