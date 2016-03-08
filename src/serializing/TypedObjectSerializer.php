<?php
namespace watoki\stores\serializing;

use watoki\reflect\PropertyReader;
use watoki\reflect\Type;
use watoki\reflect\type\ClassType;
use watoki\reflect\TypeFactory;

class TypedObjectSerializer implements Serializer {

    /** @var Type */
    private $type;

    /** @var Serializer */
    private $primitive;

    /** @var TypeFactory */
    private $types;

    /**
     * @param Type $type
     * @param Serializer $primitive
     * @param TypeFactory $types
     */
    public function __construct(Type $type, Serializer $primitive, TypeFactory $types) {
        $this->primitive = $primitive;
        $this->types = $types;
        $this->type = $type;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws \Exception if value doesn't match type
     */
    public function serialize($value) {
        if (!$this->type->is($value)) {
            throw new \Exception("Given value is not of type [{$this->type}]");
        }
        return $this->primitive->serialize($this->toPrimitive($value));
    }

    /**
     * @param string $string
     * @return mixed
     */
    public function inflate($string) {
        return $this->fromPrimitive($this->primitive->inflate($string)) ?: $string;
    }

    private function toPrimitive($value) {
        return $value;
    }

    private function fromPrimitive($inflated) {
        if (!($this->type instanceof ClassType)) {
            return $inflated;
        }

        return $this->objectFromPrimitive($this->type->getClass(), $inflated);
    }

    private function objectFromPrimitive($className, $data) {
        $class = new \ReflectionClass($className);
        $instance = $class->newInstanceWithoutConstructor();

        $reader = new PropertyReader($this->types, $class->getName());
        foreach ($reader->readState() as $property) {
            if (array_key_exists($property->name(), $data)) {
                $property->set($instance, $this->fromPrimitive($data[$property->name()]));
            }
        }

        return $instance;
    }
}