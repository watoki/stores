<?php
namespace watoki\stores;

use watoki\stores\serializers\ArraySerializer;
use watoki\stores\serializers\BooleanSerializer;
use watoki\stores\serializers\DateTimeSerializer;
use watoki\stores\serializers\IntegerSerializer;
use watoki\stores\serializers\NullSerializer;
use watoki\stores\serializers\StringSerializer;

class SerializerRepository {

    const TYPE_STRING = 'string';
    const TYPE_NULL = 'null';
    const TYPE_ARRAY = 'array';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_LONG = 'long';

    /** @var array|Serializer[] */
    private $serializers = array();

    function __construct() {
        $this->setDefaultSerializers();
    }

    public function setSerializer($type, Serializer $serializer) {
        $this->serializers[$type] = $serializer;
    }

    public function getSerializer($type) {
        if (!array_key_exists($type, $this->serializers)) {
            throw new \Exception('There is not serializer registered for [' . $type . ']');
        }
        return $this->serializers[$type];
    }

    public function getType($inflated) {
        if (is_object($inflated)) {
            return get_class($inflated);
        } else if (is_string($inflated)) {
            return self::TYPE_STRING;
        } else if (is_null($inflated)) {
            return self::TYPE_NULL;
        } else if (is_array($inflated)) {
            return self::TYPE_ARRAY;
        } else if (is_bool($inflated)) {
            return self::TYPE_BOOLEAN;
        } else if (is_int($inflated)) {
            return self::TYPE_INTEGER;
        } else if (is_float($inflated)) {
            return self::TYPE_FLOAT;
        } else if (is_double($inflated)) {
            return self::TYPE_DOUBLE;
        } else if (is_long($inflated)) {
            return self::TYPE_LONG;
        }

        throw new \Exception('Could not determine type.');
    }

    private function setDefaultSerializers() {
        $this->setSerializer(self::TYPE_INTEGER, new IntegerSerializer());
        $this->setSerializer(self::TYPE_BOOLEAN, new BooleanSerializer());
        $this->setSerializer(self::TYPE_STRING, new StringSerializer());
        $this->setSerializer(self::TYPE_NULL, new NullSerializer());
        $this->setSerializer(self::TYPE_ARRAY, new ArraySerializer());
        $this->setSerializer(get_class(new \DateTime()), new DateTimeSerializer());
    }

} 