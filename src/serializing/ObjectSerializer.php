<?php
namespace watoki\stores\serializing;

use watoki\reflect\PropertyReader;
use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\reflect\TypeFactory;

class ObjectSerializer implements Serializer {

    const TYPE_KEY = 'TYPE';
    const ESCAPED_TYPE_KEY = '_TYPE';

    /** @var Serializer */
    private $primitive;

    /** @var TypeFactory */
    private $types;

    /** @var null|Type */
    private $type;

    /**
     * @param Type $type
     * @param TypeFactory $types
     * @param Serializer $primitive
     */
    public function __construct(Type $type = null, TypeFactory $types = null, Serializer $primitive = null) {
        $this->type = $type;
        $this->types = $types ?: new TypeFactory();
        $this->primitive = $primitive ?: new JsonSerializer();
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value) {
        return $this->primitive->serialize($this->toPrimitive($value, $this->type));
    }

    /**
     * @param string $string
     * @return mixed
     */
    public function inflate($string) {
        return $this->fromPrimitive($this->primitive->inflate($string), $this->type) ?: $string;
    }

    private function toPrimitive($value, Type $type = null) {
        if (is_object($value)) {
            if ($type instanceof ClassType && $type->getClass() == get_class($value)) {
                return $this->objectToPrimitive($value);
            }

            return array_merge(
                [self::TYPE_KEY => get_class($value)],
                $this->objectToPrimitive($value)
            );

        } else if (is_array($value)) {
            return $this->arrayToPrimitive($value, $type);

        } else {
            return $value;
        }
    }

    private function fromPrimitive($inflated, Type $type = null) {
        if (!is_array($inflated)) {
            return $inflated;
        }

        if (array_key_exists(self::TYPE_KEY, $inflated)) {
            $type = new ClassType($inflated[self::TYPE_KEY]);
        }

        if ($type instanceof ClassType) {
            return $this->objectFromPrimitive($type->getClass(), $inflated);
        }

        return $this->arrayFromPrimitive($inflated, $type);
    }

    private function objectToPrimitive($object) {
        $reader = new PropertyReader($this->types, $object);

        $array = [];
        foreach ($reader->readState() as $property) {
            $array[$property->name()] = $this->toPrimitive($property->get($object), $property->type());
        }
        return $array;
    }

    private function objectFromPrimitive($className, $data) {
        $class = new \ReflectionClass($className);
        $instance = $class->newInstanceWithoutConstructor();

        $reader = new PropertyReader($this->types, $class->getName());
        foreach ($reader->readState() as $property) {
            if (array_key_exists($property->name(), $data)) {
                $property->set($instance, $this->fromPrimitive($data[$property->name()], $property->type()));
            }
        }

        return $instance;
    }

    private function arrayToPrimitive(array $array, Type $type = null) {
        $itemType = null;
        if ($type instanceof ArrayType) {
            $itemType = $type->getItemType();
        }

        $primitive = [];
        foreach ($array as $key => $value) {
            $key = str_replace(self::TYPE_KEY, self::ESCAPED_TYPE_KEY, $key);
            $primitive[$key] = $this->toPrimitive($value, $itemType);
        }
        return $primitive;
    }

    private function arrayFromPrimitive(array $data, Type $type = null) {
        $itemType = null;
        if ($type instanceof ArrayType) {
            $itemType = $type->getItemType();
        }

        $array = [];
        foreach ($data as $key => $value) {
            $key = str_replace(self::ESCAPED_TYPE_KEY, self::TYPE_KEY, $key);
            $array[$key] = $this->fromPrimitive($value, $itemType);
        }
        return $array;
    }
}