<?php
namespace watoki\stores\serializing;

use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;

class DynamicObjectSerializer implements Serializer {

    const ARRAY_TYPE = 'array';

    /** @var Serializer */
    private $primitive;

    /** @var TypeFactory */
    private $types;

    /**
     * @param Serializer $primitive
     */
    public function __construct(Serializer $primitive) {
        $this->primitive = $primitive;
        $this->types = new TypeFactory();
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value) {
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
        if (is_object($value)) {
            return [
                'type' => get_class($value),
                'data' => $this->objectToPrimitive($value)
            ];
        } else if (is_array($value)) {
            return [
                'type' => 'array',
                'data' => $this->arrayToPrimitive($value)
            ];
        } else {
            return $value;
        }
    }

    private function fromPrimitive($inflated) {
        if (!is_array($inflated)) {
            return $inflated;
        }

        if ($inflated['type'] == self::ARRAY_TYPE) {
            return $this->arrayFromPrimitive($inflated['data']);
        }

        return $this->objectFromPrimitive($inflated['type'], $inflated['data']);
    }

    private function objectToPrimitive($object) {
        $reader = new PropertyReader($this->types, $object);

        $array = [];
        foreach ($reader->readState() as $property) {
            $array[$property->name()] = $this->toPrimitive($property->get($object));
        }
        return $array;
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

    private function arrayToPrimitive($array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->toPrimitive($value);
        }
        return $array;
    }

    private function arrayFromPrimitive($data) {
        foreach ($data as $key => $value) {
            $data[$key] = $this->fromPrimitive($value);
        }
        return $data;
    }
}