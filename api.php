<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Start the session to manage authentication token

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['register'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/api/index.php/auth/login");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'password' => $password]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return "cURL error: " . curl_error($ch);
        }
        
        curl_close($ch);

        $responseData = json_decode($response, true);
        if (isset($responseData['token'])) {
            $_SESSION['authToken'] = $responseData['token'];
            return null; // No error
        } else {
            return $responseData['message'] ?? 'Login failed';
        }
    }
    return null; // No login attempt
}

function fetchProducts() {
    if (!isset($_SESSION['authToken'])) {
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/api/index.php/products");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $_SESSION['authToken']]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return "cURL error: " . curl_error($ch);
    }
    
    curl_close($ch);

    return json_decode($response, true);
}

function createProduct($data) {
    if (!isset($_SESSION['authToken'])) {
        return 'Unauthorized';
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/api/index.php/products");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['authToken'],
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return "cURL error: " . curl_error($ch);
    }
    
    curl_close($ch);

    return json_decode($response, true);
}

function registerUser($data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/api/index.php/auth/register");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return "cURL error: " . curl_error($ch);
    }
    
    curl_close($ch);

    return json_decode($response, true);
}

$error = handleLogin();
$products = isset($_SESSION['authToken']) ? fetchProducts() : null;

$creationResult = null;
$registrationResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_product'])) {
        $productData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? ''
        ];
        $creationResult = createProduct($productData);
        if (isset($creationResult['message']) && $creationResult['message'] === 'Product created successfully') {
            $products = fetchProducts(); // Refresh product list after successful creation
            $creationResult = 'Product created successfully';
        } else {
            $creationResult = $creationResult['message'] ?? 'Failed to create product';
        }
    } elseif (isset($_POST['register'])) {
        $email = $_POST['register_email'] ?? '';
        $name = $_POST['register_name'] ?? '';
        $password = $_POST['register_password'] ?? '';

        $registrationResult = registerUser([
            'email' => $email,
            'name' => $name,
            'password' => $password
        ]);
        
        if (isset($registrationResult['message']) && $registrationResult['message'] === 'User registered successfully') {
            $registrationResult = 'User registered successfully';
        } else {
            $registrationResult = $registrationResult['message'] ?? 'Failed to register user';
        }
    } elseif (isset($_POST['show_create_form'])) {
        // Show create form
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title><?php echo isset($_SESSION['authToken']) ? 'Product List | Ludiflex' : 'Login | Ludiflex'; ?></title>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
<div class="container mt-5">
    <?php if (!isset($_SESSION['authToken'])): ?>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <form method="post">
                    <div class="card">
                        <div class="card-header">
                            <h4>Login</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($registrationResult)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo htmlspecialchars($registrationResult); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="email">Correo</label>
                                <input id="email" type="text" name="email" class="form-control" placeholder="Correo" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input id="password" type="password" name="password" class="form-control" placeholder="Contraseña" autocomplete="off" required>
                            </div>
                            <button class="btn btn-primary" type="submit">Iniciar sesión</button>
                            <button type="submit" name="show_register_form" class="btn btn-link">Registrarse</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif (isset($_POST['show_register_form'])): ?>
        <div class="row mt-5">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4>Register</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="register_name">Name</label>
                                <input id="register_name" type="text" name="register_name" class="form-control" placeholder="Name" required>
                            </div>
                            <div class="form-group">
                                <label for="register_email">Correo</label>
                                <input id="register_email" type="text" name="register_email" class="form-control" placeholder="Correo" required>
                            </div>
                            <div class="form-group">
                                <label for="register_password">Contraseña</label>
                                <input id="register_password" type="password" name="register_password" class="form-control" placeholder="Contraseña" required>
                            </div>
                            <button class="btn btn-primary" type="submit" name="register">Registrar</button>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="d-flex justify-content-between mb-3">
                    <form method="post" class="d-inline">
                        <button type="submit" name="show_create_form" class="btn btn-success">Crear Nuevo Producto</button>
                    </form>
                    <form method="get" class="d-inline">
                        <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                    </form>
                </div>
                <?php if (isset($_POST['show_create_form'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h4>Create Product</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input id="name" type="text" name="name" class="form-control" placeholder="Name" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <input id="description" type="text" name="description" class="form-control" placeholder="Description" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input id="price" type="text" name="price" class="form-control" placeholder="Price" required>
                                </div>
                                <button class="btn btn-primary" type="submit" name="create_product">Create Product</button>
                                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($creationResult): ?>
                    <div class="alert alert-success mt-3" role="alert">
                        <?php echo htmlspecialchars($creationResult); ?>
                    </div>
                <?php endif; ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h4>Product List</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($products && is_array($products)): ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                                            <td><?php echo htmlspecialchars($product['price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No products found or error occurred.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
</body>
</html>
