<?php
namespace watoki\stores\file\raw;

use watoki\stores\Serializer;

class FileSerializer implements Serializer {

    /**
     * @param \watoki\stores\file\raw\File $inflated
     */
    public function serialize($inflated) {
        return $inflated->content;
    }

    public function inflate($serialized) {
        return new File($serialized);
    }
}