<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\sqlite\SqliteSerializer;

abstract class ColumnSerializer implements SqliteSerializer {

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