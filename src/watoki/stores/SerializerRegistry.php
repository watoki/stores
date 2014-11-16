<?php
namespace watoki\stores;

use watoki\reflect\Type;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\UnknownType;

class SerializerRegistry {

    public static $CLASS = __CLASS__;

    /** @var array|Serializer[] */
    private $serializers = array();

    /** @var null|callable */
    private $fallback;

    public function register(Type $type, Serializer $serializer) {
        $this->serializers[serialize($type)] = $serializer;
    }

    public function get(Type $type) {
        if ($type instanceof UnknownType) {
            throw new \Exception("Unknown type [{$type->getHint()}].");
        } else if ($type instanceof MultiType) {
            throw new \Exception('Ambiguous type.');
        }

        $key = serialize($type);
        if (array_key_exists($key, $this->serializers)) {
            return $this->serializers[$key];
        }

        if ($this->fallback) {
            $fallback = call_user_func($this->fallback, $type);
            if ($fallback) {
                return $fallback;
            }
        }

        $typePrinted = str_replace("\n", "", print_r($type, true));
        throw new \Exception("No Serializer registered for [$typePrinted]");

    }

    /**
     * @param callable $callback
     */
    public function setFallback($callback) {
        $this->fallback = $callback;
    }

}