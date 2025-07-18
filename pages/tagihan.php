<?php
// pages/tagihan.php
require_once '../config/database.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$message = '';

// Handle payment simulation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar'])) {
    $id = $_POST['id'];
    executeQuery("UPDATE penggunaan_listrik SET status_bayar = 'sudah_bayar' WHERE id = ?", [$id]);
    $message = 'Pembayaran berhasil! Terima kasih.';
}

// Get user's tagihan
$tagihan = executeQuery("
    SELECT * FROM penggunaan_listrik 
    WHERE user_id = ? 
    ORDER BY bulan DESC
", [$_SESSION['user_id']])->fetchAll();

$totalBelumBayar = 0;
$totalSudahBayar = 0;

foreach ($tagihan as $row) {
    if ($row['status_bayar'] == 'belum_bayar') {
        $totalBelumBayar += $row['total_tagihan'];
    } else {
        $totalSudahBayar += $row['total_tagihan'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan - Pembayaran Listrik</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <h1>Tagihan Listrik</h1>
        <div class="nav">
            <ul>
                <li><a href="dashboard_pelanggan.php">Dashboard</a></li>
                <li><a href="tagihan.php">Tagihan</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="dashboard-stats">
            <div class="stat-card" style="background: #e74c3c;">
                <h3>Total Belum Bayar</h3>
                <div class="number">Rp <?php echo number_format($totalBelumBayar, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card" style="background: #27ae60;">
                <h3>Total Sudah Bayar</h3>
                <div class="number">Rp <?php echo number_format($totalSudahBayar, 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- Tagihan Belum Bayar -->
        <?php 
        $belumBayar = array_filter($tagihan, function($row) {
            return $row['status_bayar'] == 'belum_bayar';
        });
        ?>
        
        <?php if (!empty($belumBayar)): ?>
        <div class="card">
            <h3 style="color: #e74c3c;">Tagihan Belum Bayar</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>KWH Awal</th>
                        <th>KWH Akhir</th>
                        <th>Total KWH</th>
                        <th>Tarif/KWH</th>
                        <th>Total Tagihan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($belumBayar as $row): ?>
                    <tr>
                        <td><?php echo $row['bulan']; ?></td>
                        <td><?php echo $row['kwh_awal']; ?></td>
                        <td><?php echo $row['kwh_akhir']; ?></td>
                        <td><?php echo $row['total_kwh']; ?> KWH</td>
                        <td>Rp <?php echo number_format($row['tarif_per_kwh'], 0, ',', '.'); ?></td>
                        <td><strong>Rp <?php echo number_format($row['total_tagihan'], 0, ',', '.'); ?></strong></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="bayar" class="btn btn-success" style="padding: 5px 15px;" onclick="return confirm('Yakin ingin membayar tagihan ini?')">Bayar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Riwayat Pembayaran -->
        <?php 
        $sudahBayar = array_filter($tagihan, function($row) {
            return $row['status_bayar'] == 'sudah_bayar';
        });
        ?>
        
        <?php if (!empty($sudahBayar)): ?>
        <div class="card">
            <h3 style="color: #27ae60;">Riwayat Pembayaran</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Total KWH</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
                        <th>Tanggal Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sudahBayar as $row): ?>
                    <tr>
                        <td><?php echo $row['bulan']; ?></td>
                        <td><?php echo $row['total_kwh']; ?> KWH</td>
                        <td>Rp <?php echo number_format($row['total_tagihan'], 0, ',', '.'); ?></td>
                        <td><span style="color: green;">Sudah Bayar</span></td>
                        <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (empty($tagihan)): ?>
        <div class="card">
            <p style="text-align: center; color: #666;">Belum ada data tagihan.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>