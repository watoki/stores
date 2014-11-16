<?php
namespace spec\watoki\stores\fixtures;

class StoresTestEntity {

    public static $CLASS = __CLASS__;

    public $boolean;

    public $integer;

    public $float;

    public $string;

    /** @var \DateTime */
    public $dateTime;

    /** @var string|null */
    public $null;

    /** @var \DateTime|null */
    public $nullDateTime;

    /** @var array */
    public $array;

    function __construct($boolean, $integer, $float, $string, $dateTime, $array = array()) {
        $this->boolean = $boolean;
        $this->dateTime = $dateTime;
        $this->float = $float;
        $this->integer = $integer;
        $this->string = $string;
        $this->array = $array;
    }

}