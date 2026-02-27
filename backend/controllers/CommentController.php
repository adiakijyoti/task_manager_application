<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CommentController {
    private $db;
    private $request_method;
    private $comment;
    private $user_id;

    public function __construct($db) {
        $this->db = $db;
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->comment = new Comment($this->db);
    }

    public function processRequest($request_uri) {
        if (!$this->authenticate()) {
            $this->response(401, array("message" => "Authentication failed."));
            return;
        }

        $task_id = $request_uri[3];

        switch ($this->request_method) {
            case 'GET':
                $this->getCommentsForTask($task_id);
                break;
            case 'POST':
                if ($this->verifyCsrfToken()) {
                    $this->createComment($task_id);
                } else {
                    $this->response(403, array("message" => "Invalid CSRF token."));
                }
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

    private function getCommentsForTask($task_id) {
        $this->comment->task_id = $task_id;
        $stmt = $this->comment->read();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->response(200, $comments);
    }

    private function createComment($task_id) {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->content)) {
            if (strlen($data->content) > 65535) { // 65535 is the max length of a TEXT field
                $this->response(400, array("message" => "Comment is too long."));
                return;
            }

            $this->comment->content = $data->content;
            $this->comment->task_id = $task_id;
            $this->comment->user_id = $this->user_id;

            if ($this->comment->create()) {
                $this->response(201, array("message" => "Comment created."));
            } else {
                $this->response(503, array("message" => "Unable to create comment."));
            }
        } else {
            $this->response(400, array("message" => "Incomplete data."));
        }
    }

    private function response($status_code, $data) {
        http_response_code($status_code);
        echo json_encode($data);
    }
}
?>