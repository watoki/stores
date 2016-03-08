<?php
namespace watoki\stores\keys;

class KeyGeneratorFactory {

    /**
     * @return KeyGenerator
     */
    public static function getDefault() {
        return new UniqKeyGenerator();
    }
}