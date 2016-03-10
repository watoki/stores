<?php
namespace watoki\stores\stores;

use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\keyGenerating\KeyGeneratorRepository;
use watoki\stores\Store;

class FlatFileStore implements Store {

    /** @var string */
    private $basePath;

    /** @var KeyGenerator */
    private $key;

    /**
     * @param string $basePath
     * @param null|KeyGenerator $keyGenerator
     */
    public function __construct($basePath, KeyGenerator $keyGenerator = null) {
        $this->basePath = $basePath;
        $this->key = $keyGenerator ?: KeyGeneratorRepository::getDefault();
    }

    /**
     * @param string $data Data to be stored
     * @param null|string $key Key under which to store the data, is generated if omitted
     * @return string The key
     * @throws \Exception If data or key are not strings
     */
    public function write($data, $key = null) {
        if (!$this->isStringy($data)) {
            throw new \Exception('Only strings can be stored in flat files.');
        }

        if (!$key) {
            $key = $this->key->generate();
        }
        if (!$this->isStringy($key)) {
            throw new \Exception('Keys of flat files must be strings.');
        }

        $path = $this->path($key);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, (string)$data);

        return $key;
    }

    /**
     * @param string $key
     * @return string The data
     * @throws NotFoundException If no data is stored under this key
     */
    public function read($key) {
        if (!$this->has($key)) {
            throw new NotFoundException($key);
        }
        return file_get_contents($this->path($key));
    }

    /**
     * @param string $key The key which to remove from the store
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
     * @param string $key
     * @return boolean True if the key exists, false otherwise
     */
    public function has($key) {
        $path = $this->path($key);
        return file_exists($path) && is_file($path);
    }

    /**
     * @return string[] All keys that are currently stored without order
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
                $files[] = str_replace('\\', '/', substr($file, strlen($this->basePath) + 1));
            } else {
                $files = array_merge($files, $this->filesIn($file));
            }
        }
        return $files;
    }

    private function isStringy($var) {
        return is_string($var) || is_int($var) || is_float($var) || is_double($var) || is_object($var) && method_exists($var, '__toString');
    }
}