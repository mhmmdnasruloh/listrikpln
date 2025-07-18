<?php
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    
    $stmt = executeQuery("SELECT * FROM users WHERE username = ? AND password = ?", 
                        [$username, $password]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            redirect('dashboard_admin.php');
        } else {
            redirect('dashboard_pelanggan.php');
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pembayaran Listrik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class= "headerlog">
        <h1>Sistem Pembayaran Listrik Pascabayar</h1> <br>
        <h1>PROJECT LSP BSI</h1>
    </div>
    
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px;">LOGIN</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" req  uired>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Login</button>
        </form>
        
       
    </div>
</body>
</html>