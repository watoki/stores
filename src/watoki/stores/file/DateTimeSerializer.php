<?php
namespace watoki\stores\file;

use watoki\stores\Serializer;

class DateTimeSerializer implements Serializer {

    /**
     * @param \DateTime $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated->format('c');
    }

    public function inflate($serialized) {
        return new \DateTime($serialized);
    }
}