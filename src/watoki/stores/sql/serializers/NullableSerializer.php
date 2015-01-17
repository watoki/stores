<?php
namespace watoki\stores\sql\serializers;

use watoki\stores\sql\SqlSerializer;

class NullableSerializer implements SqlSerializer {

    /** @var ColumnSerializer */
    private $serializer;

    function __construct(ColumnSerializer $serializer) {
        $this->serializer = $serializer;
    }

    public function serialize($inflated) {
        return $inflated ? $this->serializer->serialize($inflated) : null;
    }

    public function inflate($serialized) {
        return $serialized ? $this->serializer->inflate($serialized) : null;
    }

    public function getDefinition() {
        $this->serializer->setNullable(true);
        return $this->serializer->getDefinition();
    }
}