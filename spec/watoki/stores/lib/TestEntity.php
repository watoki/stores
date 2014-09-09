<?php
namespace spec\watoki\stores\lib;

/**
 * @property mixed id
 */
class TestEntity {

    public static $CLASS = __CLASS__;

    /** @var boolean */
    private $boolean;

    /** @var integer */
    private $integer;

    /** @var float */
    private $float;

    /** @var string */
    private $string;

    /** @var \DateTime */
    private $dateTime;

    /** @var string|null */
    private $null;

    function __construct($boolean, $integer, $float, $string, $dateTime) {
        $this->boolean = $boolean;
        $this->dateTime = $dateTime;
        $this->float = $float;
        $this->integer = $integer;
        $this->string = $string;
    }

    /**
     * @return boolean
     */
    public function getBoolean() {
        return $this->boolean;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime() {
        return $this->dateTime;
    }

    /**
     * @return float
     */
    public function getFloat() {
        return $this->float;
    }

    /**
     * @return int
     */
    public function getInteger() {
        return $this->integer;
    }

    /**
     * @return string
     */
    public function getString() {
        return $this->string;
    }

    /**
     * @return null|string
     */
    public function getNull() {
        return $this->null;
    }

    public function setString($string) {
        $this->string = $string;
    }
}