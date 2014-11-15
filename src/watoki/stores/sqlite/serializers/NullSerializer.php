<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\SqliteSerializer;

class NullSerializer implements SqliteSerializer {

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