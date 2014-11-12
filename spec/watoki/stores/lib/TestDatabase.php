<?php
namespace spec\watoki\stores\lib;

use watoki\stores\sqlite\Database;

class TestDatabase extends Database {

    public $log;

    public function readOne($sql, $variables = array()) {
        $this->log = $sql . ' -- ' . json_encode($variables);
        return parent::readOne($sql, $variables);
    }

    public function readAll($sql, $variables = array()) {
        $this->log = $sql . ' -- ' . json_encode($variables);
        return parent::readAll($sql, $variables);
    }

    public function execute($sql, $variables = array()) {
        $this->log = $sql . ' -- ' . json_encode($variables);
        parent::execute($sql, $variables);
    }

} 