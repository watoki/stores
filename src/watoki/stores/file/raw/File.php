<?php
namespace watoki\stores\file\raw;

class File {

    public static $CLASS = __CLASS__;

    public $content;

    function __construct($content) {
        $this->content = $content;
    }

}