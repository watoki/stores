<?php
namespace spec\watoki\stores;

use spec\watoki\stores\fixtures\StoresTestEntity;
use watoki\scrut\Specification;
use watoki\stores\memory\MemorySerializerRegistry;
use watoki\stores\memory\MemoryStore;

class MemoryStoreTest extends Specification {

    /** @var MemoryStore */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = new MemoryStore(StoresTestEntity::$CLASS, new MemorySerializerRegistry());
    }

    function testCreate() {
        $entity1 = new StoresTestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity1);

        $entity2 = new StoresTestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity2);

        $this->assertNotEquals($this->store->getKey($entity1), $this->store->getKey($entity2));
        $this->assertSame($entity1, $this->store->read($this->store->getKey($entity1)));
        $this->assertSame($entity2, $this->store->read($this->store->getKey($entity2)));
    }

    function testReadWrongId() {
        $this->store->create(new StoresTestEntity(true, 42, 1.6, "Hi", new \DateTime()));

        try {
            $this->store->read(12);
            $this->fail('No Exception thrown');
        } catch (\Exception $e) {
            $this->assertEquals("Entity with ID [12] does not exist.", $e->getMessage());
        }
    }

    function testDelete() {
        $entity = new StoresTestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity);
        $this->store->delete($this->store->getKey($entity));
        $this->assertEmpty($this->store->keys());

    }

} 