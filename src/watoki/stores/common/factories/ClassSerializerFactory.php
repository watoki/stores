<?php
namespace watoki\stores\common\factories;

use watoki\reflect\Type;
use watoki\reflect\type\ClassType;
use watoki\stores\Serializer;
use watoki\stores\SerializerFactory;

class ClassSerializerFactory implements SerializerFactory {

    /** @var string */
    private $class;

    /** @var Serializer */
    private $serializer;

    /**
     * @param string $class
     * @param \watoki\stores\Serializer $serializer
     */
    public function __construct($class, Serializer $serializer) {
        $this->class = $class;
        $this->serializer = $serializer;
    }

    /**
     * @param \watoki\reflect\Type $type
     * @return boolean
     */
    public function appliesTo(Type $type) {
        return $type instanceof ClassType && $type->getClass() == $this->class;
    }

    /**
     * @param \watoki\reflect\Type $type
     * @return Serializer
     */
    public function createSerializer(Type $type) {
        return $this->serializer;
    }
}