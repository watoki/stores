<?php
namespace watoki\stores\sql;

use watoki\stores\Serializer;

interface SqlSerializer extends Serializer {

    /**
     * @return string|array|string[] If array (indexed by column name), serialize() must return an array with same keys.
     */
    public function getDefinition();

} 