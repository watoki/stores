<?php
namespace watoki\stores\file\raw;

use watoki\stores\file\raw;

class Serializer implements \watoki\stores\Serializer {

    /**
     * @param \watoki\stores\file\raw\File $inflated
     */
    public function serialize($inflated) {
        return $inflated->content;
    }

    public function inflate($serialized) {
        return new raw\File($serialized);
    }
}