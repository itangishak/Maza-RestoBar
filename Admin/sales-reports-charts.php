<!-- Sales Distribution Chart -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sales Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="salesDistributionChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profit/Loss Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="profitTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare data for charts
    const reportData = <?php echo json_encode($reportData); ?>;
    
    // Prepare labels based on report type
    const labels = reportData.map(item => {
        <?php if ($reportType == 'daily'): ?>
            return new Date(item.date).toLocaleDateString();
        <?php elseif ($reportType == 'monthly'): ?>
            return item.month;
        <?php else: ?>
            return item.year.toString();
        <?php endif; ?>
    });
    
    // Sales Distribution Chart
    const ctxDistribution = document.getElementById('salesDistributionChart').getContext('2d');
    new Chart(ctxDistribution, {
        type: 'pie',
        data: {
            labels: ['Menu Sales', 'Drink Sales', 'Buffet Sales'],
            datasets: [{
                data: [
                    <?php echo $totalMenuSales; ?>,
                    <?php echo $totalDrinkSales; ?>,
                    <?php echo $totalBuffetSales; ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    
    // Profit Trend Chart
    const ctxProfit = document.getElementById('profitTrendChart').getContext('2d');
    new Chart(ctxProfit, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: reportData.map(item => item.total_sales),
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                fill: true
            }, {
                label: 'Expenses',
                data: reportData.map(item => item.expenses),
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 2,
                fill: true
            }, {
                label: 'Profit',
                data: reportData.map(item => item.profit),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'transparent',
                borderWidth: 3,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Export to Excel function
    function exportToExcel() {
        // Create a CSV content
        let csv = 'data:text/csv;charset=utf-8,';
        
        // Add headers
        const headers = ['<?php echo ($reportType == "daily") ? "Date" : (($reportType == "monthly") ? "Month" : "Year"); ?>', 'Menu Sales', 'Drink Sales', 'Buffet Sales', 'Total Sales', 'Expenses', 'Profit'];
        csv += headers.join(',') + '\r\n';
        
        // Add rows
        reportData.forEach(row => {
            let rowData = [];
            <?php if ($reportType == 'daily'): ?>
                rowData.push(new Date(row.date).toLocaleDateString());
            <?php elseif ($reportType == 'monthly'): ?>
                rowData.push(row.month + ' ' + row.year);
            <?php else: ?>
                rowData.push(row.year);
            <?php endif; ?>
            
            rowData.push(row.menu_sales);
            rowData.push(row.drink_sales);
            rowData.push(row.buffet_sales);
            rowData.push(row.total_sales);
            rowData.push(row.expenses);
            rowData.push(row.profit);
            
            csv += rowData.join(',') + '\r\n';
        });
        
        // Create download link
        const encodedUri = encodeURI(csv);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', '<?php echo strtolower(str_replace(' ', '_', $reportTitle)); ?>.csv');
        document.body.appendChild(link);
        
        // Download the file
        link.click();
        
        // Clean up
        document.body.removeChild(link);
    }
</script>

<?php include '../includes/footer.php'; ?>
