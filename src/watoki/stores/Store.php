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
        $serializers->setSerializer($entityClass, $this->createEntitySerializer());
    }

    abstract public function read($id);

    abstract public function create($entity, $id = null);

    abstract public function update($entity);

    abstract public function delete($entity);

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