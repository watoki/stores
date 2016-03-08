<?php
namespace watoki\stores;

use watoki\stores\exceptions\NotFoundException;

interface Store {

    /**
     * @param mixed $data Data to be stored
     * @param null|mixed $key Key under which to store the data, is generated if omitted
     * @return mixed The key
     */
    public function write($data, $key = null);

    /**
     * @param mixed $key
     * @return mixed The data
     * @throws NotFoundException If no data is stored under this key
     */
    public function read($key);

    /**
     * @param mixed $key The key which to remove from the store
     * @return void
     * @throws NotFoundException If no data is stored under this key
     */
    public function remove($key);

    /**
     * @param mixed $key
     * @return boolean True if the key exists, false otherwise
     */
    public function has($key);

    /**
     * @return mixed[] All keys that are currently stored
     */
    public function keys();
}