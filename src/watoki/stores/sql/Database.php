<?php
namespace watoki\stores\sql;

class Database {

    static $CLASS = __CLASS__;

    /**
     * @var \PDO
     */
    private $pdo;

    function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Closes the connection to the database
     */
    public function close() {
        $this->pdo = null;
    }

    /**
     * @return int
     */
    public function getLastInsertedId() {
        return intval($this->pdo->lastInsertId());
    }

    /**
     * @param string $sql
     * @param array $variables
     * @return mixed|null
     */
    public function readOne($sql, $variables = array()) {
        $result = $this->readAll($sql, $variables);
        if (empty($result)) {
            return null;
        }
        return $result[0];
    }

    /**
     * @param string $sql
     * @param array $variables
     * @throws \PDOException
     * @return array
     */
    public function readAll($sql, $variables = array()) {
        try {
            return $this->doExecute($sql, $variables)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            throw new \PDOException("Error [$message] while executing query [$sql, " . var_export($variables, true) . "]");
        }
    }

    /**
     * @param string $sql
     * @param array $variables
     * @throws \PDOException
     * @return void
     */
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

    /**
     * @param mixed $value
     * @return string
     */
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

}
