<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\Serializer;

class DateTimeSerializer implements Serializer {

    /**
     * @param \DateTime $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated->format('Y-m-d H:i:s');
    }

    public function inflate($serialized) {
        return new \DateTime($serialized);
    }

    public function getDefinition() {
        return 'TEXT(32)';
    }
}