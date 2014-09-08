<?php
namespace watoki\stores\memory;

class Serializer implements \watoki\stores\Serializer {

    public function serialize($inflated) {
        return $inflated;
    }

    public function inflate($serialized) {
        return $serialized;
    }
}