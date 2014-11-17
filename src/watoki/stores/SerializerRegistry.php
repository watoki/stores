<?php
namespace watoki\stores;

use watoki\reflect\type\MultiType;
use watoki\reflect\type\UnknownType;
use watoki\reflect\Type;

class SerializerRegistry {

    public static $CLASS = __CLASS__;

    /** @var array|SerializerFactory[] */
    private $factories = array();

    public function add(SerializerFactory $factory) {
        $this->factories[] = $factory;
    }

    public function get(Type $type) {
        if ($type instanceof UnknownType) {
            throw new \Exception("Unknown type [{$type->getHint()}].");
        } else if ($type instanceof MultiType) {
            throw new \Exception('Ambiguous type.');
        }

        foreach ($this->factories as $factory) {
            if ($factory->appliesTo($type)) {
                return $factory->createSerializer($type);
            }
        }

        $typePrinted = str_replace("\n", "", print_r($type, true));
        throw new \Exception("No Serializer registered for [$typePrinted]");
    }

}