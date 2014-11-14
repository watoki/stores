<?php
namespace watoki\stores;

abstract class Store {

    /** @var array|object[] entities indexed by key */
    private $entities = array();

    /** @var array|mixed[] keys indexed by spl hash of entites */
    private $keys = array();

    public function __construct() {
    }

    /**
     * @param object $entity
     * @return mixed
     * @throws \Exception
     */
    abstract protected function serialize($entity);

    /**
     * @param mixed $row
     * @return object
     * @throws \Exception
     */
    abstract protected function inflate($row);

    /**
     * @param object $entity
     * @param null|string|int $key If omitted, a key will be generated
     * @return void
     */
    public function create($entity, $key = null) {
        if (!$key) {
            $key = $this->generateKey($entity);
        }
        $this->setKey($entity, $key);
        $this->_create($entity, $key);
    }

    /**
     * @param object $entity
     * @param string|int $key
     * @return void
     */
    abstract protected function _create($entity, $key);

    /**
     * @param string|int $key
     * @return object Entity belonging given key
     * @throw EntityNotFoundException If no entity with given key exists
     */
    public function read($key) {
        if (array_key_exists($key, $this->entities)) {
            return $this->entities[$key];
        }
        $entity = $this->_read($key);
        $this->setKey($entity, $key);
        return $entity;
    }

    /**
     * @param string|int $key
     * @return object Entity belonging given key
     * @throw EntityNotFoundException If no entity with given key exists
     */
    abstract protected function _read($key);

    /**
     * @param object $entity
     * @return void
     */
    public function update($entity) {
        $this->_update($entity);
    }

    /**
     * @param object $entity
     * @return void
     */
    abstract protected function _update($entity);

    /**
     * @param string|int $key
     * @return void
     */
    public function delete($key) {
        $this->removeKey($key);
        $this->_delete($key);
    }

    /**
     * @param string|int $key
     * @return void
     */
    abstract protected function _delete($key);

    /**
     * @return array|string[]|int[] All stored keys
     */
    abstract public function keys();

    /**
     * @param string|int $key
     * @return bool
     */
    public function hasKey($key) {
        return in_array($key, $this->keys());
    }

    /**
     * @param object $entity
     * @throws \Exception If the entity has never been saved nor read by this store
     * @return string|int the key of the given entity
     */
    public function getKey($entity) {
        $hash = spl_object_hash($entity);
        if (!array_key_exists($hash, $this->keys)) {
            throw new \Exception("Entity unknown to Store");
        }
        return $this->keys[$hash];
    }

    /**
     * @param object $entity
     * @return string
     */
    protected function generateKey($entity) {
        return uniqid(spl_object_hash($entity), true);
    }

    protected function setKey($entity, $key) {
        $this->keys[spl_object_hash($entity)] = $key;
        $this->entities[$key] = $entity;
    }

    protected function removeKey($key) {
        unset($this->keys[spl_object_hash($this->entities[$key])]);
        unset($this->entities[$key]);
    }

} 