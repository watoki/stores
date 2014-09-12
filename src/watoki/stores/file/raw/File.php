<?php
namespace watoki\stores\file\raw;

/**
 * @property string id
 */
class File {

    public static $CLASS = __CLASS__;

    public $content;

    function __construct($content) {
        $this->content = $content;
    }

}