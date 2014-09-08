<?php
namespace watoki\stores\file;

class ObjectSerializer extends \watoki\stores\ObjectSerializer {

    public function inflate($serialized) {
        return parent::inflate(json_decode($serialized, true));
    }

    public function serialize($inflated) {
        return json_encode(parent::serialize($inflated), JSON_PRETTY_PRINT);
    }

}