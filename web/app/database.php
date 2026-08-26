<?php
class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        $config = require BASE_PATH . '/app/config.php';
        $this->db = new SQLite3($config['db_path']);
        $this->db->enableExceptions(true);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        return $stmt->execute();
    }

    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function lastInsertId() {
        return $this->db->lastInsertRowID();
    }
}
