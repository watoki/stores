<?php
namespace watoki\stores\adapter;

use watoki\stores\file\raw\RawFileStore;
use watoki\stores\Store;

class FileStoreAdapter extends RawFileStore {

    /** @var Store */
    private $store;

    private $root;

    /**
     * @param Store $store
     * @param null $root
     */
    public function __construct(Store $store, $root = null) {
        $this->store = $store;
        $this->root = $root ? trim($root, '/') . '/' : '';
    }

    public function read($id) {
        return $this->store->read($this->root . $id);
    }

    public function create($entity, $id = null) {
        $this->store->create($entity, $this->root . $id);
    }

    public function update($entity) {
        $this->store->update($entity);
    }

    public function delete($entity) {
        $this->store->delete($entity);
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