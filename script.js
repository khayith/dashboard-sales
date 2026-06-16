function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    if (event && event.currentTarget) event.currentTarget.classList.add('active');

    const filterSection = document.getElementById('filter-section');
    if (filterSection) {
        if (tabId === 'tableau-dashboard') {
            filterSection.style.display = 'none';
        } else {
            filterSection.style.display = 'block';
        }
    }
}

function selectTopic(topicText) {
    const inputField = document.getElementById('ai-input');
    const submitBtn = document.getElementById('btn-minta-insight');
    inputField.value = topicText;
    submitBtn.disabled = false;
    submitBtn.style.opacity = "1";
    submitBtn.style.cursor = "pointer";
}

function simulateAI() {
    const responseBox = document.getElementById('ai-response-box');
    const inputField = document.getElementById('ai-input');
    const submitBtn = document.getElementById('btn-minta-insight');
    const selectedTopic = inputField.value.trim();
    if(selectedTopic === "") return; 
    responseBox.innerHTML = '<em style="color: #94a3b8;">Menganalisis basis data terintegrasi berdasarkan topik...</em>';
    submitBtn.disabled = true;
    submitBtn.style.opacity = "0.5";
    submitBtn.style.cursor = "not-allowed";
    
    setTimeout(() => {
        if (selectedTopic === 'Prioritas profit?') {
            responseBox.innerHTML = `
                <p><strong>Analisis AI: Prioritas Profitabilitas</strong></p>
                <ul style="margin-top: 10px; padding-left: 20px; line-height: 1.6;">
                    <li>Kategori <strong>Bikes</strong> menghasilkan rasio profit tertinggi mencapai 97% dari total margin saat ini.</li>
                    <li>Sub-kategori <strong>Caps</strong> mengalami kebocoran anggaran dengan kerugian bersih sebesar -$1.2K akibat tingginya biaya logistik.</li>
                    <li><strong style="color: #4f46e5;">Rekomendasi:</strong> Alokasikan 60% anggaran operasional berikutnya untuk memperkuat inventaris kategori Bikes dan kurangi subsidi produksi untuk produk Caps.</li>
                </ul>
            `;
        } 
        else if (selectedTopic === 'Masalah region?') {
            responseBox.innerHTML = `
                <p><strong>Analisis AI: Evaluasi Wilayah/Region</strong></p>
                <ul style="margin-top: 10px; padding-left: 20px; line-height: 1.6;">
                    <li><strong>Amerika Serikat & Australia</strong> memimpin sebagai dua wilayah penyumbang keuntungan bersih terbesar secara global.</li>
                    <li>Pasar wilayah <strong>Prancis & Jerman</strong> stagnan, dengan pertumbuhan volume transaksi di bawah 2% dalam tiga bulan terakhir.</li>
                    <li><strong style="color: #4f46e5;">Rekomendasi:</strong> Lakukan audit pasar regional Eropa barat untuk melihat apakah terdapat kendala regulasi atau masalah efisiensi rantai pasok lokal.</li>
                </ul>
            `;
        } 
        else if (selectedTopic === 'Berikan rekomendasi taktis untuk bulan depan!') {
            responseBox.innerHTML = `
                <p><strong>Analisis AI: Strategi Taktis Bulan Depan</strong></p>
                <ul style="margin-top: 10px; padding-left: 20px; line-height: 1.6;">
                    <li><strong>Cross-Selling:</strong> Manfaatkan dominasi kategori <em>Bikes</em> dengan membuat bundling paket aksesoris (misal: gratis botol minum atau diskon helm 20%).</li>
                    <li><strong>Targeting Pemulihan Margin:</strong> Mengurangi program promosi berbiaya tinggi di region dengan margin laba tipis seperti Prancis.</li>
                    <li><strong style="color: #4f46e5;">Rekomendasi Utama:</strong> Luncurkan kampanye pemasaran khusus akhir pekan berfokus pada pasar Australia yang memiliki performa tren positif stabil.</li>
                </ul>
            `;
        } 
        else {
            responseBox.innerHTML = `<p>Analisis umum untuk topik <em>"${selectedTopic}"</em>: Silakan evaluasi metrik penjualan utama pada grafik pencapaian di atas.</p>`;
        }

        inputField.value = ""; 
    }, 1500);
}

document.addEventListener('DOMContentLoaded', function() {

    const ctxSales = document.getElementById('salesCategoryChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'bar',
        data: {
            labels: phpCategories,
            datasets: [{
                label: 'Total Sales ($)',
                data: phpSalesData,
                backgroundColor: '#3b82f6', 
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const ctxProfit = document.getElementById('profitRegionChart').getContext('2d');
    new Chart(ctxProfit, {
        type: 'bar',
        data: {
            labels: phpRegions,
            datasets: [{
                label: 'Total Profit ($)',
                data: phpProfitData,
                backgroundColor: '#22c55e', 
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', 
            responsive: true,
            scales: {
                x: { beginAtZero: true }
            }
        }
    });

    const ctxMonthly = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: phpMonths,
            datasets: [{
                label: 'Total Penjualan ($)',
                data: phpMonthlySales,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const ctxProducts = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctxProducts, {
        type: 'bar',
        data: {
            labels: phpProducts,
            datasets: [{
                label: 'Total Qty Terjual',
                data: phpQtyData,
                backgroundColor: '#a855f7',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
});