<?php
namespace watoki\stores\file\raw;

use watoki\stores\file\FileStore;
use watoki\stores\file\FileSerializerRegistry;

class RawFileStore extends FileStore {

    public static $CLASS = __CLASS__;

    /**
     * @param FileSerializerRegistry $serializers <-
     * @param string $rootDirectory
     */
    public function __construct(FileSerializerRegistry $serializers, $rootDirectory) {
        parent::__construct(File::$CLASS, $serializers, $rootDirectory);
        $serializers->register(File::$CLASS, new FileSerializer());
    }

    /**
     * @param string $id
     * @return File
     */
    public function read($id) {
        return parent::read($id);
    }

} 