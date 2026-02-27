<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

require __DIR__ . '/vendor/autoload.php';
include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();

// Simple router
$request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$resource = $request_uri[4];
$action = isset($request_uri[5]) ? $request_uri[5] : '';

// Handle requests based on resource
switch ($resource) {
    case 'users':
        include_once './controllers/UserController.php';
        $controller = new UserController($db);
        $controller->processRequest($request_uri);
        break;
    case 'projects':
        include_once './models/Project.php';
        include_once './controllers/ProjectController.php';
        // If the URL is /projects/{id}/tasks, route to TaskController
        if (isset($request_uri[3]) && $request_uri[3] == 'tasks') {
            include_once './models/Task.php';
            include_once './controllers/TaskController.php';
            $controller = new TaskController($db);
        } else {
            $controller = new ProjectController($db);
        }
        $controller->processRequest($request_uri);
        break;
    case 'tasks':
        include_once './models/Task.php';
        include_once './controllers/TaskController.php';
        // If the URL is /tasks/{id}/comments, route to CommentController
        if (isset($request_uri[4]) && $request_uri[4] == 'comments') {
            include_once './models/Comment.php';
            include_once './controllers/CommentController.php';
            $controller = new CommentController($db);
        } else {
            $controller = new TaskController($db);
        }
        $controller->processRequest($request_uri);
        break;
    // Other resources like 'projects', 'tasks' will be handled here
    default:
        http_response_code(404);
        echo json_encode(array("message" => "Resource not found."));
        break;
}
?>