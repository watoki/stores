<?php
namespace watoki\stores\pdo;

use watoki\collections\Liste;
use watoki\collections\Map;
use watoki\collections\Set;
use watoki\stores\pdo\serializers\ArraySerializer;
use watoki\stores\pdo\serializers\BooleanSerializer;
use watoki\stores\pdo\serializers\CollectionSerializer;
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
        $this->setSerializer(self::TYPE_ARRAY, new ArraySerializer());
        $this->setSerializer('DateTime', new DateTimeSerializer());
        $this->setSerializer(Liste::$CLASSNAME, new CollectionSerializer(Liste::$CLASSNAME, $this));
        $this->setSerializer(Map::$CLASSNAME, new CollectionSerializer(Map::$CLASSNAME, $this));
        $this->setSerializer(Set::$CLASSNAME, new CollectionSerializer(Set::$CLASSNAME, $this));
    }

} 