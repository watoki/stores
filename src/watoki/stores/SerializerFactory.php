<?php
namespace watoki\stores;

use watoki\reflect\Type;

interface SerializerFactory {

    /**
     * @param Type $type
     * @return boolean
     */
    public function appliesTo(Type $type);

    /**
     * @param Type $type
     * @return Serializer
     */
    public function createSerializer(Type $type);

} 