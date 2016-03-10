<?php
namespace watoki\stores\stores;

use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\keyGenerating\KeyGeneratorRepository;
use watoki\stores\Store;

class MemoryStore implements Store {

    private $data = [];

    /** @var KeyGenerator */
    private $key;

    /**
     * @param null|KeyGenerator $keyGenerator
     */
    public function __construct(KeyGenerator $keyGenerator = null) {
        $this->key = $keyGenerator ?: KeyGeneratorRepository::getDefault();
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
        if (!$this->isStringy($key)) {
            throw new \Exception('Memory keys must be strings.');
        }
        $key = (string)$key;

        $this->data[$key] = $data;

        return $key;
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
        return array_key_exists((string)$key, $this->data);
    }

    /**
     * @return mixed[] All keys that are currently stored
     */
    public function keys() {
        return array_keys($this->data);
    }

    private function isStringy($var) {
        return is_string($var) || is_int($var) || is_float($var) || is_double($var) || is_object($var) && method_exists($var, '__toString');
    }
}