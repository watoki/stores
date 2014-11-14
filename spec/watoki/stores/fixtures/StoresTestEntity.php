<?php
namespace spec\watoki\stores\fixtures;

use watoki\stores\SerializerRegistry;
use watoki\stores\sqlite\serializers\ArraySerializer;
use watoki\stores\sqlite\serializers\BooleanSerializer;
use watoki\stores\sqlite\serializers\DateTimeSerializer;
use watoki\stores\sqlite\serializers\FloatSerializer;
use watoki\stores\sqlite\serializers\IntegerSerializer;
use watoki\stores\sqlite\serializers\StringSerializer;

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

    public static function serializers() {
        return array(
            'boolean' => new BooleanSerializer(),
            'integer' => new IntegerSerializer(),
            'float' => new FloatSerializer(),
            'string' => new StringSerializer(),
            'dateTime' => new DateTimeSerializer(),
            'null' => new StringSerializer(true),
            'nullDateTime' => new DateTimeSerializer(true),
            'array' => new ArraySerializer(new StringSerializer()),
        );
    }
}