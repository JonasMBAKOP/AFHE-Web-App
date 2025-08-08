// Dans admin_script.js ou un nouveau fichier
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between grid and table view
    const viewButtons = document.querySelectorAll('.view-btn');
    const gridView = document.querySelector('.categories-grid');
    const tableView = document.querySelector('.categories-table');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            if (this.dataset.view === 'grid') {
                gridView.style.display = 'grid';
                tableView.style.display = 'none';
            } else {
                gridView.style.display = 'none';
                tableView.style.display = 'block';
            }
        });
    });

    // Animation for cards
    const cards = document.querySelectorAll('.category-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.12)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
        });
    });

});