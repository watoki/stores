<?php
namespace watoki\stores\serializing;

class SerializerFactory {

    /** @var Serializer */
    private static $default;

    /**
     * @param Serializer $default
     */
    public static function setDefault(Serializer $default) {
        self::$default = $default;
    }

    /**
     * @return Serializer
     */
    public static function getDefault() {
        return self::$default ?: new ObjectSerializer();
    }
}