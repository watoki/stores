<?php
namespace watoki\stores\file\raw;

use watoki\stores\file\FileStore;

class RawFileStore extends FileStore {

    public static $CLASS = __CLASS__;

    /**
     * @param string $rootDirectory
     */
    public function __construct($rootDirectory) {
        parent::__construct(new FileSerializer(), $rootDirectory);
    }

    /**
     * @param string $id
     * @return File
     */
    public function read($id) {
        return parent::read($id);
    }

} 