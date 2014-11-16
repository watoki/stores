<?php
namespace watoki\stores\common;

use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\stores\Serializer;
use watoki\stores\SerializerRegistry;

class ReflectingSerializer implements Serializer {

    /** @var \watoki\stores\common\GenericSerializer */
    private $genericSerializer;

    /**
     * @param string $class
     * @param \watoki\stores\SerializerRegistry $registry <-
     * @param null|string $genericSerializerClass Class extending GenericSerializer (defaults to GenericSerializer)
     * @throws \InvalidArgumentException
     * @throws \Exception if not all properties of given class have a defined type hint
     */
    public function __construct($class, SerializerRegistry $registry, $genericSerializerClass = null) {
        $genericSerializerClass = $genericSerializerClass ? : GenericSerializer::$CLASS;
        $this->genericSerializer = $this->createGenericSerializer($class, $genericSerializerClass);
        $this->defineProperties($registry, $class);
    }

    public function serialize($inflated) {
        return $this->genericSerializer->serialize($inflated);
    }

    public function inflate($serialized) {
        return $this->genericSerializer->inflate($serialized);
    }

    /**
     * @param string $class
     * @param string $genericSerializerClass
     * @return GenericSerializer
     * @throws \InvalidArgumentException
     */
    protected function createGenericSerializer($class, $genericSerializerClass) {
        if ($genericSerializerClass != GenericSerializer::$CLASS
            && !is_subclass_of($genericSerializerClass, GenericSerializer::$CLASS)
        ) {
            throw new \InvalidArgumentException("[$genericSerializerClass] is not a subclass of [" . GenericSerializer::$CLASS . "]");
        }

        $reflection = new \ReflectionClass($class);

        return new $genericSerializerClass(function () use ($reflection) {
            return $reflection->newInstanceWithoutConstructor();
        });
    }

    private function defineProperties(SerializerRegistry $registry, $class) {
        $reader = new PropertyReader($class);
        $properties = $reader->readState(~\ReflectionProperty::IS_PRIVATE);

        foreach ($properties as $name => $property) {
            try {
                $this->defineProperty($name, $property, $registry);
            } catch (\Exception $e) {
                throw new \Exception("Could not infer Serializer of [$class::$name]: " . $e->getMessage(), 0, $e);
            }
        }
    }

    private function defineProperty($name, Property $property, SerializerRegistry $registry) {
        $serializer = $registry->get($property->type());

        $getter = function ($object) use ($property) {
            return $property->get($object);
        };
        $setter = function ($object, $value) use ($property) {
            $property->set($object, $value);
        };

        $this->genericSerializer->defineChild($name, $serializer, $getter, $setter);
    }
}