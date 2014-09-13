<?php
namespace watoki\stores\file;

class SerializerRepository extends \watoki\stores\SerializerRepository {

    protected function setDefaultSerializers() {
        $this->setSerializer(self::TYPE_NULL, new GenericSerializer());
        $this->setSerializer(self::TYPE_INTEGER, new GenericSerializer());
        $this->setSerializer(self::TYPE_FLOAT, new GenericSerializer());
        $this->setSerializer(self::TYPE_BOOLEAN, new GenericSerializer());
        $this->setSerializer(self::TYPE_STRING, new GenericSerializer());
        $this->setSerializer(self::TYPE_ARRAY, new GenericSerializer());
        $this->setSerializer('DateTime', new DateTimeSerializer());
    }

} 