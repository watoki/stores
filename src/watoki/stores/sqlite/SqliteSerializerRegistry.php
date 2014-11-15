<?php
namespace watoki\stores\sqlite;

use watoki\stores\SerializerRegistry;
use watoki\stores\sqlite\serializers\BooleanSerializer;
use watoki\stores\sqlite\serializers\DateTimeSerializer;
use watoki\stores\sqlite\serializers\FloatSerializer;
use watoki\stores\sqlite\serializers\IntegerSerializer;
use watoki\stores\sqlite\serializers\NullSerializer;
use watoki\stores\sqlite\serializers\StringSerializer;

class SqliteSerializerRegistry extends SerializerRegistry {

    /**
     * @param $type
     * @return SqliteSerializer
     */
    public function getSerializer($type) {
        return parent::getSerializer($type);
    }

    protected function registerDefaultSerializers() {
        $this->register(self::TYPE_NULL, new NullSerializer());
        $this->register(self::TYPE_INTEGER, new IntegerSerializer());
        $this->register(self::TYPE_FLOAT, new FloatSerializer());
        $this->register(self::TYPE_BOOLEAN, new BooleanSerializer());
        $this->register(self::TYPE_STRING, new StringSerializer());
        $this->register('DateTime', new DateTimeSerializer());
    }

} 