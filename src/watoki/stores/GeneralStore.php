<?php
namespace watoki\stores;

abstract class GeneralStore extends Store {

    /** @var Serializer */
    private $serializer;

    public function __construct(Serializer $serializer) {
        $this->serializer = $serializer;
    }

    /**
     * @param object $entity
     * @return mixed
     * @throws \Exception
     */
    protected function serialize($entity) {
        return $this->serializer->serialize($entity);
    }

    /**
     * @param mixed $row
     * @return object
     * @throws \Exception
     */
    protected function inflate($row) {
        return $this->serializer->inflate($row);
    }

} 