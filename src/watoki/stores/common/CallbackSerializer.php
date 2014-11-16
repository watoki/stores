<?php
namespace watoki\stores\common;

use watoki\stores\Serializer;

class CallbackSerializer implements Serializer {

    private $serializer;

    private $inflater;

    /**
     * @param callable $serializer
     * @param callable $inflater
     */
    public function __construct($serializer, $inflater) {
        $this->serializer = $serializer;
        $this->inflater = $inflater;
    }


    public function serialize($inflated) {
        return call_user_func($this->serializer, $inflated);
    }

    public function inflate($serialized) {
        return call_user_func($this->inflater, $serialized);
    }
}