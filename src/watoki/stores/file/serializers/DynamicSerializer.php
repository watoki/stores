<?php namespace watoki\stores\file\serializers;

use watoki\reflect\TypeFactory;
use watoki\stores\common\GenericSerializer;
use watoki\stores\common\Reflector;
use watoki\stores\Serializer;
use watoki\stores\SerializerRegistry;

class DynamicSerializer implements Serializer {

    /** @var SerializerRegistry */
    protected $registry;

    /** @var TypeFactory */
    private $typeFactory;

    /**
     * @param SerializerRegistry $registry
     * @param TypeFactory $types
     */
    public function __construct(SerializerRegistry $registry, TypeFactory $types) {
        $this->registry = $registry;
        $this->typeFactory = $types;
    }

    /**
     * @param object $inflated
     * @return array
     */
    public function serialize($inflated) {
        if (!is_object($inflated)) {
            return $inflated;
        }
        return [
            'class' => get_class($inflated),
            'data' => $this->serializer(get_class($inflated))->serialize($inflated)
        ];
    }

    public function inflate($serialized) {
        if (!is_array($serialized)) {
            return $serialized;
        }
        return $this->serializer($serialized['class'])->inflate($serialized['data']);
    }

    private function serializer($class) {
        $reflector = new Reflector($class, $this->registry, $this->typeFactory);
        return $reflector->create(GenericSerializer::$CLASS);
    }
}