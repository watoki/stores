<?php
namespace watoki\stores\memory;

use watoki\stores\Store;

class MemoryStore extends Store {

    private $memory = array();

    private $currentId = 0;

    public function __construct($entityClass, SerializerRepository $serializers) {
        parent::__construct($entityClass, $serializers);
    }

    public function read($id) {
        if (!isset($this->memory[$id])) {
            throw new \Exception("Entity with ID [$id] does not exist.");
        }
        return $this->memory[$id];
    }

    public function create($entity, $id = null) {
        if (is_null($id)) {
            $this->currentId += 1;
            $id = $this->currentId;
        }
        $entity->id = $id;
        $this->memory[$id] = $entity;
    }

    public function update($entity) {
        // Nothing to do
    }

    public function delete($entity) {
        unset($this->memory[$entity->id]);
    }

    /**
     * @return array|mixed[] All stored keys
     */
    public function keys() {
        return array_keys($this->memory);
    }

    protected function createEntitySerializer() {
        return new Serializer();
    }
}