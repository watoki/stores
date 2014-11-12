<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

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