document.addEventListener('DOMContentLoaded', function() {
    // Dynamic expense form handling
    const expenseForm = document.querySelector('.expense-form form');
    if (expenseForm) {
        expenseForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const amount = parseFloat(formData.get('amount'));
            
            if (amount <= 0) {
                showNotification('Please enter a valid amount', 'error');
                return;
            }

            try {
                const response = await fetch('add_expense.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    showNotification('Expense added successfully', 'success');
                    addExpenseToList(result.expense);
                    this.reset();
                }
            } catch (error) {
                showNotification('Error adding expense', 'error');
            }
        });
    }

    // Category-based color coding
    const categoryColors = {
        groceries: '#2ecc71',
        utilities: '#3498db',
        entertainment: '#e74c3c',
        transport: '#f1c40f',
        other: '#95a5a6'
    };

    // Expense item animation and color coding
    function addExpenseToList(expense) {
        const expenseList = document.querySelector('.expense-items');
        const expenseItem = document.createElement('div');
        expenseItem.className = 'expense-item slide-in';
        
        expenseItem.innerHTML = `
            <div class="expense-date">${formatDate(expense.date)}</div>
            <div class="expense-details">
                <div class="expense-category" style="color: ${categoryColors[expense.category]}">
                    ${expense.category}
                </div>
                <div class="expense-description">${expense.description}</div>
            </div>
            <div class="expense-amount">$${parseFloat(expense.amount).toFixed(2)}</div>
        `;
        
        expenseList.prepend(expenseItem);
    }

    // Date formatting helper
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
    }

    // Notification system
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Search and filter functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'expense-search';
    searchInput.placeholder = 'Search expenses...';
    document.querySelector('.expense-list h2').after(searchInput);

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const expenseItems = document.querySelectorAll('.expense-item');
        
        expenseItems.forEach(item => {
            const description = item.querySelector('.expense-description').textContent.toLowerCase();
            const category = item.querySelector('.expense-category').textContent.toLowerCase();
            
            if (description.includes(searchTerm) || category.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Expense statistics
    function updateExpenseStats() {
        const expenses = document.querySelectorAll('.expense-item');
        const stats = {
            total: 0,
            byCategory: {}
        };

        expenses.forEach(expense => {
            const amount = parseFloat(expense.querySelector('.expense-amount').textContent.slice(1));
            const category = expense.querySelector('.expense-category').textContent;
            
            stats.total += amount;
            stats.byCategory[category] = (stats.byCategory[category] || 0) + amount;
        });

        displayExpenseStats(stats);
    }

    function displayExpenseStats(stats) {
        const statsContainer = document.createElement('div');
        statsContainer.className = 'expense-stats';
        statsContainer.innerHTML = `
            <h3>Expense Summary</h3>
            <div class="total-expenses">Total: $${stats.total.toFixed(2)}</div>
            <div class="category-breakdown">
                ${Object.entries(stats.byCategory).map(([category, amount]) => `
                    <div class="category-stat">
                        <span>${category}</span>
                        <span>$${amount.toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
        `;

        const existingStats = document.querySelector('.expense-stats');
        if (existingStats) {
            existingStats.remove();
        }
        document.querySelector('.expense-list').appendChild(statsContainer);
    }

    // Initialize stats
    updateExpenseStats();

    // Export functionality
    const exportBtn = document.createElement('button');
    exportBtn.className = 'export-btn';
    exportBtn.textContent = 'Export Expenses';
    document.querySelector('.expense-list h2').after(exportBtn);

    exportBtn.addEventListener('click', function() {
        const expenses = Array.from(document.querySelectorAll('.expense-item')).map(item => ({
            date: item.querySelector('.expense-date').textContent,
            category: item.querySelector('.expense-category').textContent,
            description: item.querySelector('.expense-description').textContent,
            amount: item.querySelector('.expense-amount').textContent
        }));

        const csv = [
            ['Date', 'Category', 'Description', 'Amount'],
            ...expenses.map(exp => [exp.date, exp.category, exp.description, exp.amount])
        ].map(row => row.join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'expenses.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
}); 