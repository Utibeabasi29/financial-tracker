document.addEventListener('DOMContentLoaded', function() {
    // Goal progress animation
    const progressBars = document.querySelectorAll('.progress');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });

    // Real-time form validation
    const goalForm = document.querySelector('.goals-form form');
    if (goalForm) {
        goalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const targetAmount = parseFloat(formData.get('target_amount'));
            const currentAmount = parseFloat(formData.get('current_amount'));
            const targetDate = new Date(formData.get('target_date'));
            const today = new Date();

            let isValid = true;
            let errorMessage = '';

            if (currentAmount > targetAmount) {
                isValid = false;
                errorMessage = 'Current amount cannot be greater than target amount';
            }

            if (targetDate < today) {
                isValid = false;
                errorMessage = 'Target date must be in the future';
            }

            if (isValid) {
                try {
                    const response = await fetch('add_goal.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        showFeedback('Goal added successfully!', 'success');
                        this.reset();
                        refreshGoals();
                    } else {
                        showFeedback(result.message || 'Error adding goal', 'error');
                    }
                } catch (error) {
                    showFeedback('Error adding goal', 'error');
                }
            } else {
                showFeedback(errorMessage, 'error');
            }
        });
    }

    // Dynamic goal updates
    const updateForms = document.querySelectorAll('.update-form');
    updateForms.forEach(form => {
        const input = form.querySelector('input[name="current_amount"]');
        const originalValue = input.value;

        input.addEventListener('change', function() {
            const newValue = parseFloat(this.value);
            const targetAmount = parseFloat(form.closest('.goal-card')
                .querySelector('.progress-numbers')
                .lastElementChild.textContent.replace(/[^0-9.]/g, ''));

            if (newValue > targetAmount) {
                this.value = originalValue;
                showError('Update amount cannot exceed target amount');
                return;
            }

            form.submit();
        });
    });

    // Error message handling
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        document.querySelector('.goals-form').prepend(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 3000);
    }

    // Goal completion celebration
    function celebrateGoalCompletion(goalCard) {
        goalCard.classList.add('goal-completed');
        
        // Create confetti effect
        const colors = ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'];
        for (let i = 0; i < 50; i++) {
            createConfetti(colors[Math.floor(Math.random() * colors.length)]);
        }
    }

    function createConfetti(color) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.backgroundColor = color;
        confetti.style.left = Math.random() * 100 + 'vw';
        document.body.appendChild(confetti);

        setTimeout(() => {
            confetti.remove();
        }, 2000);
    }

    // Check for completed goals
    const goalCards = document.querySelectorAll('.goal-card');
    goalCards.forEach(card => {
        const progress = parseFloat(card.querySelector('.goal-status').firstElementChild.textContent);
        if (progress >= 100) {
            celebrateGoalCompletion(card);
        }
    });

    function showFeedback(message, type) {
        const feedback = document.createElement('div');
        feedback.className = `alert alert-${type} fade-in`;
        feedback.textContent = message;

        const container = document.querySelector('.goals-container');
        container.insertBefore(feedback, container.firstChild);

        setTimeout(() => {
            feedback.remove();
        }, 3000);
    }

    async function refreshGoals() {
        try {
            const response = await fetch('get_goals.php');
            const goals = await response.json();
            updateGoalsDisplay(goals);
        } catch (error) {
            console.error('Error fetching goals:', error);
        }
    }

    function updateGoalsDisplay(goals) {
        const goalsGrid = document.querySelector('.goals-grid');
        if (!goalsGrid) return;

        goalsGrid.innerHTML = goals.length ? goals.map(goal => `
            <div class="goal-card fade-in">
                <div class="goal-header">
                    <h3>${goal.name}</h3>
                    <span class="goal-date">
                        Target: ${new Date(goal.target_date).toLocaleDateString()}
                        <br>
                        Created: ${new Date(goal.created_at).toLocaleDateString()}
                    </span>
                </div>
                <div class="goal-progress">
                    <div class="progress-bar">
                        <div class="progress" style="width: ${(goal.current_amount / goal.target_amount * 100)}%"></div>
                    </div>
                    <div class="progress-numbers">
                        <span>$${goal.current_amount.toFixed(2)}</span>
                        <span>of $${goal.target_amount.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `).join('') : '<p class="no-goals-message">No financial goals found. Add one to get started!</p>';
    }

    // Initial load of goals
    refreshGoals();
});