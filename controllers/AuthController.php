<?php
include_once 'models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function login($data) {
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $user_data = $this->user->authenticate();

        if ($user_data) {
            $auth = new AuthMiddleware($this->db);
            $token = $auth->createToken($user_data['id']);
            Response::send(200, ['message' => 'Login successful', 'token' => $token]);
        } else {
            Response::send(401, ['message' => 'Invalid credentials']);
        }
    }

    public function register($data) {

        

        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = password_hash($data['password'], PASSWORD_BCRYPT);

        if ($this->user->isEmailExists()) {
            Response::send(400, ['message' => 'Email already exists']);
            return;
        }


        if ($this->user->create()) {
            Response::send(201, ['message' => 'User registered']);
        } else {
            Response::send(500, ['message' => 'User not registered']);
        }
    }
}
?>
