<?php
namespace watoki\stores\file;

use watoki\stores\Store;

class FileStore extends Store {

    public static $CLASS = __CLASS__;

    private $root;

    public function __construct($entityClass, SerializerRepository $serializers, $rootDirectory) {
        parent::__construct($entityClass, $serializers);
        $this->root = rtrim($rootDirectory, '\\/');
    }

    public function read($id) {
        return $this->inflate(file_get_contents($this->getFile($id)), $id);
    }

    public function create($entity, $id = null) {
        $id = $id ? : uniqid();
        $file = $this->getFile($id);
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $this->serialize($entity, $id));
    }

    public function update($entity) {
        $this->create($entity, $this->getKey($entity));
    }

    public function delete($entity) {
        unlink($this->getFile($this->getKey($entity)));
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