<?php
namespace watoki\stores;

use watoki\collections\Set;

abstract class Store {

    /** @var SerializerRepository */
    private $serializers;

    /** @var string */
    private $entityClass;

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

    protected function serialize($entity) {
        return $this->serializers->getSerializer($this->getEntityClass())->serialize($entity);
    }

    protected function inflate($row) {
        return $this->serializers->getSerializer($this->getEntityClass())->inflate($row);
    }

    protected function inflateAll($rows, $collection = null) {
        $entities = $collection ? : new Set();
        foreach ($rows as $row) {
            $entities[] = $this->inflate($row);
        }
        return $entities;
    }

} 