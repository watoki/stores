<?php
namespace watoki\stores\sqlite;

use watoki\stores\sqlite\serializers\ArraySerializer;
use watoki\stores\sqlite\serializers\BooleanSerializer;
use watoki\stores\sqlite\serializers\DateTimeSerializer;
use watoki\stores\sqlite\serializers\FloatSerializer;
use watoki\stores\sqlite\serializers\IntegerSerializer;
use watoki\stores\sqlite\serializers\NullSerializer;
use watoki\stores\sqlite\serializers\StringSerializer;

class SerializerRepository extends \watoki\stores\SerializerRepository {

    /**
     * @param $type
     * @return Serializer
     */
    public function getSerializer($type) {
        return parent::getSerializer($type);
    }

    protected function setDefaultSerializers() {
        $this->setSerializer(self::TYPE_NULL, new NullSerializer());
        $this->setSerializer(self::TYPE_INTEGER, new IntegerSerializer());
        $this->setSerializer(self::TYPE_FLOAT, new FloatSerializer());
        $this->setSerializer(self::TYPE_BOOLEAN, new BooleanSerializer());
        $this->setSerializer(self::TYPE_STRING, new StringSerializer());
        $this->setSerializer(self::TYPE_ARRAY, new ArraySerializer());
        $this->setSerializer('DateTime', new DateTimeSerializer());
    }

} 