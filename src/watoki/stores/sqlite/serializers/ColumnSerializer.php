<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\SqliteSerializer;

abstract class ColumnSerializer implements SqliteSerializer {

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