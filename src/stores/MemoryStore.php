<?php
namespace watoki\stores\stores;

use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keys\KeyGenerator;
use watoki\stores\Store;

class MemoryStore implements Store {

    private $data = [];

    /** @var KeyGenerator */
    private $key;

    /**
     * @param KeyGenerator $keyGenerator
     */
    public function __construct(KeyGenerator $keyGenerator) {
        $this->key = $keyGenerator;
    }

    /**
     * @param mixed $data Data to be stored
     * @param null|mixed $key Key under which to store the data, is generated if omitted
     * @return string The key
     * @throws \Exception
     */
    public function write($data, $key = null) {
        if (!$key) {
            $key = $this->key->generate();
        }
        if (!is_string($key)) {
            throw new \Exception('Memory keys must be strings.');
        }

        $this->data[$key] = $data;
    }

    /**
     * @param mixed $key
     * @return mixed The data
     * @throws NotFoundException If no data is stored under this key
     */
    public function read($key) {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }

        return $this->data[$key];
    }

    /**
     * @param mixed $key The key which to remove from the store
     * @return void
     * @throws NotFoundException If no data is stored under this key
     */
    public function remove($key) {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }

        unset($this->data[$key]);
    }

    /**
     * @param mixed $key
     * @return boolean True if the key exists, false otherwise
     */
    public function has($key) {
        return array_key_exists($key, $this->data);
    }

    /**
     * @return mixed[] All keys that are currently stored
     */
    public function keys() {
        return array_keys($this->data);
    }
}