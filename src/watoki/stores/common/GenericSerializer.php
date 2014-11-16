<?php
namespace watoki\stores\common;

use watoki\stores\Serializer;

class GenericSerializer implements Serializer {

    public static $CLASS = __CLASS__;

    /** @var array|Serializer[] */
    private $serializers = array();

    /** @var array|callable[] */
    private $getters = array();

    /** @var array|callable[] */
    private $setters = array();

    /** @var callable */
    private $creator;

    /**
     * @param callable $creator
     */
    public function __construct($creator) {
        $this->creator = $creator;
    }

    /**
     * @param string $name
     * @param Serializer $serializer
     * @param callable $getter
     * @param callable|null $setter
     * @return $this
     */
    public function defineChild($name, Serializer $serializer, $getter, $setter = null) {
        $this->serializers[$name] = $serializer;
        $this->getters[$name] = $getter;
        $this->setters[$name] = $setter ?: function () {};
        return $this;
    }

    /**
     * @param object $inflated
     * @return array
     */
    public function serialize($inflated) {
        $serialized = array();
        foreach ($this->serializers as $child => $serializer) {
            $value = call_user_func($this->getters[$child], $inflated);
            $serialized[$child] = $serializer->serialize($value);
        }
        return $serialized;
    }

    /**
     * @param array $serialized
     * @return array
     */
    public function inflate($serialized) {
        $object = call_user_func($this->creator, $serialized);
        foreach ($this->serializers as $child => $serializer) {
            $inflatedChild = $serializer->inflate($serialized[$child]);
            call_user_func($this->setters[$child], $object, $inflatedChild);
        }
        return $object;
    }
}