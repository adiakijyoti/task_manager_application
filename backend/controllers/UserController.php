<?php
include_once '../models/User.php';

use Firebase\JWT\JWT;

class UserController {
    private $db;
    private $request_method;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->user = new User($this->db);
    }

    public function processRequest($request_uri) {
        $action = isset($request_uri[3]) ? $request_uri[3] : '';

        switch ($this->request_method) {
            case 'GET':
                if ($this->authenticate()) {
                    $this->getAllUsers();
                } else {
                    $this->response(401, array("message" => "Authentication failed."));
                }
                break;
            case 'POST':
                if ($action === 'login') {
                    $this->login();
                } else {
                    $this->handlePost();
                }
                break;
            // Other methods like GET, PUT, DELETE will be handled here
            default:
                $this->response(405, "Method Not Allowed");
                break;
        }
    }

    private function getAllUsers() {
        $stmt = $this->user->readAll();
        $users_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $user_item = array(
                "id" => $id,
                "username" => $username
            );
            array_push($users_arr, $user_item);
        }
        $this->response(200, $users_arr);
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
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return false;
    }

    private function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->email) && !empty($data->password)) {
            $this->user->email = $data->email;
            $stmt = $this->user->findByEmail();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $id = $row['id'];
                $username = $row['username'];
                $password2 = $row['password'];
                $role = $row['role'];

                if (password_verify($data->password, $password2)) {
                    $secret_key = "YOUR_SECRET_KEY"; // Note: Should be in a config file
                    $issuer_claim = "localhost";
                    $audience_claim = "localhost";
                    $issuedat_claim = time();
                    $notbefore_claim = $issuedat_claim;
                    $expire_claim = $issuedat_claim + 3600;

                    $token = array(
                        "iss" => $issuer_claim,
                        "aud" => $audience_claim,
                        "iat" => $issuedat_claim,
                        "nbf" => $notbefore_claim,
                        "exp" => $expire_claim,
                        "data" => array(
                            "id" => $id,
                            "username" => $username,
                            "email" => $this->user->email,
                            "role" => $role
                        )
                    );

                    $jwt = JWT::encode($token, $secret_key, 'HS256');

                    // Generate CSRF token
                    $csrf_token = bin2hex(random_bytes(32));
                    $_SESSION['csrf_token'] = $csrf_token;

                    $this->response(200, json_encode(array("message" => "Successful login.", "token" => $jwt, "csrf_token" => $csrf_token)));
                } else {
                    $this->response(401, "Invalid password.");
                }
            } else {
                $this->response(401, "Invalid email.");
            }
        } else {
            $this->response(400, "Incomplete data.");
        }
    }


    private function handlePost() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->username) && !empty($data->email) && !empty($data->password)) {
            // Validate email format
            if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                $this->response(400, array("message" => "Invalid email format."));
                return;
            }

            // Validate password length
            if (strlen($data->password) < 8) {
                $this->response(400, array("message" => "Password must be at least 8 characters long."));
                return;
            }

            $user = new User($this->db);
            $user->username = $data->username;
            $user->email = $data->email;
            $user->password = $data->password;
            $user->role = isset($data->role) ? $data->role : 'user'; // Default role is 'user'

            if ($user->create()) {
                $this->response(201, array("message" => "User was created."));
            } else {
                $this->response(503, array("message" => "Unable to create user."));
            }
        } else {
            $this->response(400, array("message" => "Unable to create user. Data is incomplete."));
        }
    }

    private function response($status_code, $message) {
        http_response_code($status_code);
        echo json_encode(array("message" => $message));
    }
}
?>