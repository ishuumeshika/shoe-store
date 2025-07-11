// Form validation for registration
document.addEventListener('DOMContentLoaded', function() {
    // Password match validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if(password && confirmPassword) {
        function validatePassword() {
            if(password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
    }
    
    // Live search functionality
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');
    
    if(searchInput && searchResults) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if(query.length > 2) {
                fetch(`../search.php?live=1&query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.length > 0) {
                            let html = '';
                            data.forEach(item => {
                                html += `
                                    <a class="dropdown-item" href="../pages/products/details.php?id=${item.id}">
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/uploads/${item.image}" width="50" height="50" class="me-2">
                                            <div>
                                                <h6 class="mb-0">${item.name}</h6>
                                                <small class="text-muted">$${item.price}</small>
                                            </div>
                                        </div>
                                    </a>
                                `;
                            });
                            searchResults.innerHTML = html;
                            searchResults.classList.add('show');
                        } else {
                            searchResults.innerHTML = '<div class="dropdown-item text-muted">No results found</div>';
                            searchResults.classList.add('show');
                        }
                    });
            } else {
                searchResults.classList.remove('show');
            }
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if(!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('show');
            }
        });
    }
});