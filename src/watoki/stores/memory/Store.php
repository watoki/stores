<?php
namespace watoki\stores\memory;

abstract class Store extends \watoki\stores\Store {

    private $memory = array();

    private $currentId = 0;

    public function read($id) {
        try {
            return $this->memory[$id];
        } catch (\Exception $e) {
            throw new \Exception("Entity with ID [$id] does not exist.");
        }
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

    protected function createEntitySerializer() {
        return new Serializer();
    }
}