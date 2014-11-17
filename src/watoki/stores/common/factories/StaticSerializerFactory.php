<?php
namespace watoki\stores\common\factories;

use watoki\reflect\Type;
use watoki\stores\SerializerFactory;

class StaticSerializerFactory implements SerializerFactory {

    /** @var array|\pomf\persistence\Serializer[] */
    private $serializers = array();

    /**
     * @param array|\watoki\stores\Serializer[] $serializers indexed by the class they apply to
     */
    function __construct($serializers = array()) {
        $this->serializers = $serializers;
    }

    /**
     * @param \watoki\reflect\Type $type
     * @return boolean
     */
    public function appliesTo(Type $type) {
        return array_key_exists(get_class($type), $this->serializers);
    }

    /**
     * @param \watoki\reflect\Type $type
     * @return \watoki\stores\Serializer
     */
    public function createSerializer(Type $type) {
        return $this->serializers[get_class($type)];
    }
}