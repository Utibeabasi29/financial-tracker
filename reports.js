document.addEventListener('DOMContentLoaded', function() {
    // Initial Chart Setup (moved from reports.php)
    const categoryData = {
        labels: categoryLabels, // We'll define these variables in reports.php
        datasets: [{
            data: categoryValues,
            backgroundColor: [
                '#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6'
            ]
        }]
    };

    const trendData = {
        labels: trendLabels,
        datasets: [{
            label: 'Monthly Spending',
            data: trendValues,
            borderColor: '#3498db',
            tension: 0.1
        }]
    };

    // Create initial charts
    window.categoryChart = new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: categoryData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    window.trendChart = new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: trendData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Initialize date range picker with custom styling
    const dateInputs = document.querySelectorAll('.date-filter input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateReports();
        });
    });

    // Dynamic chart updates
    async function updateReports() {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;

        try {
            const response = await fetch(`get_report_data.php?start_date=${startDate}&end_date=${endDate}`);
            const data = await response.json();
            
            updateCategoryChart(data.categories);
            updateTrendChart(data.trends);
            updateBudgetComparison(data.budgets);
            
            showToast('Reports updated successfully', 'success');
        } catch (error) {
            showToast('Error updating reports', 'error');
        }
    }

    // Enhanced Category Chart with interactions
    function updateCategoryChart(data) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        
        if (window.categoryChart) {
            window.categoryChart.destroy();
        }

        window.categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: [
                        '#3498db', '#2ecc71', '#f1c40f', 
                        '#e74c3c', '#9b59b6', '#1abc9c'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: $${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });
    }

    // Interactive Trend Chart
    function updateTrendChart(data) {
        const ctx = document.getElementById('trendChart').getContext('2d');
        
        if (window.trendChart) {
            window.trendChart.destroy();
        }

        const gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
        gradientFill.addColorStop(0, 'rgba(52, 152, 219, 0.3)');
        gradientFill.addColorStop(1, 'rgba(52, 152, 219, 0)');

        window.trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Monthly Spending',
                    data: data.values,
                    borderColor: '#3498db',
                    backgroundColor: gradientFill,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3498db',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `$${context.raw.toFixed(2)}`;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    // Animated Budget Comparison
    function updateBudgetComparison(data) {
        const container = document.querySelector('.budget-comparison');
        container.innerHTML = '';

        data.forEach(item => {
            const percentage = (item.spent / item.budget) * 100;
            const barClass = percentage > 100 ? 'over' : 'under';

            const comparisonItem = document.createElement('div');
            comparisonItem.className = 'comparison-item';
            comparisonItem.innerHTML = `
                <div class="category-label">
                    ${item.category}
                    <span class="percentage ${barClass}">
                        ${percentage.toFixed(1)}%
                    </span>
                </div>
                <div class="comparison-bar">
                    <div class="budget-bar" style="width: 100%">
                        $${item.budget.toFixed(2)}
                    </div>
                    <div class="actual-bar ${barClass}" 
                         style="width: ${Math.min(100, percentage)}%">
                        $${item.spent.toFixed(2)}
                    </div>
                </div>
            `;

            container.appendChild(comparisonItem);
            
            // Animate bars
            setTimeout(() => {
                comparisonItem.querySelector('.actual-bar').style.opacity = '1';
            }, 100);
        });
    }

    // Export functionality for reports
    const exportButtons = {
        pdf: createExportButton('PDF', 'file-pdf', exportToPDF),
        excel: createExportButton('Excel', 'file-excel', exportToExcel),
        image: createExportButton('Image', 'image', exportToImage)
    };

    function createExportButton(text, icon, handler) {
        const button = document.createElement('button');
        button.className = 'export-btn';
        button.innerHTML = `<i class="fas fa-${icon}"></i> Export as ${text}`;
        button.addEventListener('click', handler);
        document.querySelector('.header').appendChild(button);
        return button;
    }

    async function exportToPDF() {
        const element = document.querySelector('.reports-grid');
        try {
            const canvas = await html2canvas(element);
            const pdf = new jsPDF('p', 'mm', 'a4');
            pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, 211, 298);
            pdf.save('financial_report.pdf');
            showToast('PDF exported successfully', 'success');
        } catch (error) {
            showToast('Error exporting PDF', 'error');
        }
    }

    function exportToExcel() {
        const data = gatherReportData();
        const ws = XLSX.utils.json_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Financial Report");
        XLSX.writeFile(wb, "financial_report.xlsx");
        showToast('Excel file exported successfully', 'success');
    }

    async function exportToImage() {
        try {
            const element = document.querySelector('.reports-grid');
            const canvas = await html2canvas(element);
            const link = document.createElement('a');
            link.download = 'financial_report.png';
            link.href = canvas.toDataURL();
            link.click();
            showToast('Image exported successfully', 'success');
        } catch (error) {
            showToast('Error exporting image', 'error');
        }
    }

    // Toast notification system
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Initialize reports
    updateReports();
}); 