<?php
namespace watoki\stores\stores;

use watoki\stores\keys\KeyGenerator;
use watoki\stores\serializing\Serializer;
use watoki\stores\serializing\SerializerFactory;

class FileStore extends FlatFileStore {

    /** @var Serializer */
    private $serializer;

    /**
     * @param string $basePath
     * @param null|KeyGenerator $keyGenerator
     * @param null|Serializer $serializer
     */
    public function __construct($basePath, KeyGenerator $keyGenerator, Serializer $serializer = null) {
        parent::__construct($basePath, $keyGenerator);
        $this->serializer = $serializer ?: SerializerFactory::getDefault();
    }

    public function write($data, $key = null) {
        parent::write($this->serializer->serialize($data), $key);
    }

    public function read($key) {
        return $this->serializer->inflate(parent::read($key));
    }
}