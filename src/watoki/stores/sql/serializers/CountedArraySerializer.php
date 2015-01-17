<?php
namespace watoki\stores\sql\serializers;

use watoki\stores\sql\SqlSerializer;

class CountedArraySerializer implements SqlSerializer {

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
