<?php
namespace watoki\stores;

use watoki\reflect\Type;

class SerializerRegistry {

    public static $CLASS = __CLASS__;

    /** @var array|SerializerFactory[] */
    private $factories = array();

    public function add(SerializerFactory $factory) {
        $this->factories[] = $factory;
    }

    public function get(Type $type) {
        foreach ($this->factories as $factory) {
            if ($factory->appliesTo($type)) {
                return $factory->createSerializer($type);
            }
        }

        throw new \Exception("No Serializer registered for [$type]");
    }

}