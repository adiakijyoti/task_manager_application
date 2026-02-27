<?php
class Task {
    private $conn;
    private $table_name = "tasks";

    public $id;
    public $title;
    public $description;
    public $project_id;
    public $assignee_id;
    public $status;
    public $priority;
    public $due_date;
    public $tags;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET title=:title, description=:description, project_id=:project_id, assignee_id=:assignee_id, status=:status, priority=:priority, due_date=:due_date, tags=:tags";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->project_id = htmlspecialchars(strip_tags($this->project_id));
        $this->assignee_id = htmlspecialchars(strip_tags($this->assignee_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));
        $this->tags = htmlspecialchars(strip_tags($this->tags));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":assignee_id", $this->assignee_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":tags", $this->tags);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function read() {
        $query = "SELECT t.id, t.title, t.description, t.status, t.priority, t.due_date, t.tags, u.username as assignee_name FROM " . $this->table_name . " t LEFT JOIN users u ON t.assignee_id = u.id WHERE t.project_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->project_id);
        $stmt->execute();
        return $stmt;
    }

    function readAllForUser($user_id) {
        $query = "SELECT t.* FROM " . $this->table_name . " t JOIN projects p ON t.project_id = p.id WHERE p.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET title = :title, description = :description, status = :status, priority = :priority, due_date = :due_date, tags = :tags, assignee_id = :assignee_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));
        $this->tags = htmlspecialchars(strip_tags($this->tags));
        $this->assignee_id = htmlspecialchars(strip_tags($this->assignee_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':due_date', $this->due_date);
        $stmt->bindParam(':tags', $this->tags);
        $stmt->bindParam(':assignee_id', $this->assignee_id);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>