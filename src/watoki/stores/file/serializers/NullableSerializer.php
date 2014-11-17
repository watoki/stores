<?php
namespace watoki\stores\file\serializers;

use watoki\stores\Serializer;

class NullableSerializer implements Serializer {

    /** @var Serializer */
    private $serializer;

    function __construct(Serializer $serializer) {
        $this->serializer = $serializer;
    }

    public function serialize($inflated) {
        return $inflated ? $this->serializer->serialize($inflated) : null;
    }

    public function inflate($serialized) {
        return $serialized ? $this->serializer->inflate($serialized) : null;
    }
}