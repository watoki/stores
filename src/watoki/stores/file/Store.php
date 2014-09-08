<?php
namespace watoki\stores\file;

use watoki\stores\SerializerRepository;

abstract class Store extends \watoki\stores\Store {

    private $root;

    public function __construct(SerializerRepository $serializers, $rootDirectory) {
        parent::__construct($serializers);
        $this->root = $rootDirectory;
    }

    public function read($id) {
        // TODO: Implement read() method.
    }

    public function create($entity) {
        file_put_contents($this->getFile($entity->id), $this->serialize($entity));
    }

    public function createAt($id, $entity) {
        $entity->id = $id;
        $this->create($entity);
    }

    public function update($entity) {
        // TODO: Implement update() method.
    }

    public function delete($entity) {
        // TODO: Implement delete() method.
    }

    protected function createEntitySerializer() {
        return new ObjectSerializer($this->getEntityClass(), $this->getSerializers());
    }

    private function getFile($id) {
        return $this->root . DIRECTORY_SEPARATOR . $id;
    }
}