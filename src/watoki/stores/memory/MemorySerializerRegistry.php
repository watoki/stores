<?php
namespace watoki\stores\memory;

use watoki\stores\common\NoneSerializer;

class MemorySerializerRegistry extends \watoki\stores\SerializerRegistry {

    public function getSerializer($type) {
        return new NoneSerializer();
    }

} 