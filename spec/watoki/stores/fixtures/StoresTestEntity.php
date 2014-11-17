<?php
namespace spec\watoki\stores\fixtures;

class StoresTestEntity {

    public static $CLASS = __CLASS__;

    /** @var boolean */
    public $boolean;

    /** @var integer */
    public $integer;

    /** @var float */
    public $float;

    /** @var string */
    public $string;

    /** @var \DateTime */
    public $dateTime;

    /** @var string|null */
    public $null;

    /** @var \DateTime|null */
    public $nullDateTime;

    /** @var array|string */
    public $array;

    /** @var StoresTestEntity_Child */
    public $child;

    function __construct($boolean, $integer, $float, $string, $dateTime, $array = array()) {
        $this->boolean = $boolean;
        $this->dateTime = $dateTime;
        $this->float = $float;
        $this->integer = $integer;
        $this->string = $string;
        $this->array = $array;

        $this->child = new StoresTestEntity_Child('uno', 'dos', new StoresTestEntity_GrandChild());
    }

}

class StoresTestEntity_Child {

    /** @var string */
    public $one;

    /** @var integer */
    public $two;

    /** @var StoresTestEntity_GrandChild */
    public $child;

    function __construct($one, $two, $child = null) {
        $this->child = $child;
        $this->one = $one;
        $this->two = $two;
    }

}

class StoresTestEntity_GrandChild {

    /** @var string */
    public $foo = 'bar';

}