<?php
namespace watoki\stores\file\raw;

use watoki\stores\Serializer;

class FileSerializer implements Serializer {

    /**
     * @param \watoki\stores\file\raw\File $inflated
     * @return mixed
     */
    public function serialize($inflated) {
        return $inflated->getContents();
    }

    /**
     * @param string $file
     * @return File
     */
    public function inflate($file) {
        return new LazyFile($file);
    }
}