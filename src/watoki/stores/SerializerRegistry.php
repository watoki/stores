<?php
namespace watoki\stores;

use watoki\collections\Liste;
use watoki\reflect\Type;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\UnknownType;

class SerializerRegistry {

    public static $CLASS = __CLASS__;

    /** @var array|Serializer[] */
    private $serializers = array();

    /** @var Liste|callable[] */
    private $fallBacks;

    public function __construct() {
        $this->fallBacks = new Liste();
    }

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

        foreach ($this->fallBacks as $fallBack) {
            $serializer = call_user_func($fallBack, $type);
            if ($serializer) {
                return $serializer;
            }
        }

        $typePrinted = str_replace("\n", "", print_r($type, true));
        throw new \Exception("No Serializer registered for [$typePrinted]");

    }

    /**
     * @return \callable[]|\watoki\collections\Liste
     */
    public function getFallBacks() {
        return $this->fallBacks;
    }

}