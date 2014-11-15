<?php
namespace spec\watoki\stores\fixtures;

use watoki\factory\providers\CallbackProvider;
use watoki\scrut\Fixture;
use watoki\stores\adapter\FileStoreAdapter;
use watoki\stores\file\FileStore;
use watoki\stores\file\raw\File;
use watoki\stores\file\raw\RawFileStore;
use watoki\stores\memory\MemoryStore;

class FileStoreFixture extends Fixture {

    /** @var RawFileStore */
    public $store;

    public function setUp() {
        parent::setUp();
        $memory = new MemoryStore();
        $this->store = new FileStoreAdapter($memory);

        $provider = new CallbackProvider(function($class, array $args) use ($memory) {
            $root = isset($args['rootDirectory']) ? $args['rootDirectory'] : null;
            return new FileStoreAdapter($memory, $root);
        });

        $this->spec->factory->setProvider(FileStore::$CLASS, $provider);
        $this->spec->factory->setProvider(RawFileStore::$CLASS, $provider);
    }

    public function givenAFile_WithContent($filename, $content) {
        $this->store->create(new File($content), $filename);
    }

}