<?php
namespace watoki\stores\file\serializers;

use watoki\stores\common\GenericSerializer;

class JsonSerializer extends GenericSerializer {

    public static $CLASS = __CLASS__;

    public function serialize($inflated) {
        return json_encode(parent::serialize($inflated), JSON_PRETTY_PRINT);
    }

    public function inflate($serialized) {
        return parent::inflate(json_decode($serialized, true));
    }

} 