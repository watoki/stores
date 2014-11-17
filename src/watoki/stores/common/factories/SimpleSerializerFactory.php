<?php
namespace watoki\stores\common\factories;

use watoki\reflect\Type;
use watoki\stores\SerializerFactory;

class SimpleSerializerFactory implements SerializerFactory {

    /** @var string */
    private $typeClass;

    /** @var callable */
    private $creator;

    /**
     * @param string $typeClass
     * @param callable $creator
     */
    public function __construct($typeClass, $creator) {
        $this->creator = $creator;
        $this->typeClass = $typeClass;
    }

    /**
     * @param Type $type
     * @return boolean
     */
    public function appliesTo(Type $type) {
        return is_a($type, $this->typeClass);
    }

    /**
     * @param Type $type
     * @return \watoki\stores\Serializer
     */
    public function createSerializer(Type $type) {
        return call_user_func($this->creator, $type);
    }
}