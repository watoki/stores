<?php
namespace watoki\stores\keys;

class UniqKeyGenerator implements KeyGenerator {

    /**
     * @return string
     */
    public function generate() {
        return uniqid('', true);
    }
}