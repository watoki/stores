<?php
namespace watoki\stores\serializing\serializers;

use watoki\stores\serializing\Serializer;

class JsonSerializer implements Serializer {

    /** @var bool */
    private $prettyPrint = false;

    /**
     * @param bool $to
     * @return $this
     */
    public function setPrettyPrint($to = true) {
        $this->prettyPrint = $to;
        return $this;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value) {
        return json_encode($this->encode($value),
            $this->prettyPrint ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * @param string $string
     * @return mixed
     */
    public function inflate($string) {
        return $this->decode(json_decode($string, true)) ?: $string;
    }

    private function encode($value) {
        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = $this->encode($item);
            }
            return $value;

        } else if (is_string($value)) {
            return utf8_encode($value);

        } else {
            return $value;
        }
    }

    private function decode($value) {
        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = $this->decode($item);
            }
            return $value;

        } else if (is_string($value)) {
            return utf8_decode($value);

        } else {
            return $value;
        }
    }
}