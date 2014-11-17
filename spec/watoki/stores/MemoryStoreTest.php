<?php
namespace spec\watoki\stores;

use watoki\scrut\Specification;
use watoki\stores\memory\MemoryStore;

class MemoryStoreTest extends Specification {

    /** @var MemoryStore */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = new MemoryStore();
    }

    function testCreate() {
        $entity1 = $this->createEntity('one');
        $this->store->create($entity1);

        $entity2 = $this->createEntity('two');
        $this->store->create($entity2);

        $this->assertNotEquals($this->store->getKey($entity1), $this->store->getKey($entity2));
        $this->assertSame($entity1, $this->store->read($this->store->getKey($entity1)));
        $this->assertSame($entity2, $this->store->read($this->store->getKey($entity2)));
    }

    function testReadWrongId() {
        $this->store->create($this->createEntity('foo'));

        try {
            $this->store->read(12);
            $this->fail('No Exception thrown');
        } catch (\Exception $e) {
            $this->assertEquals("Entity with ID [12] does not exist.", $e->getMessage());
        }
    }

    function testDelete() {
        $entity = $this->createEntity('bar');
        $this->store->create($entity);
        $this->store->delete($this->store->getKey($entity));
        $this->assertEmpty($this->store->keys());

    }

    private function createEntity($id) {
        $entity = new \StdClass;
        $entity->id = $id;
        return $entity;
    }

} 