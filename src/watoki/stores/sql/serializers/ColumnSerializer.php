<?php
namespace watoki\stores\sql\serializers;

use watoki\stores\sql\SqlSerializer;

abstract class ColumnSerializer implements SqlSerializer {

    /** @var bool */
    private $nullable = false;

    abstract protected function getColumnDefinition();

    /**
     * @param boolean $nullable
     */
    public function setNullable($nullable) {
        $this->nullable = $nullable;
    }

    public function getDefinition() {
        return $this->getColumnDefinition() . ($this->nullable ? ' DEFAULT NULL' : ' NOT NULL');
    }

} 