<?php
namespace watoki\stores\file;

use watoki\reflect\type\ClassType;
use watoki\reflect\type\PrimitiveType;
use watoki\reflect\type\StringType;
use watoki\reflect\Type;
use watoki\stores\common\DateTimeSerializer;
use watoki\stores\common\NoneSerializer;
use watoki\stores\exception\EntityNotFoundException;
use watoki\stores\GeneralStore;
use watoki\stores\Serializer;
use watoki\stores\SerializerRegistry;

class FileStore extends GeneralStore {

    public static $CLASS = __CLASS__;

    private $root;

    /**
     * @param Serializer $serializer
     * @param $rootDirectory
     */
    public function __construct(Serializer $serializer, $rootDirectory) {
        parent::__construct($serializer);
        $this->root = rtrim($rootDirectory, '\\/');
    }

    public static function registerDefaultSerializers(SerializerRegistry $registry) {
        $registry->register(new ClassType('DateTime'), new DateTimeSerializer());
        $registry->getFallBacks()->append(function (Type $type) {
            if ($type instanceof PrimitiveType) {
                return new NoneSerializer();
            }
            return null;
        });
    }

    protected function _read($id) {
        if (!file_exists($this->fileName($id))) {
            throw new EntityNotFoundException("File [$id] does not exist.");
        }
        return $this->inflate(file_get_contents($this->fileName($id)), $id);
    }

    protected function _create($entity, $id) {
        $file = $this->fileName($id);
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $this->serialize($entity, $id));
    }

    protected function _update($entity) {
        $this->_create($entity, $this->getKey($entity));
    }

    protected function _delete($key) {
        unlink($this->fileName($key));
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

    public function hasKey($id) {
        return file_exists($this->fileName($id));
    }

    public function exists($id) {
        return $this->hasKey($id);
    }

    private function fileName($id) {
        return $this->root . DIRECTORY_SEPARATOR . $id;
    }
}