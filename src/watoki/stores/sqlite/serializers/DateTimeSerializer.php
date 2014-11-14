<?php
namespace watoki\stores\sqlite\serializers;

class DateTimeSerializer extends ColumnSerializer {

    /** @var \watoki\stores\common\DateTimeSerializer */
    private $serializer;

    function __construct($nullable = false) {
        parent::__construct($nullable);
        $this->serializer = new \watoki\stores\common\DateTimeSerializer();
    }

    public function serialize($inflated) {
        return $this->serializer->serialize($inflated);
    }

    public function inflate($serialized) {
        return $this->serializer->inflate($serialized);
    }

    protected function getColumnDefinition() {
        return 'TEXT(32)';
    }
}