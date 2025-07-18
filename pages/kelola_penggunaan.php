<?php
// pages/kelola_penggunaan.php
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
                $user_id = $_POST['user_id'];
                $bulan = $_POST['bulan'];
                $kwh_awal = $_POST['kwh_awal'];
                $kwh_akhir = $_POST['kwh_akhir'];
                
                if ($kwh_akhir <= $kwh_awal) {
                    $error = 'KWH akhir harus lebih besar dari KWH awal!';
                } else {
                    executeQuery("INSERT INTO penggunaan_listrik (user_id, bulan, kwh_awal, kwh_akhir) VALUES (?, ?, ?, ?)",
                                [$user_id, $bulan, $kwh_awal, $kwh_akhir]);
                    $message = 'Data penggunaan berhasil ditambahkan!';
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $kwh_awal = $_POST['kwh_awal'];
                $kwh_akhir = $_POST['kwh_akhir'];
                $status = $_POST['status_bayar'];
                
                if ($kwh_akhir <= $kwh_awal) {
                    $error = 'KWH akhir harus lebih besar dari KWH awal!';
                } else {
                    executeQuery("UPDATE penggunaan_listrik SET kwh_awal = ?, kwh_akhir = ?, status_bayar = ? WHERE id = ?",
                                [$kwh_awal, $kwh_akhir, $status, $id]);
                    $message = 'Data penggunaan berhasil diupdate!';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                executeQuery("DELETE FROM penggunaan_listrik WHERE id = ?", [$id]);
                $message = 'Data penggunaan berhasil dihapus!';
                break;
        }
    }
}

// Get all penggunaan with user names
$penggunaan = executeQuery("
    SELECT p.*, u.nama 
    FROM penggunaan_listrik p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.bulan DESC
")->fetchAll();

// Get all pelanggan for dropdown
$pelanggan = executeQuery("SELECT id, nama FROM users WHERE role = 'pelanggan'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penggunaan - Pembayaran Listrik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <h1>Kelola Penggunaan Listrik</h1>
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
            <h3>Tambah Penggunaan Listrik</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Pelanggan:</label>
                    <select name="user_id" required>
                        <option value="">Pilih Pelanggan</option>
                        <?php foreach ($pelanggan as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo $p['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Bulan:</label>
                    <input type="month" name="bulan" required>
                </div>
                
                <div class="form-group">
                    <label>KWH Awal:</label>
                    <input type="number" name="kwh_awal" required>
                </div>
                
                <div class="form-group">
                    <label>KWH Akhir:</label>
                    <input type="number" name="kwh_akhir" required>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn">Tambah</button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="card">
            <h3>Data Penggunaan Listrik</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Bulan</th>
                        <th>KWH Awal</th>
                        <th>KWH Akhir</th>
                        <th>Total KWH</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penggunaan as $row): ?>
                    <tr>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['bulan']; ?></td>
                        <td><?php echo $row['kwh_awal']; ?></td>
                        <td><?php echo $row['kwh_akhir']; ?></td>
                        <td><?php echo $row['total_kwh']; ?> KWH</td>
                        <td>Rp <?php echo number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                        <td>
                            <span style="color: <?php echo $row['status_bayar'] == 'sudah_bayar' ? 'green' : 'red'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $row['status_bayar'])); ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="editRow(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="btn" style="padding: 5px 10px; margin-right: 5px;">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
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
            <h3>Edit Penggunaan Listrik</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>KWH Awal:</label>
                    <input type="number" name="kwh_awal" id="edit_kwh_awal" required>
                </div>
                
                <div class="form-group">
                    <label>KWH Akhir:</label>
                    <input type="number" name="kwh_akhir" id="edit_kwh_akhir" required>
                </div>
                
                <div class="form-group">
                    <label>Status Bayar:</label>
                    <select name="status_bayar" id="edit_status" required>
                        <option value="belum_bayar">Belum Bayar</option>
                        <option value="sudah_bayar">Sudah Bayar</option>
                    </select>
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
            document.getElementById('edit_kwh_awal').value = data.kwh_awal;
            document.getElementById('edit_kwh_akhir').value = data.kwh_akhir;
            document.getElementById('edit_status').value = data.status_bayar;
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