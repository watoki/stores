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
        $entity = $this->inflate(file_get_contents($this->getFile($id)));
        $entity->id = $id;
        return $entity;
    }

    public function create($entity) {
        file_put_contents($this->getFile($entity->id), $this->serialize($entity));
    }

    public function createAt($id, $entity) {
        $entity->id = $id;
        $this->create($entity);
    }

    public function update($entity) {
        $this->create($entity);
    }

    public function delete($entity) {
        unlink($this->getFile($entity->id));
    }

    protected function createEntitySerializer() {
        return new ObjectSerializer($this->getEntityClass(), $this->getSerializers());
    }

    private function getFile($id) {
        return $this->root . DIRECTORY_SEPARATOR . $id;
    }
}