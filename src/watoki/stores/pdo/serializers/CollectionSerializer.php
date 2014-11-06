<?php
namespace watoki\stores\pdo\serializers;

use watoki\collections\Collection;
use watoki\stores\ObjectSerializer as BaseObjectSerializer;

class CollectionSerializer extends BaseObjectSerializer {

    /**
     * @param Collection $inflated
     * @return string
     */
    public function serialize($inflated) {
        return json_encode(parent::serialize($inflated));
    }

    public function inflate($serialized) {
        return parent::inflate(json_decode($serialized, true));
    }

    public function getDefinition() {
        return 'TEXT';
    }
}