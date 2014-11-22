<?php
namespace watoki\stores\file\raw;

class LazyFile extends File {

    private $file;

    /**
     * @param string $fullPath
     */
    public function __construct($fullPath) {
        $this->file = $fullPath;
    }

    public function getContents() {
        return file_get_contents($this->file);
    }

} 