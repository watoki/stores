<?php
namespace watoki\stores\common\factories;

use watoki\reflect\Type;
use watoki\stores\SerializerFactory;

class CallbackSerializerFactory implements SerializerFactory {

    /** @var callable */
    private $matcher;

    /** @var callable */
    private $creator;

    /**
     * @param callable $matcher
     * @param callable $creator
     */
    public function __construct($matcher, $creator) {
        $this->creator = $creator;
        $this->matcher = $matcher;
    }

    /**
     * @param \watoki\reflect\Type $type
     * @return boolean
     */
    public function appliesTo(Type $type) {
        return call_user_func($this->matcher, $type);
    }

    /**
     * @param \watoki\reflect\Type $type
     * @return \watoki\stores\Serializer
     */
    public function createSerializer(Type $type) {
        return call_user_func($this->creator, $type);
    }
}