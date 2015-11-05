<?php
namespace watoki\stores\file\serializers;

use watoki\stores\Serializer;

class DateTimeSerializer implements Serializer {

    /** @var string */
    private $class;

    /**
     * @param string $class
     */
    public function __construct($class = 'DateTime') {
        $this->class = $class;
    }

    /**
     * @param \DateTime $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated ? $inflated->format('c') : null;
    }

    public function inflate($serialized) {
        $class = $this->class;
        return $serialized ? new $class($serialized) : null;
    }
}