<?php
// pages/kelola_pelanggan.php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = trim($_POST['username']);
                $password = md5(trim($_POST['password']));
                $nama = trim($_POST['nama']);
                
                // Check if username exists
                $check = executeQuery("SELECT id FROM users WHERE username = ?", [$username]);
                if ($check->rowCount() > 0) {
                    $error = 'Username sudah digunakan!';
                } else {
                    executeQuery("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, 'pelanggan')",
                                [$username, $password, $nama]);
                    $message = 'Pelanggan berhasil ditambahkan!';
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $username = trim($_POST['username']);
                $nama = trim($_POST['nama']);
                
                // Check if username exists for other users
                $check = executeQuery("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
                if ($check->rowCount() > 0) {
                    $error = 'Username sudah digunakan!';
                } else {
                    if (!empty($_POST['password'])) {
                        $password = md5(trim($_POST['password']));
                        executeQuery("UPDATE users SET username = ?, password = ?, nama = ? WHERE id = ?",
                                    [$username, $password, $nama, $id]);
                    } else {
                        executeQuery("UPDATE users SET username = ?, nama = ? WHERE id = ?",
                                    [$username, $nama, $id]);
                    }
                    $message = 'Data pelanggan berhasil diupdate!';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                executeQuery("DELETE FROM users WHERE id = ?", [$id]);
                $message = 'Pelanggan berhasil dihapus!';
                break;
        }
    }
}

// Get all pelanggan
$pelanggan = executeQuery("SELECT * FROM users WHERE role = 'pelanggan' ORDER BY nama")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan - Pembayaran Listrik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <h1>Kelola Pelanggan</h1>
        <div class="nav">
            <ul>
                <li><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="kelola_pelanggan.php">Kelola Pelanggan</a></li>
                <li><a href="kelola_penggunaan.php">Kelola Penggunaan</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Form -->
        <div class="card">
            <h3>Tambah Pelanggan Baru</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn">Tambah</button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="card">
            <h3>Data Pelanggan</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pelanggan as $row): ?>
                    <tr>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <button onclick="editRow(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="btn" style="padding: 5px 10px; margin-right: 5px;">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; min-width: 400px;">
            <h3>Edit Pelanggan</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                
                <div class="form-group">
                    <label>Password Baru (kosongkan jika tidak ingin mengubah):</label>
                    <input type="password" name="password" id="edit_password">
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama" id="edit_nama" required>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="closeModal()" class="btn" style="background: #95a5a6; margin-right: 10px;">Batal</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editRow(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_password').value = '';
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>