<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;

class DateTimeSerializer implements Serializer {

    /**
     * @param \DateTime $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated->format('Y-m-d H:i:s');
    }

    public function inflate($serialized) {
        if (!$serialized) {
            return null;
        }
        return new \DateTime($serialized);
    }

    public function getDefinition() {
        return 'TEXT(32)';
    }
}