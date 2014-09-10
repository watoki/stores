<?php
namespace watoki\stores\adapter;

use watoki\stores\file\FileStore;
use watoki\stores\file\SerializerRepository;
use watoki\stores\Store;

class FileStoreAdapter extends FileStore {

    /** @var Store */
    private $store;

    public function __construct(Store $store) {
        parent::__construct($store->getEntityClass(), new SerializerRepository(), null);
        $this->store = $store;
    }

    public function read($id) {
        return $this->store->read($id);
    }

    public function create($entity, $id = null) {
        return $this->store->create($entity, $id);
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

    public function find($pattern) {
        $pattern = str_replace(array('*', '?'), array('.*', '.'), $pattern);

        $matches = array();
        foreach ($this->keys() as $key) {
            if (preg_match('?' . $pattern . '?', $key)) {
                $matches[] = $key;
            }
        }
        return $matches;
    }

    public function exists($id) {
        try {
            $this->store->read($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

} 