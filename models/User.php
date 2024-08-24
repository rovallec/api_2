<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un nuevo usuario (Registro)
    public function create() {
        // Consulta SQL para insertar un nuevo usuario
        $query = "INSERT INTO " . $this->table . " SET name=:name, email=:email, password=:password";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);

        // Enlace de parámetros
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para autenticar un usuario (Login)
    public function authenticate() {
        // Consulta SQL para buscar al usuario por email
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 0,1";

        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Enlace de parámetros
        $stmt->bindParam(':email', $this->email);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener la fila correspondiente
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario existe, verificar la contraseña
        if ($user) {
            if (password_verify($this->password, $user['password'])) {
                return $user; // Retornar los datos del usuario si la autenticación es exitosa
            }
        }

        // Si no se encuentra el usuario o la contraseña no es correcta
        return false;
    }

    // Método para verificar si un email ya está registrado
    public function isEmailExists() {
        // Consulta SQL para buscar el email
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 0,1";

        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Enlace de parámetros
        $stmt->bindParam(':email', $this->email);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener la fila correspondiente
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si se encuentra el email, retornar verdadero
        if ($user) {
            return true;
        }

        // Si no se encuentra el email, retornar falso
        return false;
    }
}
?>
