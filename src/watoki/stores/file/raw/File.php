<?php
namespace watoki\stores\file\raw;

class File {

    public static $CLASS = __CLASS__;

    private $contents;

    function __construct($contents) {
        $this->contents = $contents;
    }

    /**
     * @return mixed
     */
    public function getContents() {
        return $this->contents;
    }

}