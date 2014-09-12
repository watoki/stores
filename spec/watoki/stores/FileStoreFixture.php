<?php
namespace spec\watoki\stores;

use watoki\factory\Provider;
use watoki\scrut\Fixture;
use watoki\stores\adapter\FileStoreAdapter;
use watoki\stores\file\FileStore;
use watoki\stores\file\raw\File;
use watoki\stores\file\raw\RawFileStore;
use watoki\stores\memory\MemoryStore;
use watoki\stores\memory\SerializerRepository;

class FileStoreFixture extends Fixture {

    /** @var RawFileStore */
    public $store;

    public function setUp() {
        parent::setUp();
        $memory = new MemoryStore(File::$CLASS, new SerializerRepository());
        $this->store = new FileStoreAdapter($memory);

        $this->spec->factory->setProvider(FileStore::$CLASS, new FileStoreFixture_FileStoreProvider($memory));
        $this->spec->factory->setProvider(RawFileStore::$CLASS, new FileStoreFixture_FileStoreProvider($memory));
    }

    public function givenAFile_WithContent($filename, $content) {
        $this->store->create(new File($content), $filename);
    }

}

class FileStoreFixture_FileStoreProvider implements Provider {

    /** @var MemoryStore */
    private $memory;

    function __construct(MemoryStore $memory) {
        $this->memory = $memory;
    }

    public function provide($class, array $args = array()) {
        $root = isset($args['rootDirectory']) ? $args['rootDirectory'] : null;
        return new FileStoreAdapter($this->memory, $root);
    }
}