<?php
namespace watoki\stores;

class SerializerRegistry {

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
        $this->registerDefaultSerializers();
    }

    public function register($type, Serializer $serializer) {
        $this->serializers[$type] = $serializer;
    }

    public function getSerializer($type) {
        if (!array_key_exists($type, $this->serializers)) {
            throw new \Exception('There is not serializer registered for [' . $type . ']');
        }
        return $this->serializers[$type];
    }

    /**
     * @param $value
     * @throws \Exception
     * @return string
     */
    public function getType($value) {
        if (is_object($value)) {
            return get_class($value);
        } else if (is_string($value)) {
            return self::TYPE_STRING;
        } else if (is_null($value)) {
            return self::TYPE_NULL;
        } else if (is_array($value)) {
            return self::TYPE_ARRAY;
        } else if (is_bool($value)) {
            return self::TYPE_BOOLEAN;
        } else if (is_int($value)) {
            return self::TYPE_INTEGER;
        } else if (is_float($value)) {
            return self::TYPE_FLOAT;
        } else if (is_double($value)) {
            return self::TYPE_DOUBLE;
        } else if (is_long($value)) {
            return self::TYPE_LONG;
        }

        throw new \Exception('Could not determine type.');
    }

    protected function registerDefaultSerializers() {
    }

} 