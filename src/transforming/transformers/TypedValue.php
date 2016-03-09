<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\Type;

class TypedValue {

    /** @var mixed */
    private $value;

    /** @var Type */
    private $type;

    /**
     * @param mixed $value
     * @param Type $type
     */
    public function __construct($value, Type $type) {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return Type
     */
    public function getType() {
        return $this->type;
    }
}