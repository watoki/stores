<?php
namespace watoki\stores;

abstract class GeneralStore extends Store {

    /** @var SerializerRegistry */
    private $serializers;

    /** @var string */
    private $entityClass;

    /**
     * @param string $entityClass
     * @param SerializerRegistry $serializers <-
     */
    public function __construct($entityClass, SerializerRegistry $serializers) {
        $this->entityClass = $entityClass;
        $this->serializers = $serializers;
    }

    /**
     * @param object $entity
     * @return mixed
     * @throws \Exception
     */
    protected function serialize($entity) {
        return $this->serializers->getSerializer($this->getEntityType())->serialize($entity);
    }

    /**
     * @param mixed $row
     * @return object
     * @throws \Exception
     */
    protected function inflate($row) {
        return $this->serializers->getSerializer($this->getEntityType())->inflate($row);
    }

    /**
     * @return string
     */
    protected function getEntityType() {
        return $this->entityClass;
    }

    /**
     * @return SerializerRegistry
     */
    protected function getSerializers() {
        return $this->serializers;
    }

} 