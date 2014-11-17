<?php
namespace watoki\stores\memory;

use watoki\stores\common\factories\CallbackSerializerFactory;
use watoki\stores\common\NoneSerializer;
use watoki\stores\exception\EntityNotFoundException;
use watoki\stores\GeneralStore;
use watoki\stores\SerializerRegistry;

class MemoryStore extends GeneralStore {

    public static $CLASS = __CLASS__;

    private $memory = array();

    private $currentId = 0;

    public function __construct() {
        parent::__construct(new NoneSerializer());
    }

    public static function registerDefaultSerializers(SerializerRegistry $registry) {
        $registry->add(new CallbackSerializerFactory(
            function () { return true; },
            function () { return new NoneSerializer(); }
        ));

        return $registry;
    }

    protected function _read($id) {
        if (!isset($this->memory[$id])) {
            throw new EntityNotFoundException("Entity with ID [$id] does not exist.");
        }
        return $this->inflate($this->memory[$id], $id);
    }

    protected function _create($entity, $id) {
        $this->memory[$id] = $this->serialize($entity, $id);
    }

    protected function _update($entity) {
        // Nothing to do
    }

    protected function _delete($key) {
        unset($this->memory[$key]);
    }

    /**
     * @return array|mixed[] All stored keys
     */
    public function keys() {
        return array_keys($this->memory);
    }

    /**
     * @param object $entity
     * @return int|string
     */
    protected function generateKey($entity) {
        return $this->currentId++;
    }


}