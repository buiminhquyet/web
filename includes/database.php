<?php
/**
 * Database Helper Class - Security Optimized (QUYETDEV Defense)
 */
class Database {
    private $host;
    private $user;
    private $pass;
    private $name;
    private $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->name = DB_NAME;
        
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        if ($this->conn->connect_error) {
            // Secure error logging (don't show to user)
            error_log("Database Connection Error: " . $this->conn->connect_error);
            header('Content-Type: application/json');
            die(json_encode(['status' => 'error', 'message' => 'Hệ thống đang bảo trì, vui lòng quay lại sau.']));
        }
        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Executes a query using prepared statements for maximum security
     */
    public function query($sql, $params = [], $types = "") {
        if (empty($params)) {
            return $this->conn->query($sql);
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare Error: " . $this->conn->error . " | SQL: " . $sql);
            return false;
        }

        if (empty($types)) {
            foreach ($params as $param) {
                if (is_int($param)) $types .= "i";
                elseif (is_double($param)) $types .= "d";
                else $types .= "s";
            }
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Return result for SELECT, or the statement itself for others
        return $result ? $result : $stmt;
    }

    /**
     * Fetch a single row
     */
    public function fetch($sql, $params = [], $types = "") {
        $result = $this->query($sql, $params, $types);
        if ($result instanceof mysqli_result) {
            return $result->fetch_assoc();
        }
        return false;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = [], $types = "") {
        $result = $this->query($sql, $params, $types);
        $rows = [];
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }

    public function insert_id() {
        return $this->conn->insert_id;
    }

    public function affected_rows() {
        return $this->conn->affected_rows;
    }
}
?>
