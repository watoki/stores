<?php
namespace watoki\stores\pdo;

use watoki\stores\pdo\serializers\BooleanSerializer;
use watoki\stores\pdo\serializers\DateTimeSerializer;
use watoki\stores\pdo\serializers\FloatSerializer;
use watoki\stores\pdo\serializers\IntegerSerializer;
use watoki\stores\pdo\serializers\NullSerializer;
use watoki\stores\pdo\serializers\StringSerializer;

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
        $this->setSerializer(get_class(new \DateTime()), new DateTimeSerializer());
    }

} 