<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\SqliteSerializer;

class NullableSerializer implements SqliteSerializer {

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