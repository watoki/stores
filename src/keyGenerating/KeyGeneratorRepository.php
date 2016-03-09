<?php
namespace watoki\stores\keyGenerating;

use watoki\stores\keyGenerating\keyGenerators\UniqKeyGenerator;

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