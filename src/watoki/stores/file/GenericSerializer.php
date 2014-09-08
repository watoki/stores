<?php
namespace watoki\stores\file;

use watoki\stores\Serializer;

class GenericSerializer implements Serializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return $serialized;
    }
}