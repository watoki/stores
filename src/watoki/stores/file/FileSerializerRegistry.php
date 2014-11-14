<?php
namespace watoki\stores\file;

use watoki\stores\common\DateTimeSerializer;
use watoki\stores\common\NoneSerializer;

class FileSerializerRegistry extends \watoki\stores\SerializerRegistry {

    protected function registerDefaultSerializers() {
        $this->register(self::TYPE_NULL, new NoneSerializer());
        $this->register(self::TYPE_INTEGER, new NoneSerializer());
        $this->register(self::TYPE_FLOAT, new NoneSerializer());
        $this->register(self::TYPE_BOOLEAN, new NoneSerializer());
        $this->register(self::TYPE_STRING, new NoneSerializer());
        $this->register(self::TYPE_ARRAY, new NoneSerializer());
        $this->register('DateTime', new DateTimeSerializer());
    }

} 