<?php
namespace watoki\stores\adapter;

use watoki\stores\file\FileStore;
use watoki\stores\file\SerializerRepository;
use watoki\stores\Store;

class FileStoreAdapter extends FileStore {

    /** @var Store */
    private $store;

    private $root;

    public function __construct(Store $store, $root = null) {
        parent::__construct($store->getEntityClass(), new SerializerRepository(), null);
        $this->store = $store;
        $this->root = $root ? trim($root, '/') . '/' : '';
    }

    public function read($id) {
        return $this->store->read($this->root . $id);
    }

    public function create($entity, $id = null) {
        return $this->store->create($entity, $this->root . $id);
    }

    public function update($entity) {
        return $this->store->update($entity);
    }

    public function delete($entity) {
        return $this->store->delete($entity);
    }

    public function keys() {
        return $this->store->keys();
    }

    public function exists($id) {
        try {
            $this->store->read($this->root . $id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

} 