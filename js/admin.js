/**
 * Admin panel functionality module
 */

/**
 * Toggles the edit panel for a specific user
 * @param {string|number} userId - The ID of the user to toggle edit panel for
 */
function toggleEdit(userId) {
    const allPanels = document.querySelectorAll('.admin-edit-panel');
    allPanels.forEach(p => {
        if (p.id !== 'edit-' + userId) {
            p.style.display = 'none';
        }
    });

    const panel = document.getElementById('edit-' + userId);
    if (!panel) return;

    // Animacja przy otwieraniu/zamykaniu
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.opacity = '0';
        panel.style.display = 'block';
        setTimeout(() => {
            panel.style.opacity = '1';
        }, 10);
    } else {
        panel.style.opacity = '0';
        setTimeout(() => {
            panel.style.display = 'none';
        }, 200);
    }
}

/**
 * Filters users in the admin panel based on search input
 */
function filterUsers() {
    const input = document.getElementById('user-search');
    if (!input) return;

    const filter = input.value.toLowerCase();
    const table = document.querySelector('.users-table');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

        if (username.includes(filter) || email.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Initialize the admin panel functionality
 */
function initializeAdmin() {
    // Set up user search functionality
    const searchInput = document.getElementById('user-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterUsers);
    }

    // Add click handlers for edit buttons
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(btn => {
        const userId = btn.getAttribute('data-user-id');
        if (userId) {
            btn.addEventListener('click', () => toggleEdit(userId));
        }
    });

    console.log('Admin module initialized');
}

// Export the public functions
export {
    initializeAdmin,  // Main initialization function
    toggleEdit,       // Export in case we need direct access
    filterUsers       // Export in case we need direct access
};

// Expose functions to global scope for HTML onclick attributes
// This is necessary when functions are called directly from HTML
window.toggleEdit = toggleEdit;
window.filterUsers = filterUsers;