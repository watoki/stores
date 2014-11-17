<?php
namespace watoki\stores\file\serializers;

use watoki\stores\Serializer;

class DateTimeSerializer implements Serializer {

    /**
     * @param \DateTime $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated ? $inflated->format('c') : null;
    }

    public function inflate($serialized) {
        return $serialized ? new \DateTime($serialized) : null;
    }
}