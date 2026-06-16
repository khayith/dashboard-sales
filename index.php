<?php
include 'config/koneksi.php';

$filter_region = isset($_GET['region']) ? $_GET['region'] : 'All';
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'All';

$where_clauses = [];
if ($filter_region != 'All') {
    $where_clauses[] = "CountryRegion = '" . mysqli_real_escape_string($conn, $filter_region) . "'";
}
if ($filter_category != 'All') {
    $where_clauses[] = "Category = '" . mysqli_real_escape_string($conn, $filter_category) . "'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

$query_kpi = "SELECT SUM(REPLACE(Sales, ',', '.')) as total_sales, SUM(REPLACE(Profit, ',', '.')) as total_profit, COUNT(DISTINCT SalesOrderID) as total_orders FROM orders $where_sql";
$result_kpi = mysqli_query($conn, $query_kpi);
$data_kpi = mysqli_fetch_assoc($result_kpi);

$total_sales = isset($data_kpi['total_sales']) ? (float)$data_kpi['total_sales'] : 0.0;
$total_profit = isset($data_kpi['total_profit']) ? (float)$data_kpi['total_profit'] : 0.0;
$total_orders = isset($data_kpi['total_orders']) ? (int)$data_kpi['total_orders'] : 0;
$margin = $total_sales > 0 ? ($total_profit / $total_sales) * 100 : 0;

$query_chart1 = "SELECT Category, SUM(REPLACE(Sales, ',', '.')) as sales FROM orders $where_sql GROUP BY Category";
$result_chart1 = mysqli_query($conn, $query_chart1);
$categories = [];
$sales_data = [];
while($row = mysqli_fetch_assoc($result_chart1)) {
    $categories[] = $row['Category'];
    $sales_data[] = (float)$row['sales'];
}

$query_chart2 = "SELECT CountryRegion, SUM(REPLACE(Profit, ',', '.')) as profit FROM orders $where_sql GROUP BY CountryRegion ORDER BY profit DESC LIMIT 5";
$result_chart2 = mysqli_query($conn, $query_chart2);
$regions = [];
$profit_data = [];
while($row = mysqli_fetch_assoc($result_chart2)) {
    $regions[] = $row['CountryRegion'];
    $profit_data[] = (float)$row['profit'];
}

$query_chart3 = "SELECT LEFT(OrderDate, 7) as bulan, SUM(REPLACE(Sales, ',', '.')) as sales FROM orders $where_sql GROUP BY LEFT(OrderDate, 7) ORDER BY bulan ASC";
$result_chart3 = mysqli_query($conn, $query_chart3);
$months = [];
$monthly_sales = [];
while($row = mysqli_fetch_assoc($result_chart3)) {
    if (!empty($row['bulan']) && $row['bulan'] != '') {
        $months[] = $row['bulan'];
        $monthly_sales[] = (float)$row['sales'];
    }
}

$query_chart4 = "SELECT ProductName, SUM(Qty + 0) as total_qty FROM orders $where_sql GROUP BY ProductName ORDER BY total_qty DESC LIMIT 5";
$result_chart4 = mysqli_query($conn, $query_chart4);
$products = [];
$qty_data = [];
while($row = mysqli_fetch_assoc($result_chart4)) {
    if (!empty($row['ProductName'])) {
        $products[] = $row['ProductName'];
        $qty_data[] = (int)$row['total_qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analisis Performa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="navbar">
        <div class="logo"><i data-lucide="layout-dashboard"></i> Sales Dashboard</div>
        <div class="nav-tabs">
            <button class="tab-btn active" onclick="switchTab('ai-dashboard')">
                <i data-lucide="bot" style="width:18px;height:18px;"></i> Dashboard
            </button>
            <button class="tab-btn" onclick="switchTab('tableau-dashboard')">
                <i data-lucide="bar-chart-3" style="width:18px;height:18px;"></i> Tableau
            </button>
        </div>
    </header>

    <main class="container">
        
        <section class="filters" id="filter-section">
            <div class="filters-wrapper">
                <form method="GET" action="" id="filterForm" style="display: flex; gap: 15px;">
                    <select name="region" onchange="document.getElementById('filterForm').submit()">
                        <option value="All" <?php if($filter_region == 'All') echo 'selected'; ?>>Region: All</option>
                        <option value="United States" <?php if($filter_region == 'United States') echo 'selected'; ?>>United States</option>
                        <option value="Australia" <?php if($filter_region == 'Australia') echo 'selected'; ?>>Australia</option>
                        <option value="Canada" <?php if($filter_region == 'Canada') echo 'selected'; ?>>Canada</option>
                        <option value="France" <?php if($filter_region == 'France') echo 'selected'; ?>>France</option>
                        <option value="Germany" <?php if($filter_region == 'Germany') echo 'selected'; ?>>Germany</option>
                        <option value="United Kingdom" <?php if($filter_region == 'United Kingdom') echo 'selected'; ?>>United Kingdom</option>
                    </select>

                    <select name="category" onchange="document.getElementById('filterForm').submit()">
                        <option value="All" <?php if($filter_category == 'All') echo 'selected'; ?>>Category: All</option>
                        <option value="Bikes" <?php if($filter_category == 'Bikes') echo 'selected'; ?>>Bikes</option>
                        <option value="Accessories" <?php if($filter_category == 'Accessories') echo 'selected'; ?>>Accessories</option>
                        <option value="Clothing" <?php if($filter_category == 'Clothing') echo 'selected'; ?>>Clothing</option>
                    </select>
                </form>
            </div>
        </section>

        <div id="ai-dashboard" class="tab-content active">
            
            <div class="section-title">SALES ANALYTICS</div>

            <section class="kpi-grid">
                <div class="kpi-card">
                    <h3>Total Sales</h3>
                    <p class="kpi-value">$<?php echo number_format($total_sales / 1000000, 2); ?>M</p>
                </div>
                <div class="kpi-card">
                    <h3>Total Profit</h3>
                    <p class="kpi-value">$<?php echo number_format($total_profit / 1000000, 2); ?>M</p>
                </div>
                <div class="kpi-card">
                    <h3>Profit Margin</h3>
                    <p class="kpi-value" style="color: var(--success);"><?php echo number_format($margin, 2); ?>%</p>
                </div>
                <div class="kpi-card">
                    <h3>Total Orders</h3>
                    <p class="kpi-value"><?php echo number_format($total_orders); ?></p>
                </div>
            </section>

            <section class="anomaly-section">
                <div class="anomaly-header">
                    <div class="anomaly-title-flex">
                        <i data-lucide="alert-triangle" style="color: var(--danger)"></i>
                        <span>Anomali Terdeteksi</span>
                    </div>
                    <span class="badge red">Kritis</span>
                </div>
                <div class="anomaly-body">
                    <ul>
                        <li><strong>Kebocoran Profit: Sub-kategori "Caps"</strong> <br> Margin Negatif | Kerugian sebesar -$1.2K terdeteksi meskipun mencetak sales $51.2K. <br><em style="color: var(--text-muted)">Rekomendasi: Evaluasi ulang biaya produksi atau hentikan promosi pada sub-kategori ini.</em></li>
                        <li><strong>Insight Bisnis:</strong> Kategori "Bikes" mendominasi 97% dari total pendapatan. Ketergantungan pada satu kategori sangat tinggi. Disarankan untuk melakukan cross-selling dengan produk "Accessories".</li>
                    </ul>
                </div>
            </section>

            <section class="charts-grid">
                <div class="chart-card">
                    <h3>Sales berdasarkan Kategori</h3>
                    <canvas id="salesCategoryChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Profit per Negara (Top 5)</h3>
                    <canvas id="profitRegionChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Tren Penjualan Bulanan</h3>
                    <canvas id="monthlySalesChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Top 5 Produk Terlaris (Qty)</h3>
                    <canvas id="topProductsChart"></canvas>
                </div>
            </section>

            <div class="section-title" style="margin-top: 40px;">RESOLUTION • INSIGHT & REKOMENDASI</div>
            <section class="resolution-grid">
                
                <div class="ai-chat-card">
                    <div class="ai-chat-header">
                        <span style="display:flex; align-items:center; gap:8px;"> Tanya AI Asisten</span>
                        <span class="badge gray">gemma3:latest</span>
                    </div>
                    <div class="ai-chat-body" id="ai-response-box">
                        <em style="color: #94a3b8;">Silakan pilih salah satu topik di bawah untuk meminta insight dari AI...</em>
                    </div>
                    <div class="ai-chat-footer">
                        <div class="input-group">
                            <input type="text" id="ai-input" placeholder="Pilih topik di bawah ini..." readonly style="background-color: #f8fafc; cursor: default;">
                            <button class="btn-primary" id="btn-minta-insight" onclick="simulateAI()" disabled style="opacity: 0.5; cursor: not-allowed;">
                                <span>Minta Insight</span> <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                        
                        <div class="quick-topics" style="margin-top: 15px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <span style="font-size: 13px; color: #64748b;">Topik:</span>
                            <button type="button" class="btn-topic" onclick="selectTopic('Prioritas profit?')" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 20px; background: white; cursor: pointer; font-size: 13px; color: #334155;">Prioritas profit?</button>
                            <button type="button" class="btn-topic" onclick="selectTopic('Masalah region?')" style="padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 20px; background: white; cursor: pointer; font-size: 13px; color: #334155;">Masalah region?</button>
                            <button type="button" class="btn-topic" onclick="selectTopic('Berikan rekomendasi taktis untuk bulan depan!')" style="padding: 6px 14px; border: 1px solid #4f46e5; color: #4f46e5; border-radius: 20px; background: #f5f3ff; cursor: pointer; font-size: 13px; font-weight: 500;">Rekomendasi?</button>
                        </div>
                    </div>
                </div>

                <div class="summary-table-card">
                    <h3 style="margin-bottom: 20px; font-size: 15px; font-weight: 600;">Ringkasan Performa per Kategori</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>KATEGORI</th>
                                <th class="text-right">SALES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result_table = mysqli_query($conn, "SELECT Category, SUM(REPLACE(Sales, ',', '.')) as s FROM orders $where_sql GROUP BY Category");
                            while($t_row = mysqli_fetch_assoc($result_table)) {
                                echo "<tr>";
                                echo "<td style='font-weight: 500;'>".$t_row['Category']."</td>";
                                echo "<td class='text-right text-blue'>$".number_format((float)$t_row['s'], 2)."</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </section>
        </div>

        <div id="tableau-dashboard" class="tab-content">
            <div class="tableau-header-wrapper">
                <h2>Dashboard Sales</h2>
                <a href="https://public.tableau.com/views/Sales_17814319315840/Dashboard3" target="_blank" class="btn-external">
                    Buka di Tableau Public
                </a>
            </div>
            <iframe 
                src="https://public.tableau.com/views/Sales_17814319315840/Dashboard3?:embed=yes&:showVizHome=no&:display_count=yes&:origin=viz_share_link" 
                width="100%" 
                height="850px" 
                frameborder="0"
                style="border-radius: 12px; border: 1px solid var(--border-color);">
            </iframe >
        </div>

    </main>

    <script>
        const phpCategories = <?php echo json_encode($categories); ?>;
        const phpSalesData = <?php echo json_encode($sales_data); ?>;
        const phpRegions = <?php echo json_encode($regions); ?>;
        const phpProfitData = <?php echo json_encode($profit_data); ?>;
        const phpMonths = <?php echo json_encode($months); ?>;
        const phpMonthlySales = <?php echo json_encode($monthly_sales); ?>;
        const phpProducts = <?php echo json_encode($products); ?>;
        const phpQtyData = <?php echo json_encode($qty_data); ?>;
    </script>
    
    <script src="script.js"></script>
    <script>
        if(window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>