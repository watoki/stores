<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\SqliteSerializer;

class ArraySerializer extends ColumnSerializer {

    /** @var SqliteSerializer */
    private $itemSerializer;

    /**
     * @param SqliteSerializer $itemSerializer
     */
    public function __construct(SqliteSerializer $itemSerializer) {
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
