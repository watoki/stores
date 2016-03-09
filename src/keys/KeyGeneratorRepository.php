<?php
namespace watoki\stores\keys;

class KeyGeneratorRepository {

    private static $default;

    /**
     * @param KeyGenerator $generator
     * @return void
     */
    public static function setDefault(KeyGenerator $generator) {
        self::$default = $generator;
    }

    /**
     * @return KeyGenerator
     */
    public static function getDefault() {
        return self::$default ?: new UniqKeyGenerator();
    }
}