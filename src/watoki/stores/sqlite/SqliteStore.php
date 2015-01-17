<?php
namespace watoki\stores\sqlite;

use watoki\stores\common\factories\ClassSerializerFactory;
use watoki\stores\SerializerRegistry;
use watoki\stores\sql\SqlStore;
use watoki\stores\sqlite\serializers\DateTimeImmutableSerializer;
use watoki\stores\sqlite\serializers\DateTimeSerializer;

class SqliteStore extends SqlStore {

    public static function registerDefaultSerializers(SerializerRegistry $registry) {

        $registry->add(new ClassSerializerFactory('DateTime', new DateTimeSerializer()));
        $registry->add(new ClassSerializerFactory('DateTimeImmutable', new DateTimeImmutableSerializer()));

        return parent::registerDefaultSerializers($registry);
    }

    /**
     * @return string
     */
    protected function primaryKeyDefinition() {
        return '"id" INTEGER PRIMARY KEY AUTOINCREMENT';
    }

    /**
     * @param string $key
     * @return string
     */
    protected function quote($key) {
        return '"' . $key . '"';
    }

} 
