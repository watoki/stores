<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\DefinedSerializer;

abstract class ColumnSerializer implements DefinedSerializer {

    /** @var bool */
    private $nullable;

    /**
     * @param bool $nullable
     */
    function __construct($nullable = false) {
        $this->nullable = $nullable;
    }

    abstract protected function getColumnDefinition();

    public function getDefinition() {
        return $this->getColumnDefinition() . ($this->nullable ? ' DEFAULT NULL' : ' NOT NULL');
    }

} 