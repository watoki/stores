<?php
namespace watoki\stores\common;

use watoki\stores\Serializer;

class NoneSerializer implements Serializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return $serialized;
    }
}