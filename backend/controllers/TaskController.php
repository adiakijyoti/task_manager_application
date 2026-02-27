<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TaskController {
    private $db;
    private $request_method;
    private $task;
    private $user_id;

    public function __construct($db) {
        $this->db = $db;
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->task = new Task($this->db);
    }

    public function processRequest($request_uri) {
        if (!$this->authenticate()) {
            $this->response(401, array("message" => "Authentication failed."));
            return;
        }

        // Route: /projects/{projectId}/tasks
        if (isset($request_uri[3]) && $request_uri[3] == 'tasks') {
            $project_id = $request_uri[2];
            $task_id = isset($request_uri[4]) ? $request_uri[4] : null;
            $this->handleProjectTasks($project_id, $task_id);
        } else if ($request_uri[2] == 'tasks') { // Route: /tasks/{taskId} or /tasks
            $task_id = isset($request_uri[3]) ? $request_uri[3] : null;
            if ($task_id) {
                $this->handleTasks($task_id);
            } else {
                $this->getAllUserTasks();
            }
        }
    }

    private function getAllUserTasks() {
        $stmt = $this->task->readAllForUser($this->user_id);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->response(200, $tasks);
    }

    private function handleProjectTasks($project_id, $task_id) {
        // Authorization: Check if user owns the project
        if (!$this->isProjectOwner($project_id)) {
            $this->response(403, array("message" => "You don't have access to this project."));
            return;
        }

        switch ($this->request_method) {
            case 'GET':
                $this->getAllTasksForProject($project_id);
                break;
            case 'POST':
                if ($this->verifyCsrfToken()) {
                    $this->createTask($project_id);
                } else {
                    $this->response(403, array("message" => "Invalid CSRF token."));
                }
                break;
            default:
                $this->response(405, array("message" => "Method Not Allowed"));
                break;
        }
    }

    private function handleTasks($task_id) {
         if (!$task_id) {
            $this->response(400, array("message" => "Task ID not provided."));
            return;
        }

        if (!$this->verifyCsrfToken()) {
            $this->response(403, array("message" => "Invalid CSRF token."));
            return;
        }

        switch ($this->request_method) {
            case 'PUT':
                $this->updateTask($task_id);
                break;
            case 'DELETE':
                $this->deleteTask($task_id);
                break;
            default:
                $this->response(405, array("message" => "Method Not Allowed"));
                break;
        }
    }

    private function verifyCsrfToken() {
        $csrf_header = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : null;
        return isset($_SESSION['csrf_token']) && $csrf_header === $_SESSION['csrf_token'];
    }

    private function authenticate() {
        // Similar to ProjectController, should be refactored into a middleware
        $auth_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
        if ($auth_header) {
            list($jwt) = sscanf($auth_header, 'Bearer %s');
            if ($jwt) {
                try {
                    $database = new Database();
                    $secret_key = $database->secret_key;
                    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
                    $this->user_id = $decoded->data->id;
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

    private function isProjectOwner($project_id) {
        $project = new Project($this->db);
        $project->id = $project_id;
        $project->user_id = $this->user_id;
        $project->readOne();
        return $project->name != null;
    }

    private function getAllTasksForProject($project_id) {
        $this->task->project_id = $project_id;
        $stmt = $this->task->read();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->response(200, $tasks);
    }

    private function createTask($project_id) {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->title)) {
            if (strlen($data->title) > 255) {
                $this->response(400, array("message" => "Task title is too long."));
                return;
            }

            $allowed_priorities = ['Low', 'Medium', 'High'];
            if (isset($data->priority) && !in_array($data->priority, $allowed_priorities)) {
                $this->response(400, array("message" => "Invalid priority value."));
                return;
            }

            if (isset($data->due_date) && !DateTime::createFromFormat('Y-m-d', $data->due_date)) {
                $this->response(400, array("message" => "Invalid date format for due date. Use YYYY-MM-DD."));
                return;
            }

            $this->task->title = $data->title;
            $this->task->description = $data->description ?? null;
            $this->task->project_id = $project_id;
            $this->task->status = $data->status ?? 'To Do';
            $this->task->priority = $data->priority ?? 'Medium';
            $this->task->due_date = $data->due_date ?? null;
            $this->task->tags = $data->tags ?? null;
            $this->task->assignee_id = $data->assignee_id ?? null;

            if ($this->task->create()) {
                $this->addNotification($project_id, json_encode(array('action' => 'task_updated')));
                $this->response(201, array("message" => "Task created."));
            } else {
                $this->response(503, array("message" => "Unable to create task."));
            }
        } else {
            $this->response(400, array("message" => "Incomplete data."));
        }
    }
    
    private function updateTask($task_id) {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($task_id) && !empty($data->title)) {
            if (strlen($data->title) > 255) {
                $this->response(400, array("message" => "Task title is too long."));
                return;
            }

            $allowed_priorities = ['Low', 'Medium', 'High'];
            if (isset($data->priority) && !in_array($data->priority, $allowed_priorities)) {
                $this->response(400, array("message" => "Invalid priority value."));
                return;
            }

            if (isset($data->due_date) && !DateTime::createFromFormat('Y-m-d', $data->due_date)) {
                $this->response(400, array("message" => "Invalid date format for due date. Use YYYY-MM-DD."));
                return;
            }

            $this->task->id = $task_id;
            $this->task->title = $data->title;
            $this->task->description = $data->description;
            $this->task->status = $data->status;
            $this->task->priority = $data->priority;
            $this->task->due_date = $data->due_date;
            $this->task->tags = $data->tags;
            $this->task->assignee_id = $data->assignee_id;

            if ($this->task->update()) {
                $this->addNotification($data->project_id, json_encode(array('action' => 'task_updated')));
                $this->response(200, array("message" => "Task updated."));
            } else {
                $this->response(503, array("message" => "Unable to update task."));
            }
        } else {
            $this->response(400, array("message" => "Incomplete data."));
        }
    }

    private function deleteTask($task_id) {
        // Before deleting, we need to get the project_id to notify the correct clients
        $this->task->id = $task_id;
        // This is not ideal, we should have a method to get task details before deleting
        // For now, we can't notify on delete
        if ($this->task->delete()) {
            $this->response(200, array("message" => "Task deleted."));
        } else {
            $this->response(503, array("message" => "Unable to delete task."));
        }
    }

    private function addNotification($project_id, $message) {
        $query = "INSERT INTO notifications SET project_id = ?, message = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $project_id);
        $stmt->bindParam(2, $message);
        $stmt->execute();
    }

    private function response($status_code, $data) {
        http_response_code($status_code);
        echo json_encode($data);
    }
}
?>