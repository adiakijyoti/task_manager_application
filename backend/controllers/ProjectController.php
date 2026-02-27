<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ProjectController {
    private $db;
    private $request_method;
    private $project;
    private $user_id;

    public function __construct($db) {
        $this->db = $db;
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->project = new Project($this->db);
    }

    public function processRequest($request_uri) {
        if (!$this->authenticate()) {
            $this->response(401, array("message" => "Authentication failed."));
            return;
        }

        $id = isset($request_uri[3]) ? $request_uri[3] : null;

        switch ($this->request_method) {
            case 'GET':
                if ($id) {
                    $this->getProject($id);
                } else {
                    $this->getAllProjects();
                };
                break;
            case 'POST':
                if ($this->verifyCsrfToken()) {
                    $this->createProject();
                } else {
                    $this->response(403, array("message" => "Invalid CSRF token."));
                }
                break;
            case 'PUT':
                if ($this->verifyCsrfToken()) {
                    $this->updateProject($id);
                } else {
                    $this->response(403, array("message" => "Invalid CSRF token."));
                }
                break;
            case 'DELETE':
                if ($this->verifyCsrfToken()) {
                    $this->deleteProject($id);
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

    private function getAllProjects() {
        $this->project->user_id = $this->user_id;
        $stmt = $this->project->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $projects_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $project_item = array(
                    "id" => $id,
                    "name" => $name,
                    "description" => $description
                );
                array_push($projects_arr, $project_item);
            }
            $this->response(200, $projects_arr);
        } else {
            $this->response(200, array());
        }
    }

    private function getProject($id) {
        $this->project->id = $id;
        $this->project->user_id = $this->user_id;
        $this->project->readOne();
        if ($this->project->name != null) {
            $project_arr = array(
                "id" => $this->project->id,
                "name" => $this->project->name,
                "description" => $this->project->description
            );
            $this->response(200, $project_arr);
        } else {
            $this->response(404, array("message" => "Project not found."));
        }
    }
    
    private function createProject() {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name)) {
            if (strlen($data->name) > 255) {
                $this->response(400, array("message" => "Project name is too long."));
                return;
            }

            $this->project->name = $data->name;
            $this->project->description = isset($data->description) ? $data->description : '';
            $this->project->user_id = $this->user_id;

            if ($this->project->create()) {
                $this->response(201, array("message" => "Project was created."));
            } else {
                $this->response(503, array("message" => "Unable to create project."));
            }
        } else {
            $this->response(400, array("message" => "Unable to create project. Data is incomplete."));
        }
    }

    private function updateProject($id) {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($id) && !empty($data->name)) {
            if (strlen($data->name) > 255) {
                $this->response(400, array("message" => "Project name is too long."));
                return;
            }

            $this->project->id = $id;
            $this->project->name = $data->name;
            $this->project->description = $data->description;
            $this->project->user_id = $this->user_id;

            if ($this->project->update()) {
                $this->response(200, array("message" => "Project was updated."));
            } else {
                $this->response(503, array("message" => "Unable to update project."));
            }
        } else {
            $this->response(400, array("message" => "Unable to update project. Data is incomplete."));
        }
    }

    private function deleteProject($id) {
        if (!empty($id)) {
            $this->project->id = $id;
            $this->project->user_id = $this->user_id;
            if ($this->project->delete()) {
                $this->response(200, array("message" => "Project was deleted."));
            } else {
                $this->response(503, array("message" => "Unable to delete project."));
            }
        } else {
            $this->response(400, array("message" => "Unable to delete project. ID not provided."));
        }
    }

    private function response($status_code, $data) {
        http_response_code($status_code);
        echo json_encode($data);
    }