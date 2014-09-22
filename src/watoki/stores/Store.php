<?php
namespace watoki\stores;

abstract class Store {

    /** @var SerializerRepository */
    private $serializers;

    /** @var string */
    private $entityClass;

    /** @var array|object[] entities indexed by key */
    private $entities = array();

    /** @var array|mixed[] keys indexed by spl hash of entites */
    private $keys = array();

    public function __construct($entityClass, SerializerRepository $serializers) {
        $this->entityClass = $entityClass;
        $this->serializers = $serializers;
        $serializers->setSerializer($this->getEntityClass(), $this->createEntitySerializer());
    }

    /**
     * @param mixed $key
     * @return object Entity belonging given key
     * @throw \Exception If no entity with given key exists
     */
    abstract public function read($key);

    /**
     * @param object $entity
     * @param null|mixed $key If omitted, a key will be generated
     * @return null
     */
    abstract public function create($entity, $key = null);

    /**
     * @param object $entity
     * @return null
     */
    abstract public function update($entity);

    /**
     * @param object $entity
     * @return null
     */
    abstract public function delete($entity);

    /**
     * @return array|mixed[] All stored keys
     */
    abstract public function keys();

    /**
     * @return Serializer
     */
    abstract protected function createEntitySerializer();

    /**
     * @param object $entity
     * @throws \Exception If the entity has never been saved nor read by this store
     * @return mixed the key of the given entity
     */
    public function getKey($entity) {
        $hash = spl_object_hash($entity);
        if (!array_key_exists($hash, $this->keys)) {
            throw new \Exception("Entity unknown to Store");
        }
        return $this->keys[$hash];
    }

    protected function setKey($entity, $key) {
        if (!$key) {
            return;
        }
        $this->keys[spl_object_hash($entity)] = $key;
        $this->entities[$key] = $entity;
    }

    protected function removeKey($key) {
        unset($this->keys[spl_object_hash($this->entities[$key])]);
        unset($this->entities[$key]);
    }

    /**
     * @return string
     */
    protected function getEntityClass() {
        return $this->entityClass;
    }

    /**
     * @return SerializerRepository
     */
    protected function getSerializers() {
        return $this->serializers;
    }

    protected function serialize($entity, $key) {
        $this->setKey($entity, $key);
        return $this->serializers->getSerializer($this->getEntityClass())->serialize($entity);
    }

    protected function inflate($row, $key) {
        $inflated = $this->serializers->getSerializer($this->getEntityClass())->inflate($row);
        $this->setKey($inflated, $key);
        return $inflated;
    }

} 