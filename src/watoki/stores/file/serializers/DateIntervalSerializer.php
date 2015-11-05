<?php namespace watoki\stores\file\serializers;

use watoki\stores\Serializer;

class DateIntervalSerializer implements Serializer {

    /**
     * @param \DateInterval $inflated
     * @return string|null
     */
    public function serialize($inflated) {
        return $inflated ? $inflated->format('P%dDT%hH%iM') : null;
    }

    /**
     * @param string $serialized
     * @return \DateInterval|null
     */
    public function inflate($serialized) {
        return $serialized ? new \DateInterval($serialized) : null;
    }
}