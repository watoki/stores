<?php
namespace watoki\stores\sqlite\serializers;

use watoki\reflect\type\IdentifierObjectType;

class IdentifierObjectSerializer extends ColumnSerializer {

    /** @var \watoki\reflect\type\IdentifierObjectType */
    private $type;

    function __construct(IdentifierObjectType $type) {
        $this->type = $type;
    }

    public function serialize($inflated) {
        return (string)$inflated;
    }

    public function inflate($serialized) {
        return $this->type->inflate($serialized);
    }

    protected function getColumnDefinition() {
        return 'TEXT';
    }
}