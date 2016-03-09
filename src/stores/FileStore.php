<?php
namespace watoki\stores\stores;

use watoki\reflect\Type;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\serializing\Serializer;
use watoki\stores\transforming\TransformerRegistry;

class FileStore extends SerializedStore {

    /** @var FlatFileStore */
    private $file;

    /**
     * @param string $basePath
     * @param null|KeyGenerator $keyGenerator
     * @param null|TransformerRegistry $transformers
     * @param null|Serializer $serializer
     */
    public function __construct($basePath, KeyGenerator $keyGenerator = null, TransformerRegistry $transformers = null, Serializer $serializer = null) {
        parent::__construct($transformers, $serializer);
        $this->file = new FlatFileStore($basePath, $keyGenerator);
    }

    /**
     * @param string $serialized
     * @param mixed $key
     * @return void
     */
    protected function writeSerialized($serialized, $key) {
        $this->file->write($serialized, $key);
    }

    /**
     * @param mixed $key
     * @return string
     */
    protected function readSerialized($key) {
        return $this->file->read($key);
    }

    /**
     * @param mixed $key The key which to remove from the store
     * @return void
     * @throws NotFoundException If no data is stored under this key
     */
    public function remove($key) {
        $this->file->remove($key);
    }

    /**
     * @param mixed $key
     * @return boolean True if the key exists, false otherwise
     */
    public function has($key) {
        return $this->file->has($key);
    }

    /**
     * @return mixed[] All keys that are currently stored
     */
    public function keys() {
        return $this->file->keys();
    }
}