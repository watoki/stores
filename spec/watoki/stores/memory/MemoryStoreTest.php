<?php
namespace spec\watoki\stores\memory;

use spec\watoki\stores\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\memory\SerializerRepository;

class MemoryStoreTest extends Specification {

    function testCreate() {
        $entity1 = new TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity1);
        $entity2 = new TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity2);

        $this->assertSame($entity1, $this->store->read($entity1->id));
        $this->assertSame($entity2, $this->store->read($entity2->id));
        $this->assertNotEquals($entity1->id, $entity2->id);
    }

    function testReadWrongId() {
        $this->store->create(new TestEntity(true, 42, 1.6, "Hi", new \DateTime()));

        try {
            $this->store->read(12);
            $this->fail('No Exception thrown');
        } catch (\Exception $e) {
            $this->assertEquals("Entity with ID [12] does not exist.", $e->getMessage());
        }
    }

    /** @var TestStore */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = new TestStore(new SerializerRepository());
    }

} 