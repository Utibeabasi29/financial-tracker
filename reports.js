document.addEventListener('DOMContentLoaded', function() {
    const categoryData = {
        labels: [],
        datasets: [{
            data: [],
                    backgroundColor: [
                '#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6'
            ]
                }]
    };

    const trendData = {
        labels: [],
        datasets: [{
            label: 'Monthly Spending',
            data: [],
            borderColor: '#3498db',
            tension: 0.1
        }]
    };

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

    const dateInputs = document.querySelectorAll('.date-filter input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', updateReports);
    });

    async function updateReports() {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;

        if (!startDate || !endDate) {
            showToast('Please select both start and end dates', 'error');
            return;
        }
        try {
            const response = await fetch(`api/get_report_data.php?start_date=${startDate}&end_date=${endDate}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            if (!data || !data.categories || !data.trends || !data.budgets) {
                throw new Error('Invalid data format received');
            }
            
            updateCategoryChart(data.categories);
            updateTrendChart(data.trends);
            updateBudgetComparison(data.budgets);
            showToast('Reports updated successfully', 'success');
        } catch (error) {
            console.error('Error updating reports:', error);
            showToast('Error updating reports: ' + error.message, 'error');
        }
    }

    function updateCategoryChart(data) {
        if (!data.labels || !data.values) return;
        
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

    function updateTrendChart(data) {
        if (!data.labels || !data.values) return;
        
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

    function updateBudgetComparison(data) {
        if (!Array.isArray(data)) return;
        
        const container = document.querySelector('.budget-comparison');
        if (!container) return;
        
        container.innerHTML = '';

        data.forEach(item => {
            if (!item.category || !item.budget || !item.spent) return;
            
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
        setTimeout(() => {
                comparisonItem.querySelector('.actual-bar').style.opacity = '1';
            }, 100);
}); 
    }

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

    setTimeout(updateReports, 500);
});