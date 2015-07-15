<?php
namespace watoki\stores\common;

use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;
use watoki\stores\SerializerRegistry;

class Reflector {

    /** @var string */
    protected $class;

    /** @var \watoki\stores\SerializerRegistry */
    protected $registry;

    /** @var TypeFactory */
    private $typeFactory;

    /**
     * @param string $class
     * @param \watoki\stores\SerializerRegistry $registry
     * @param TypeFactory $types
     */
    public function __construct($class, SerializerRegistry $registry, TypeFactory $types) {
        $this->class = $class;
        $this->registry = $registry;
        $this->typeFactory = $types;
    }

    /**
     * @param string $genericSerializerClass
     * @return GenericSerializer|mixed instance of $genericSerializerClass
     */
    public function create($genericSerializerClass) {
        $genericSerializer = $this->createGenericSerializer($genericSerializerClass);
        $this->defineProperties($genericSerializer);
        return $genericSerializer;
    }


    /**
     * @param $genericSerializerClass
     * @return GenericSerializer|mixed instance of $genericSerializerClass
     * @throws \InvalidArgumentException
     */
    protected function createGenericSerializer($genericSerializerClass) {
        if (!$this->isGenericSerializer($genericSerializerClass)) {
            throw new \InvalidArgumentException(
                "[$genericSerializerClass] is not a subclass of [" . GenericSerializer::$CLASS . "]");
        }

        $reflection = new \ReflectionClass($this->class);

        return new $genericSerializerClass(function () use ($reflection) {
            return $reflection->newInstanceWithoutConstructor();
        });
    }

    /**
     * @param GenericSerializer $genericSerializer
     * @throws \Exception
     */
    protected function defineProperties(GenericSerializer $genericSerializer) {
        $reader = new PropertyReader($this->typeFactory, $this->class);
        $properties = $reader->readState(~\ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $name => $property) {
            try {
                $this->defineProperty($name, $property, $genericSerializer);
            } catch (\Exception $e) {
                throw new \Exception(
                    "Could not infer Serializer of [{$this->class}::$name]: " . $e->getMessage(), 0, $e);
            }
        }
    }

    private function defineProperty($name, Property $property, GenericSerializer $genericSerializer) {
        $serializer = $this->registry->get($property->type());

        $getter = function ($object) use ($property) {
            return $property->get($object);
        };
        $setter = function ($object, $value) use ($property) {
            $property->set($object, $value);
        };

        $genericSerializer->defineChild($name, $serializer, $getter, $setter);
    }

    /**
     * @param string $genericSerializerClass
     * @return bool
     */
    protected function isGenericSerializer($genericSerializerClass) {
        return $genericSerializerClass == GenericSerializer::$CLASS
        || is_subclass_of($genericSerializerClass, GenericSerializer::$CLASS);
    }
}