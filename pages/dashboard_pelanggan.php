<?php
// pages/dashboard_pelanggan.php
require_once '../config/database.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

// Get user's penggunaan
$penggunaan = executeQuery("
    SELECT * FROM penggunaan_listrik 
    WHERE user_id = ? 
    ORDER BY bulan DESC
", [$_SESSION['user_id']])->fetchAll();

$totalTagihan = executeQuery("
    SELECT SUM(total_tagihan) as total 
    FROM penggunaan_listrik 
    WHERE user_id = ? AND status_bayar = 'belum_bayar'
", [$_SESSION['user_id']])->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Pembayaran Listrik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <h1>Dashboard Pelanggan</h1>
        <div class="nav">
            <ul>
                <li><a href="dashboard_pelanggan.php">Dashboard</a></li>
                <li><a href="tagihan.php">Tagihan</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="container">
        <div class="alert alert-success">
            Selamat datang, <?php echo $_SESSION['nama']; ?>!
        </div>

        <div class="dashboard-stats">
            <div class="stat-card" style="background: #e74c3c;">
                <h3>Total Tagihan Belum Bayar</h3>
                <div class="number">Rp <?php echo number_format($totalTagihan ?: 0, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card" style="background: #f39c12;">
                <h3>Total Riwayat</h3>
                <div class="number"><?php echo count($penggunaan); ?></div>
            </div>
        </div>

        <div class="card">
            <h3>Riwayat Penggunaan Listrik</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>KWH Awal</th>
                        <th>KWH Akhir</th>
                        <th>Total KWH</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penggunaan as $row): ?>
                    <tr>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>