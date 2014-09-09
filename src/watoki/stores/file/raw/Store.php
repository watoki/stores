<?php
namespace watoki\stores\file\raw;

use watoki\stores\SerializerRepository;

class Store extends \watoki\stores\file\FileStore {

    public function __construct(SerializerRepository $serializers, $rootDirectory) {
        parent::__construct(File::$CLASS, $serializers, $rootDirectory);
    }

    protected function createEntitySerializer() {
        return new Serializer();
    }

} 