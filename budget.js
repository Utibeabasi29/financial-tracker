document.addEventListener('DOMContentLoaded', function() {
    // Budget form handling with real-time validation
    const budgetForm = document.querySelector('.budget-form form');
    if (budgetForm) {
        budgetForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Debug form data
            console.log('Form data:', {
                category: formData.get('category'),
                amount: formData.get('amount'),
                month: formData.get('month')
            });

            try {
                const response = await fetch('update_budget.php', {
                    method: 'POST',
                    body: formData
                });

                // Debug response
                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Raw response:', responseText);

                // Parse JSON manually
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Server response not in JSON format');
                }

                if (result.success) {
                    showToast('Budget updated successfully', 'success');
                    updateBudgetCard(result.budget);
                    this.reset();
                } else {
                    throw new Error(result.message || 'Server returned error');
                }
            } catch (error) {
                console.error('Budget update error:', error);
                showToast(error.message, 'error');
            }
        });
    }

    // Add updateBudgetCard function if missing
    function updateBudgetCard(budget) {
        const card = document.querySelector(`[data-category="${budget.category}"]`);
        if (card) {
            card.dataset.budget = budget.amount;
            // Update card display
            const progressBar = card.querySelector('.progress');
            if (progressBar) {
                const spent = parseFloat(card.dataset.spent);
                const percentage = (spent / budget.amount) * 100;
                progressBar.style.width = `${Math.min(100, percentage)}%`;
            }
        }
    }

    // Interactive budget cards
    const budgetCards = document.querySelectorAll('.budget-card');
    budgetCards.forEach(card => {
        // Add hover effect for more details
        card.addEventListener('mouseenter', function() {
            const spent = parseFloat(this.dataset.spent);
            const budget = parseFloat(this.dataset.budget);
            const remaining = budget - spent;
            
            const detailsPopup = document.createElement('div');
            detailsPopup.className = 'budget-details-popup';
            detailsPopup.innerHTML = `
                <div>Budget: $${budget.toFixed(2)}</div>
                <div>Spent: $${spent.toFixed(2)}</div>
                <div>Remaining: $${remaining.toFixed(2)}</div>
            `;
            
            this.appendChild(detailsPopup);
        });

        card.addEventListener('mouseleave', function() {
            const popup = this.querySelector('.budget-details-popup');
            if (popup) popup.remove();
        });

        // Quick edit functionality
        card.addEventListener('dblclick', function() {
            const category = this.dataset.category;
            const currentBudget = this.dataset.budget;
            
            const input = document.createElement('input');
            input.type = 'number';
            input.value = currentBudget;
            input.className = 'quick-edit-input';
            
            const originalContent = this.innerHTML;
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            input.addEventListener('blur', async function() {
                const newAmount = this.value;
                try {
                    const response = await fetch('update_budget.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            category: category,
                            amount: newAmount
                        })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        updateBudgetCard(result.budget);
                    }
                } catch (error) {
                    card.innerHTML = originalContent;
                    showToast('Error updating budget', 'error');
                }
            });
        });
    });

    // Budget progress animation
    function animateBudgetProgress() {
        const progressBars = document.querySelectorAll('.progress-bar .progress');
        progressBars.forEach(bar => {
            const targetWidth = bar.dataset.progress + '%';
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, 100);
        });
    }

    // Budget alerts system
    function checkBudgetAlerts() {
        const budgetCards = document.querySelectorAll('.budget-card');
        budgetCards.forEach(card => {
            const spent = parseFloat(card.dataset.spent);
            const budget = parseFloat(card.dataset.budget);
            const percentage = (spent / budget) * 100;
            
            if (percentage >= 90) {
                showBudgetAlert(card.dataset.category, percentage);
            }
        });
    }

    function showBudgetAlert(category, percentage) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'budget-alert animated';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            Warning: ${category} budget is at ${percentage.toFixed(1)}%
        `;
        
        document.querySelector('.budget-overview').prepend(alertDiv);
    }

    // Budget comparison chart
    function initializeBudgetChart() {
        const ctx = document.getElementById('budgetComparisonChart').getContext('2d');
        const budgetData = Array.from(document.querySelectorAll('.budget-card')).map(card => ({
            category: card.dataset.category,
            budget: parseFloat(card.dataset.budget),
            spent: parseFloat(card.dataset.spent)
        }));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: budgetData.map(d => d.category),
                datasets: [
                    {
                        label: 'Budget',
                        data: budgetData.map(d => d.budget),
                        backgroundColor: 'rgba(52, 152, 219, 0.5)'
                    },
                    {
                        label: 'Spent',
                        data: budgetData.map(d => d.spent),
                        backgroundColor: 'rgba(231, 76, 60, 0.5)'
                    }
                ]
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
    }

    // Toast notification system
    function showToast(message, type) {
        // Remove any existing toasts first
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Show after brief delay
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Export budget report
    const exportButton = document.createElement('button');
    exportButton.className = 'export-button';
    exportButton.innerHTML = '<i class="fas fa-download"></i> Export Budget Report';
    document.querySelector('.budget-overview').appendChild(exportButton);

    exportButton.addEventListener('click', function() {
        const budgetData = Array.from(document.querySelectorAll('.budget-card')).map(card => ({
            category: card.dataset.category,
            budget: card.dataset.budget,
            spent: card.dataset.spent,
            remaining: (parseFloat(card.dataset.budget) - parseFloat(card.dataset.spent)).toFixed(2)
        }));

        const csv = [
            ['Category', 'Budget', 'Spent', 'Remaining'],
            ...budgetData.map(d => [d.category, d.budget, d.spent, d.remaining])
        ].map(row => row.join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'budget_report.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });

    // Add delete button handlers
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            if (confirm('Are you sure you want to delete this budget?')) {
                const category = this.dataset.category;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('category', category);

                try {
                    const response = await fetch('update_budget.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        showToast('Budget deleted successfully', 'success');
                        this.closest('.budget-card').remove();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    showToast('Error deleting budget', 'error');
                }
            }
        });
    });

    // Delete button functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this budget?')) {
                return;
            }

            const category = this.dataset.category;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('category', category);

            try {
                const response = await fetch('update_budget.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showToast('Budget deleted successfully', 'success');
                    // Remove the budget card from DOM
                    this.closest('.budget-card').remove();
                } else {
                    throw new Error(result.message || 'Error deleting budget');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Error deleting budget', 'error');
            }
        });
    });

    // Quick edit functionality
    document.querySelectorAll('.budget-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking delete button
            if (e.target.closest('.delete-btn')) return;
            
            const category = this.dataset.category;
            const currentAmount = this.querySelector('.budget-amount').textContent.replace('$', '');
            
            // Create input field
            const input = document.createElement('input');
            input.type = 'number';
            input.step = '0.01';
            input.value = currentAmount;
            input.className = 'quick-edit-input';
            
            // Save original content
            const originalContent = this.innerHTML;
            
            // Replace content with input
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            // Handle save on blur or enter
            input.addEventListener('blur', handleSave);
            input.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    this.blur();
                }
                if (e.key === 'Escape') {
                    card.innerHTML = originalContent;
                }
            });
            
            async function handleSave() {
                const newAmount = input.value;
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('category', category);
                formData.append('amount', newAmount);
                
                try {
                    const response = await fetch('update_budget.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        showToast('Budget updated successfully', 'success');
                        window.location.reload();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('Update error:', error);
                    showToast('Error updating budget', 'error');
                    card.innerHTML = originalContent;
                }
            }
        });
    });

    // Initialize features
    animateBudgetProgress();
    checkBudgetAlerts();
    initializeBudgetChart();
});

function deleteBudget(category, month) {
    if (confirm('Are you sure you want to delete this budget?')) {
        // Create FormData object
        const formData = new FormData();
        formData.append('category', category);
        formData.append('month', month);

        fetch('delete_budget.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const budgetCard = document.querySelector(`.budget-card[data-category="${category}"]`);
                if (budgetCard) {
                    budgetCard.remove();
                }
                alert('Budget deleted successfully');
            } else {
                console.error('Delete error:', data.error);
                alert('Failed to delete budget: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the budget');
        });
    }
}