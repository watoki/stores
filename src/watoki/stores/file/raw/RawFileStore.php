<?php
namespace watoki\stores\file\raw;

use watoki\stores\file\FileStore;
use watoki\stores\file\SerializerRepository;

class RawFileStore extends FileStore {

    public static $CLASS = __CLASS__;

    public function __construct(SerializerRepository $serializers, $rootDirectory) {
        parent::__construct(File::$CLASS, $serializers, $rootDirectory);
    }

    protected function createEntitySerializer() {
        return new Serializer();
    }

    /**
     * @param string $id
     * @return File
     */
    public function read($id) {
        return parent::read($id);
    }

} 