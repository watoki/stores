<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\DefinedSerializer;

class NullSerializer implements DefinedSerializer {

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