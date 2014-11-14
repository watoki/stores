<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\DefinedSerializer;
use watoki\stores\sqlite\SqliteSerializerRegistry;

class CompositeSerializer implements DefinedSerializer {

    /** @var array|DefinedSerializer[] */
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
     * @param DefinedSerializer $serializer
     * @param callable $getter
     * @param callable|null $setter
     * @return $this
     */
    public function defineChild($name, DefinedSerializer $serializer, $getter, $setter = null) {
        $this->serializers[$name] = $serializer;
        $this->getters[$name] = $getter;
        $this->setters[$name] = $setter ?: function () {
        };
        return $this;
    }

    /**
     * @return array|string[] Definitions of children, indexed by their names.
     */
    public function getDefinition() {
        $definitions = array();
        foreach ($this->serializers as $child => $serializer) {
            $definition = $serializer->getDefinition();
            if (!is_array($definition)) {
                $definitions[$child] = $definition;
            } else {
                foreach ($definition as $grandChild => $grandDefinition) {
                    $definitions[$child . '_' . $grandChild] = $grandDefinition;
                }
            }
        }
        return $definitions;
    }

    /**
     * @param object $inflated
     * @return array
     */
    public function serialize($inflated) {
        $serialized = array();
        foreach ($this->serializers as $child => $serializer) {
            $value = call_user_func($this->getters[$child], $inflated);
            $serializedValue = $serializer->serialize($value);

            if (!is_array($serializedValue)) {
                $serialized[$child] = $serializedValue;
            } else {
                foreach ($serializedValue as $grandChild => $grandValue) {
                    $serialized[$child . '_' . $grandChild] = $grandValue;
                }
            }
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
            $definition = $serializer->getDefinition();

            if (!is_array($definition)) {
                $serializedChild = $serialized[$child];
            } else {
                $serializedChild = array();
                foreach ($serialized as $column => $value) {
                    if (substr($column, 0, strlen($child) + 1) == $child . '_') {
                        $serializedChild[substr($column, strlen($child) + 1)] = $value;
                    }
                }
            }
            $inflatedChild = $serializer->inflate($serializedChild);
            call_user_func($this->setters[$child], $object, $inflatedChild);
        }
        return $object;
    }
}