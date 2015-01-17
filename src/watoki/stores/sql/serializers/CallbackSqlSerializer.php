<?php
namespace watoki\stores\sql\serializers;

use watoki\stores\common\CallbackSerializer;
use watoki\stores\sql\SqlSerializer;

class CallbackSqlSerializer extends CallbackSerializer implements SqlSerializer {

    /** @var string|array */
    private $definition;

    public function __construct($serializer, $inflater, $definition) {
        parent::__construct($serializer, $inflater);
        $this->definition = $definition;
    }


    /**
     * @return string|array|\string[] If array (indexed by column name), serialize() must return an array with same keys.
     */
    public function getDefinition() {
        return $this->definition;
    }
}