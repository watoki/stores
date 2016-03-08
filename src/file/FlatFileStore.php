<?php
namespace watoki\stores\file;

use watoki\stores\exceptions\NotFoundException;
use watoki\stores\Store;

class FlatFileStore implements Store {

    /** @var string */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    /**
     * @param mixed $data Data to be stored
     * @param null|mixed $key Key under which to store the data, is generated if omitted
     * @return string The key
     */
    public function write($data, $key = null) {
        $path = $this->path($key);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, $data);
    }

    /**
     * @param mixed $key
     * @return mixed The data
     * @throws NotFoundException If no data is stored under this key
     */
    public function read($key) {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }
        return file_get_contents($this->path($key));
    }

    /**
     * @param mixed $key The key which to remove from the store
     * @return void
     * @throws NotFoundException If no data is stored under this key
     */
    public function remove($key) {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }

        unlink($this->path($key));
    }

    /**
     * @param mixed $key
     * @return boolean True if the key exists, false otherwise
     */
    public function has($key) {
        $path = $this->path($key);
        return file_exists($path) && is_file($path);
    }

    /**
     * @return mixed[] All keys that are currently stored without order
     */
    public function keys() {
        return $this->filesIn($this->basePath);
    }

    private function path($key) {
        return $this->basePath . DIRECTORY_SEPARATOR . $key;
    }

    private function filesIn($path) {
        $files = [];
        foreach (glob($path . DIRECTORY_SEPARATOR . '*') as $file) {
            if (is_file($file)) {
                $files[] = substr($file, strlen($this->basePath) + 1);
            } else {
                $files = array_merge($files, $this->filesIn($file));
            }
        }
        return $files;
    }
}