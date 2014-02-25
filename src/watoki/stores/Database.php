<?php
namespace watoki\stores;

class Database {

    static $CLASS = __CLASS__;

    /**
     * @var \PDO
     */
    private $pdo;

    function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
    }

    public function readOne($sql, $variables = array()) {
        $result = $this->readAll($sql, $variables);
        if (empty($result)) {
            throw new \PDOException("Empty result for [$sql, " . var_export($variables, true) . "]");
        }
        return $result[0];
    }

    public function readAll($sql, $variables = array()) {
        try {
            return $this->doExecute($sql, $variables)->fetchAll();
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            throw new \PDOException("Error [$message] while executing query [$sql, " . var_export($variables, true) . "]");
        }
    }

    public function execute($sql, $variables = array()) {
        try {
            $this->doExecute($sql, $variables);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            throw new \PDOException("Error [$message] while executing [$sql, " . var_export($variables, true) . "]");
        }
    }

    /**
     * @param $sql
     * @param $variables
     * @return \PDOStatement
     */
    private function doExecute($sql, $variables) {
        $sth = $this->pdo->prepare($sql);
        $sth->execute($variables);
        return $sth;
    }

    public function quote($value) {
        return $this->pdo->quote($value, $this->getParamTypeOf($value));
    }

    private function getParamTypeOf($value) {
        if (is_int($value)) {
            return \PDO::PARAM_INT;
        } else if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        } else if (is_float($value)) {
            return \PDO::PARAM_INT;
        } else if (is_null($value)) {
            return \PDO::PARAM_NULL;
        } else {
            return \PDO::PARAM_STR;
        }
    }

    public function getLastInsertedId() {
        return intval($this->pdo->lastInsertId());
    }

}
