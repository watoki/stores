<?php
namespace watoki\stores\memory;

class SerializerRepository extends \watoki\stores\SerializerRepository {

    protected function setDefaultSerializers() {
        $this->setSerializer(self::TYPE_NULL, new Serializer());
        $this->setSerializer(self::TYPE_INTEGER, new Serializer());
        $this->setSerializer(self::TYPE_FLOAT, new Serializer());
        $this->setSerializer(self::TYPE_BOOLEAN, new Serializer());
        $this->setSerializer(self::TYPE_STRING, new Serializer());
        $this->setSerializer('DateTime', new Serializer());
    }

} 