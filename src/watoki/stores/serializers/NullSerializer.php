<?php
namespace watoki\stores\serializers;

use watoki\stores\Serializer;

class NullSerializer implements Serializer {

    public function serialize($inflated) {
        return null;
    }

    public function inflate($serialized) {
        return null;
    }

    public function getDefinition() {
        throw new \Exception("NULL has no definition");
    }
}