<?php
namespace watoki\stores\file\raw;

use watoki\stores\exception\NotFoundException;
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

    protected function _read($id) {
        $file = $this->fileName($id);

        if (!file_exists($file)) {
            throw new NotFoundException("File [$id] does not exist.");
        }

        return $this->inflate($file);
    }

} 