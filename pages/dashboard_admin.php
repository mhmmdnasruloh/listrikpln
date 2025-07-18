<?php
// pages/dashboard_admin.php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Get statistics
$totalPelanggan = executeQuery("SELECT COUNT(*) as total FROM users WHERE role = 'pelanggan'")->fetch()['total'];
$totalTagihan = executeQuery("SELECT SUM(total_tagihan) as total FROM penggunaan_listrik")->fetch()['total'];
$belumBayar = executeQuery("SELECT COUNT(*) as total FROM penggunaan_listrik WHERE status_bayar = 'belum_bayar'")->fetch()['total'];
$sudahBayar = executeQuery("SELECT COUNT(*) as total FROM penggunaan_listrik WHERE status_bayar = 'sudah_bayar'")->fetch()['total'];

// Get recent penggunaan
$recentPenggunaan = executeQuery("
    SELECT p.*, u.nama 
    FROM penggunaan_listrik p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pembayaran Listrik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <h1>Dashboard Administrator</h1>
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
        <div class="alert alert-success">
            Selamat datang, <?php echo $_SESSION['nama']; ?>!
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Pelanggan</h3>
                <div class="number"><?php echo $totalPelanggan; ?></div>
            </div>
            <div class="stat-card" style="background: #e74c3c;">
                <h3>Total Tagihan</h3>
                <div class="number">Rp <?php echo number_format($totalTagihan, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card" style="background: #f39c12;">
                <h3>Belum Bayar</h3>
                <div class="number"><?php echo $belumBayar; ?></div>
            </div>
            <div class="stat-card" style="background: #27ae60;">
                <h3>Sudah Bayar</h3>
                <div class="number"><?php echo $sudahBayar; ?></div>
            </div>
        </div>

        <div class="card">
            <h3>Penggunaan Listrik Terbaru</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Bulan</th>
                        <th>Total KWH</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPenggunaan as $row): ?>
                    <tr>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['bulan']; ?></td>
                        <td><?php echo $row['total_kwh']; ?> KWH</td>
                        <td>Rp <?php echo number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                        <td>
                            <span style="color: <?php echo $row['status_bayar'] == 'sudah_bayar' ? 'green' : 'red'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $row['status_bayar'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>