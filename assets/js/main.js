// Main JavaScript for Student Course Hub - WCAG 2.1 Compliant
document.addEventListener('DOMContentLoaded', function() {
    // Detect keyboard usage for enhanced focus styles
    let isKeyboardUser = false;
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            isKeyboardUser = true;
            document.body.classList.add('keyboard-user');
        }
    });
    
    document.addEventListener('mousedown', function() {
        isKeyboardUser = false;
        document.body.classList.remove('keyboard-user');
    });

    // Initialize tooltips with accessibility improvements
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus'
        });
    });

    // Enhanced search functionality with accessibility
    const searchInput = document.getElementById('searchInput');
    const levelFilter = document.getElementById('levelFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterProgrammes, 300));
        searchInput.addEventListener('keydown', handleSearchKeydown);
    }
    
    if (levelFilter) {
        levelFilter.addEventListener('change', filterProgrammes);
    }

    // Make programme cards keyboard accessible
    initializeProgrammeCards();

    // Enhanced form validation with accessibility
    const interestForm = document.getElementById('interestForm');
    if (interestForm) {
        interestForm.addEventListener('submit', handleFormSubmit);
        
        // Real-time validation
        const nameInput = document.getElementById('studentName');
        const emailInput = document.getElementById('studentEmail');
        
        if (nameInput) {
            nameInput.addEventListener('blur', validateName);
            nameInput.addEventListener('input', clearValidationError);
        }
        
        if (emailInput) {
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('input', clearValidationError);
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', handleGlobalKeydown);
    
    // Announce page changes to screen readers
    announcePageLoad();
});

// Debounce function for search input
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

// Enhanced programme filtering with accessibility announcements
function filterProgrammes() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const levelFilter = document.getElementById('levelFilter')?.value || '';
    const programmeCards = document.querySelectorAll('.programme-card');
    let visibleCount = 0;
    
    programmeCards.forEach(card => {
        const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        const level = card.dataset.level || '';
        
        const matchesSearch = !searchTerm || title.includes(searchTerm) || description.includes(searchTerm);
        const matchesLevel = !levelFilter || level === levelFilter;
        
        const cardContainer = card.closest('.col-md-6');
        if (matchesSearch && matchesLevel) {
            cardContainer.style.display = 'block';
            card.removeAttribute('aria-hidden');
            visibleCount++;
        } else {
            cardContainer.style.display = 'none';
            card.setAttribute('aria-hidden', 'true');
        }
    });
    
    // Announce results to screen readers
    const message = visibleCount === 0 
        ? 'No programmes found matching your criteria'
        : `${visibleCount} programme${visibleCount !== 1 ? 's' : ''} found`;
    
    announceToScreenReader(message);
}

// Handle search input keyboard navigation
function handleSearchKeydown(e) {
    if (e.key === 'Escape') {
        e.target.value = '';
        filterProgrammes();
        announceToScreenReader('Search cleared');
    }
}

// Initialize programme cards for keyboard accessibility
function initializeProgrammeCards() {
    const programmeCards = document.querySelectorAll('.programme-card');
    
    programmeCards.forEach(card => {
        // Make cards focusable
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        
        // Add keyboard event listeners
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const link = card.querySelector('a[href*="programme-details"]');
                if (link) {
                    link.click();
                }
            }
        });
        
        // Add click handler for the entire card
        card.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                const link = card.querySelector('a[href*="programme-details"]');
                if (link) {
                    link.click();
                }
            }
        });
        
        // Add aria-label for better screen reader support
        const title = card.querySelector('.card-title')?.textContent || '';
        const level = card.querySelector('.level-badge')?.textContent || '';
        card.setAttribute('aria-label', `${title} - ${level} programme. Press Enter to view details.`);
    });
}

// Enhanced form submission with accessibility
function handleFormSubmit(e) {
    const form = e.target;
    const nameInput = document.getElementById('studentName');
    const emailInput = document.getElementById('studentEmail');
    
    let isValid = true;
    
    // Clear previous errors
    clearAllValidationErrors();
    
    // Validate name
    if (!validateName()) {
        isValid = false;
    }
    
    // Validate email
    if (!validateEmail()) {
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        
        // Focus first invalid field
        const firstError = form.querySelector('.is-invalid');
        if (firstError) {
            firstError.focus();
        }
        
        announceToScreenReader('Please correct the errors in the form');
        return false;
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Submitting...';
    }
}

// Validate name field
function validateName() {
    const nameInput = document.getElementById('studentName');
    const name = nameInput?.value.trim() || '';
    
    if (!name) {
        showValidationError(nameInput, 'Please enter your full name');
        return false;
    }
    
    if (name.length < 2) {
        showValidationError(nameInput, 'Name must be at least 2 characters long');
        return false;
    }
    
    showValidationSuccess(nameInput);
    return true;
}

// Validate email field
function validateEmail() {
    const emailInput = document.getElementById('studentEmail');
    const email = emailInput?.value.trim() || '';
    
    if (!email) {
        showValidationError(emailInput, 'Please enter your email address');
        return false;
    }
    
    if (!isValidEmail(email)) {
        showValidationError(emailInput, 'Please enter a valid email address');
        return false;
    }
    
    showValidationSuccess(emailInput);
    return true;
}

// Show validation error
function showValidationError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    input.setAttribute('aria-invalid', 'true');
    
    let feedback = input.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.setAttribute('role', 'alert');
        input.parentNode.appendChild(feedback);
    }
    
    feedback.textContent = message;
    input.setAttribute('aria-describedby', feedback.id || 'error-' + input.id);
}

// Show validation success
function showValidationSuccess(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    input.setAttribute('aria-invalid', 'false');
    
    const feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.remove();
    }
}

// Clear validation error for specific input
function clearValidationError(e) {
    const input = e.target;
    input.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');
    
    const feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.remove();
    }
}

// Clear all validation errors
function clearAllValidationErrors() {
    const invalidInputs = document.querySelectorAll('.is-invalid');
    invalidInputs.forEach(input => {
        input.classList.remove('is-invalid');
        input.removeAttribute('aria-invalid');
    });
    
    const feedbacks = document.querySelectorAll('.invalid-feedback');
    feedbacks.forEach(feedback => feedback.remove());
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Global keyboard shortcuts
function handleGlobalKeydown(e) {
    // Skip to main content with Alt+M
    if (e.altKey && e.key.toLowerCase() === 'm') {
        e.preventDefault();
        const main = document.querySelector('#main-content');
        if (main) {
            main.focus();
            main.scrollIntoView({ behavior: 'smooth' });
            announceToScreenReader('Jumped to main content');
        }
    }
    
    // Skip to navigation with Alt+N
    if (e.altKey && e.key.toLowerCase() === 'n') {
        e.preventDefault();
        const nav = document.querySelector('#main-navigation');
        if (nav) {
            const firstLink = nav.querySelector('a');
            if (firstLink) {
                firstLink.focus();
                announceToScreenReader('Jumped to navigation');
            }
        }
    }
    
    // Search shortcut with Alt+S
    if (e.altKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
            announceToScreenReader('Focused on search input');
        }
    }
}

// Announce messages to screen readers
function announceToScreenReader(message) {
    const liveRegion = document.getElementById('live-region');
    if (liveRegion) {
        liveRegion.textContent = message;
        
        // Clear after announcement
        setTimeout(() => {
            liveRegion.textContent = '';
        }, 1000);
    }
}

// Announce page load
function announcePageLoad() {
    const pageTitle = document.title;
    const mainHeading = document.querySelector('h1');
    const headingText = mainHeading ? mainHeading.textContent : '';
    
    setTimeout(() => {
        announceToScreenReader(`Page loaded: ${headingText || pageTitle}`);
    }, 500);
}

// Handle focus management for modals and dynamic content
function manageFocus(element) {
    if (element) {
        element.focus();
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Trap focus within modal dialogs
function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
        'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select'
    );
    
    const firstFocusableElement = focusableElements[0];
    const lastFocusableElement = focusableElements[focusableElements.length - 1];
    
    element.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstFocusableElement) {
                    lastFocusableElement.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastFocusableElement) {
                    firstFocusableElement.focus();
                    e.preventDefault();
                }
            }
        }
        
        if (e.key === 'Escape') {
            const closeButton = element.querySelector('[data-bs-dismiss="modal"]');
            if (closeButton) {
                closeButton.click();
            }
        }
    });
}

// Initialize focus trapping for existing modals
document.addEventListener('shown.bs.modal', function(e) {
    trapFocus(e.target);
    const firstInput = e.target.querySelector('input, textarea, select');
    if (firstInput) {
        firstInput.focus();
    }
});