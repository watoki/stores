<?php
namespace watoki\stores\common;

class JsonSerializer extends GenericSerializer {

    public static $CLASS = __CLASS__;

    public function serialize($inflated) {
        return json_encode(parent::serialize($inflated));
    }

    public function inflate($serialized) {
        return parent::inflate(json_decode($serialized, true));
    }

} 