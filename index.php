<?php
include_once 'config/db.php';
include_once 'helpers/Response.php';
include_once 'controllers/ProductController.php';
include_once 'controllers/AuthController.php';
include_once 'middleware/AuthMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$db = (new DB())->getConnection();

switch ($uri[3]) {
    case 'products':
        $authMiddleware = new AuthMiddleware($db);
        $user_id = $authMiddleware->authenticate();
    
        if ($user_id) {
            $productController = new ProductController($db);
    
            if ($method === 'GET' && !isset($uri[4])) {
                $productController->getAll();
            }
    
            if ($method === 'GET' && isset($uri[4])) {
                $productController->getSingle($uri[4]);
            }
    
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $productController->create($data);
            }
    
            if ($method === 'PUT' && isset($uri[4])) {
                $data = json_decode(file_get_contents('php://input'), true);
                $productController->update($uri[4], $data);
            }
    
            if ($method === 'DELETE' && isset($uri[4])) {
                $productController->delete($uri[4]);
            }
        }
        break;

    case 'auth':
        $authController = new AuthController($db);
        if ($method === 'POST' && $uri[4] === 'login') {
            $data = json_decode(file_get_contents("php://input"), true);
            $authController->login($data);
        } elseif ($method === 'POST' && $uri[4] === 'register') {
            $data = json_decode(file_get_contents("php://input"), true);
            $authController->register($data);
        } else {
            Response::send(405, ['message' => 'Method not allowed']);
        }
        break;

    default:
        Response::send(404, ['message' => 'Not found']);
        break;
}
?>
