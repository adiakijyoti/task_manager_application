<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public $id;
    public $content;
    public $task_id;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET content=:content, task_id=:task_id, user_id=:user_id";
        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->task_id = htmlspecialchars(strip_tags($this->task_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":task_id", $this->task_id);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function read() {
        $query = "SELECT c.id, c.content, c.created_at, u.username FROM " . $this->table_name . " c LEFT JOIN users u ON c.user_id = u.id WHERE c.task_id = ? ORDER BY c.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->task_id);
        $stmt->execute();
        return $stmt;
    }
}
?>