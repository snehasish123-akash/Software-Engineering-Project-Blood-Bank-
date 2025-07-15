// BBDMS Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Blood group search functionality
    const bloodGroupFilter = document.getElementById('bloodGroupFilter');
    if (bloodGroupFilter) {
        bloodGroupFilter.addEventListener('change', function() {
            filterDonors();
        });
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterDonors();
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }, 5000);
        }
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Loading button states
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading"></span> Processing...';
                this.disabled = true;
                
                // Re-enable button after 5 seconds as fallback
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 5000);
            }
        });
    });

    // Message modal functionality
    const messageButtons = document.querySelectorAll('.message-btn');
    messageButtons.forEach(button => {
        button.addEventListener('click', function() {
            const donorId = this.getAttribute('data-donor-id');
            const donorName = this.getAttribute('data-donor-name');
            
            // Check if user is logged in (this variable should be set by PHP)
            if (typeof userLoggedIn === 'undefined' || !userLoggedIn) {
                window.location.href = 'login.php?message=' + encodeURIComponent('Please login to message donors');
                return;
            }
            
            // Open message modal
            openMessageModal(donorId, donorName);
        });
    });

    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card, .donor-card, .process-card');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    cards.forEach(card => {
        observer.observe(card);
    });
});

// Filter donors function
function filterDonors() {
    const bloodGroupFilter = document.getElementById('bloodGroupFilter');
    const searchInput = document.getElementById('searchInput');
    const donorCards = document.querySelectorAll('.donor-card');

    if (!donorCards.length) return;

    const selectedBloodGroup = bloodGroupFilter ? bloodGroupFilter.value : '';
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';

    let visibleCount = 0;

    donorCards.forEach(card => {
        const bloodGroup = card.getAttribute('data-blood-group');
        const name = card.getAttribute('data-name');
        const location = card.getAttribute('data-location');

        const bloodGroupMatch = !selectedBloodGroup || bloodGroup === selectedBloodGroup;
        const searchMatch = !searchTerm || 
            (name && name.includes(searchTerm)) || 
            (location && location.includes(searchTerm));

        if (bloodGroupMatch && searchMatch) {
            card.style.display = 'block';
            card.parentElement.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
            card.parentElement.style.display = 'none';
        }
    });

    // Show "no results" message if no cards are visible
    updateNoResultsMessage(visibleCount);
}

// Update no results message
function updateNoResultsMessage(visibleCount) {
    const container = document.querySelector('.donor-list-container');
    let noResultsMsg = document.getElementById('noResults');
    
    if (visibleCount === 0) {
        if (!noResultsMsg && container) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'noResults';
            noResultsMsg.className = 'col-12';
            noResultsMsg.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No donors found</h4>
                    <p class="text-muted">Try adjusting your search criteria.</p>
                </div>
            `;
            container.querySelector('.row').appendChild(noResultsMsg);
        }
    } else {
        if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
}

// Open message modal
function openMessageModal(donorId, donorName) {
    const modal = document.getElementById('messageModal');
    if (modal) {
        const modalTitle = modal.querySelector('.modal-title');
        const donorIdInput = modal.querySelector('#donorId');
        
        if (modalTitle) modalTitle.textContent = `Message ${donorName}`;
        if (donorIdInput) donorIdInput.value = donorId;
        
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
}

// Send message function
function sendMessage(formData) {
    return fetch('send-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    });
}

// Show alert function
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }
    }, 5000);
}

// Confirm delete function
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Format date function
function formatDate(dateString, options = { year: 'numeric', month: 'long', day: 'numeric' }) {
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Format datetime function
function formatDateTime(dateString, options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) {
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Time ago function
function timeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
    if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';
    if (diffInSeconds < 31536000) return Math.floor(diffInSeconds / 2592000) + ' months ago';
    return Math.floor(diffInSeconds / 31536000) + ' years ago';
}

// Validate email function
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate phone function
function validatePhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone);
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        numbers: /\d/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    Object.values(checks).forEach(check => {
        if (check) strength++;
    });
    
    return {
        score: strength,
        checks: checks,
        level: strength < 3 ? 'weak' : strength < 5 ? 'medium' : 'strong'
    };
}

// Update password strength indicator
function updatePasswordStrength(passwordInput, strengthIndicator) {
    const password = passwordInput.value;
    const strength = checkPasswordStrength(password);
    
    if (strengthIndicator) {
        strengthIndicator.className = `password-strength ${strength.level}`;
        strengthIndicator.textContent = `Password strength: ${strength.level}`;
    }
}

// Initialize password strength checker
document.addEventListener('DOMContentLoaded', function() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const strengthIndicator = input.parentElement.querySelector('.password-strength');
        if (strengthIndicator) {
            input.addEventListener('input', () => updatePasswordStrength(input, strengthIndicator));
        }
    });
});

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debounce to search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        const debouncedFilter = debounce(filterDonors, 300);
        searchInput.addEventListener('input', debouncedFilter);
    }
});

// Handle responsive navigation
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        // Close mobile menu when clicking on a link
        const navLinks = navbarCollapse.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navbarCollapse.classList.contains('show')) {
                    navbarToggler.click();
                }
            });
        });
    }
});

// Handle form submissions with loading states
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && form.checkValidity()) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';
                submitBtn.disabled = true;
                
                // Fallback to re-enable button
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });
});

// Utility function to get URL parameters
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// Handle back to top functionality
document.addEventListener('DOMContentLoaded', function() {
    // Create back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'btn btn-primary position-fixed';
    backToTopBtn.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; display: none; width: 50px; height: 50px; border-radius: 50%;';
    backToTopBtn.title = 'Back to Top';
    
    document.body.appendChild(backToTopBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });
    
    // Scroll to top when clicked
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});