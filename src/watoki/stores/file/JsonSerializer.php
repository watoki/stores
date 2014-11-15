<?php
namespace watoki\stores\file;

use watoki\stores\common\GenericSerializer;

class JsonSerializer extends GenericSerializer {

    public function serialize($inflated) {
        return json_encode(parent::serialize($inflated));
    }

    public function inflate($serialized) {
        return parent::inflate(json_decode($serialized, true));
    }


} 