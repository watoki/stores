<?php
namespace watoki\stores\sql\serializers;

use watoki\stores\sql\SqlSerializer;

class ArraySerializer extends ColumnSerializer {

    /** @var SqlSerializer */
    private $itemSerializer;

    /**
     * @param SqlSerializer $itemSerializer
     */
    public function __construct(SqlSerializer $itemSerializer) {
        $this->itemSerializer = $itemSerializer;
    }

    /**
     * @param array $inflated
     * @return string
     */
    public function serialize($inflated) {
        $serialized = array();
        foreach ($inflated as $key => $value) {
            $serialized[$key] = $this->itemSerializer->serialize($value);
        }
        return json_encode($serialized);
    }

    public function inflate($serialized) {
        $inflated = array();
        foreach (json_decode($serialized, true) as $key => $value) {
            $inflated[$key] = $this->itemSerializer->inflate($value);
        }
        return $inflated;
    }

    protected function getColumnDefinition() {
        return 'TEXT';
    }
}
