<?php
namespace watoki\stores\file\serializers;

use watoki\stores\Serializer;

class ArraySerializer implements Serializer {

    /** @var Serializer */
    private $itemSerializer;

    public function __construct(Serializer $itemSerializer) {
        $this->itemSerializer = $itemSerializer;
    }

    public function serialize($inflated) {
        $array = array();
        foreach ($inflated as $key => $item) {
            $array[$key] = $this->itemSerializer->serialize($item);
        }
        return $array;
    }

    public function inflate($serialized) {
        $array = array();
        foreach ($serialized as $key => $item) {
            $array[$key] = $this->itemSerializer->inflate($item);
        }
        return $array;
    }
}