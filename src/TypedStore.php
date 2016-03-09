<?php
namespace watoki\stores;

use watoki\reflect\Type;

interface TypedStore {

    /**
     * @param Type $type
     * @param mixed $data
     * @param null|mixed $key
     * @return mixed The key
     */
    public function writeTyped(Type $type, $data, $key = null);

    /**
     * @param Type $type
     * @param mixed $key
     * @return mixed Data of type $type
     */
    public function readTyped(Type $type, $key);
}