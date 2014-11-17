<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\SqliteSerializer;

class CountedArraySerializer implements SqliteSerializer {

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
        return array(
            'items' => json_encode($serialized),
            'count' => count($serialized)
        );
    }

    public function inflate($serialized) {
        $inflated = array();
        foreach (json_decode($serialized['items'], true) as $key => $value) {
            $inflated[$key] = $this->itemSerializer->inflate($value);
        }
        return $inflated;
    }

    public function getDefinition() {
        return array('items' => 'TEXT', 'count' => 'INTEGER');
    }
}
