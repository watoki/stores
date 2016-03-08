<?php
namespace watoki\stores\serializing;

class JsonSerializer implements Serializer {

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value) {
        return json_encode($value);
    }

    /**
     * @param string $string
     * @return mixed
     */
    public function deserialize($string) {
        return json_decode($string, true);
    }
}