<?php
namespace watoki\stores\sqlite;

use watoki\stores\Serializer;

interface SqliteSerializer extends Serializer {

    /**
     * @return string|array|string[] If array (indexed by column name), serialize() must return an array with same keys.
     */
    public function getDefinition();

} 