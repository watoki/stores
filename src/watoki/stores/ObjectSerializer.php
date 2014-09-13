<?php
namespace watoki\stores;

use watoki\factory\ClassResolver;

class ObjectSerializer implements Serializer {

    /** @var \ReflectionClass */
    protected $class;

    /** @var SerializerRepository */
    private $serializers;

    function __construct($class, SerializerRepository $serializers) {
        $this->class = new \ReflectionClass($class);
        $this->serializers = $serializers;
    }

    public function serialize($inflated) {
        $row = array();
        foreach ($this->getPersistedProperties() as $property) {
            $value = $property->getValue($inflated);
            $type = $this->serializers->getType($value);
            $row[$property->getName()] = $this->serializers->getSerializer($type)->serialize($value);
        }
        return $row;
    }

    public function inflate($serialized) {
        $inflated = $this->class->newInstanceWithoutConstructor();
        foreach ($this->getPersistedProperties() as $property) {
            if (!array_key_exists($property->getName(), $serialized)) {
                continue;
            }

            $value = $serialized[$property->getName()];
            $type = $this->getTypeOfProperty($property);
            $property->setValue($inflated, $this->serializers->getSerializer($type)->inflate($value));
        }
        return $inflated;
    }

    /**
     * @return \ReflectionProperty[]
     */
    protected function getPersistedProperties() {
        return array_filter($this->class->getProperties(), function (\ReflectionProperty $prop) {
            $prop->setAccessible(true);
            return !$prop->isStatic();
        });
    }

    protected function getTypeOfProperty(\ReflectionProperty $property) {
        $resolver = new ClassResolver($property->getDeclaringClass());

        foreach ($this->getTypeHints($property) as $typeHint) {
            $className = $resolver->resolve(ltrim($typeHint, '\\'));
            if ($className) {
                return $className;
            }

            $type = $this->getPrimitiveTypeFromHint($typeHint);
            if ($type && $type != SerializerRepository::TYPE_NULL) {
                return $type;
            }
        }

        throw new \Exception('Could not determine type of ' .
            "[{$property->getDeclaringClass()->getName()}::{$property->getName()}].");
    }

    protected function getTypeHints(\ReflectionProperty $property) {
        $matches = array();
        $found = preg_match('/@var\s+(\S+)/', $property->getDocComment(), $matches);

        if (!$found) {
            throw new \Exception("Could not find type hint of property " .
                "[{$property->getDeclaringClass()->getName()}::{$property->getName()}].");
        }
        return $this->explodeMultipleHints($matches[1]);
    }

    protected function getPrimitiveTypeFromHint($hint) {
        switch (strtolower($hint)) {
            case 'array':
                return SerializerRepository::TYPE_ARRAY;
            case 'int':
            case 'integer':
                return SerializerRepository::TYPE_INTEGER;
            case 'long':
                return SerializerRepository::TYPE_LONG;
            case 'float':
                return SerializerRepository::TYPE_FLOAT;
            case 'double':
                return SerializerRepository::TYPE_DOUBLE;
            case 'bool':
            case 'boolean':
                return SerializerRepository::TYPE_BOOLEAN;
            case 'string':
                return SerializerRepository::TYPE_STRING;
            case 'null':
                return SerializerRepository::TYPE_NULL;
            default:
                return null;
        }
    }

    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

    /**
     * @return SerializerRepository
     */
    protected function getSerializers() {
        return $this->serializers;
    }
}