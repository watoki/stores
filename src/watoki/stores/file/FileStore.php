<?php
namespace watoki\stores\file;

use watoki\stores\Store;

class FileStore extends Store {

    public static $CLASS = __CLASS__;

    private $root;

    public function __construct($entityClass, SerializerRepository $serializers, $rootDirectory) {
        parent::__construct($entityClass, $serializers);
        $this->root = trim($rootDirectory, '\\/');
    }

    public function read($id) {
        $entity = $this->inflate(file_get_contents($this->getFile($id)));
        $entity->id = $id;
        return $entity;
    }

    public function create($entity, $id = null) {
        $entity->id = is_null($id) ? uniqid() : $id;
        $file = $this->getFile($entity->id);
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $this->serialize($entity));
    }

    public function update($entity) {
        $this->create($entity, $entity->id);
    }

    public function delete($entity) {
        unlink($this->getFile($entity->id));
    }

    public function keys() {
        $files = $this->files($this->root);
        sort($files);
        return $files;
    }

    private function files($in) {
        $files = array();
        foreach (glob($in . '/*') as $file) {
            if (is_dir($file)) {
                $files = array_merge($files, $this->files($file));
            } else {
                $files[] = substr($file, strlen($this->root) + 1);
            }
        }
        return $files;
    }

    public function exists($id) {
        return file_exists($this->getFile($id));
    }

    protected function createEntitySerializer() {
        return new ObjectSerializer($this->getEntityClass(), $this->getSerializers());
    }

    private function getFile($id) {
        return $this->root . DIRECTORY_SEPARATOR . $id;
    }
}