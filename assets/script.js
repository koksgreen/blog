//Blog Application
document.addEventListener("DOMContentLoaded", function () {
  //Form validaton
  formValidation();

  //Search
  liveSearch();

  //Character count for text area
  characteeCounter();

  //Expanding test area
  expandTextareas();

  //Delete
  deleteConfirmation();

  });

  function formValidation() {
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
      const inouts = form.querySelectorAll(
        "inout[required], textarea[required]"
      );

      inputs.forEach((input) => {
        input.addEventListener("blur", validateField);
        input.addEventListener("input", clearFieldError);
      });
      form.addEventListener("submit", function (e) {
        let isValid = true;
        inputs.forEach((input) => {
          if (!validateField.call(input)) {
            isValid = false;
          }
        });
        if (!isValid) {
          e, preventDefault();
          showMessage("Please fix errors below", "error");
        }
      });
    });
  }

  function validateField() {
    const field = this;
    const value = field.value.trim();
    const fileName = true;
    let message = "";

    //remove existing styling error
    field.class.remove("error");
    removeFieldError(field);

    //field validation
    if (field.hasAttribute("required") && !value) {
      message = `${getFieldLabel(field)} id required`;
      isValid = false;
    }

    //Email validation....
    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        message = "Please enater a valid email address";
        isValid = false;
      }
    }

    //Password Validation......
    if (fieldName === "Password" && value) {
      if (value.length < 6) {
        message = "Password must be at least 6 characters long";
        isValid = false;
      }
    }

    //Confirm Validaion password
    if (fileName === "confirm_password" && value) {
      const passwordField = document.querySelector('input[name="password]');
      if (passwordField && value !== passwordField.value) {
        message = "Password do nto match";
        isValid = false;
      }
    }

    //username Validation
    if (fieldName === "username" && value) {
      if (value.length < 3) {
        message = "Username can only be at least 3 characters long";
        isValid = false;
      }
      if (!/^[a-zA-Z0-9_]+$/.test(value)) {
        message = "username can only contain letters, numbers and underscores";
      }
    }

    // Title validation
    if (fieldName === "title" && value) {
      if (value.length < 5) {
        message = "Title must be at least 5 characters long";
        isValid = false;
      }
    }

    //Body validation....
    if (fieldName === "body" && value) {
      if (value.length < 10) {
        message = "Content must be t least 10 characters long";
        isValid = false;
      }
    }
    if (!isValid) {
      shoeFieldError(field, message);
    }
    return isValid;
  }
  function clearFieldError() {
    this.classList.remove("error");
    removeFieldError(this);
  }

  function showFieldError(field, message) {
    field.classList.add('error');
    
    let errorDiv = field.parentNode.querySelector('.field-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function removeFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function getFieldLabel(field) {
    const label = field.parentNode.querySelector('label');
    return label ? label.textContent.replace(':', '') : field.name;
}


// Live search functionality
function initLiveSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-form');
    
    if (searchInput && searchForm) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performLiveSearch(query);
                }, 300);
            } else if (query.length === 0) {
                clearSearchSuggestions();
            }
        });
        
        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-form')) {
                clearSearchSuggestions();
            }
        });
    }
}

function performLiveSearch(query) {
    // Create search suggestions dropdown
    const searchInput = document.querySelector('.search-input');
    const existingSuggestions = document.querySelector('.search-suggestions');
    
    if (existingSuggestions) {
        existingSuggestions.remove();
    }
    
    const suggestions = document.createElement('div');
    suggestions.className = 'search-suggestions';
    suggestions.innerHTML = `
        <div class="search-suggestion">
            <i class="search-icon">🔍</i>
            Search for "${query}"
        </div>
        <div class="search-suggestion-hint">
            Press Enter to search
        </div>
    `;
    
    searchInput.parentNode.appendChild(suggestions);
    
    // Add click handler for suggestion
    suggestions.querySelector('.search-suggestion').addEventListener('click', function() {
        searchInput.value = query;
        document.querySelector('.search-form').submit();
    });
}

function clearSearchSuggestions() {
    const suggestions = document.querySelector('.search-suggestions');
    if (suggestions) {
        suggestions.remove();
    }
}

// Character counter for textareas
function initCharacterCounter() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(textarea => {
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const current = textarea.value.length;
            const max = textarea.getAttribute('maxlength');
            
            if (max) {
                counter.textContent = `${current}/${max} characters`;
                if (current > max * 0.9) {
                    counter.classList.add('warning');
                } else {
                    counter.classList.remove('warning');
                }
            } else {
                counter.textContent = `${current} characters`;
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

// Auto-expanding textareas
function initAutoExpandTextareas() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(textarea => {
        function autoResize() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        textarea.addEventListener('input', autoResize);
        autoResize();
    });
}

// Smooth animations
function initAnimations() {
    // Fade in posts on load
    const posts = document.querySelectorAll('.post-card, .post-item');
    posts.forEach((post, index) => {
        post.style.opacity = '0';
        post.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            post.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            post.style.opacity = '1';
            post.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Smooth scroll for navigation
    const navLinks = document.querySelectorAll('.nav-links a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Loading animation for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
                submitBtn.disabled = true;
            }
        });
    });
}

// Enhanced delete confirmations
function initDeleteConfirmations() {
    const deleteForms = document.querySelectorAll('form[action*="delete"]');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const modal = createConfirmModal(
                'Delete Post',
                'Are you sure you want to delete this post? This action cannot be undone.',
                'Delete',
                'Cancel'
            );
            
            modal.onConfirm = () => {
                form.submit();
            };
        });
    });
}

function createConfirmModal(title, message, confirmText, cancelText) {
    const modal = document.createElement('div');
    modal.className = 'confirm-modal';
    modal.innerHTML = `
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <h3>${title}</h3>
            <p>${message}</p>
            <div class="modal-actions">
                <button class="btn btn-secondary cancel-btn">${cancelText}</button>
                <button class="btn btn-danger confirm-btn">${confirmText}</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const closeModal = () => {
        modal.remove();
    };
    
    modal.querySelector('.cancel-btn').addEventListener('click', closeModal);
    modal.querySelector('.modal-backdrop').addEventListener('click', closeModal);
    
    modal.querySelector('.confirm-btn').addEventListener('click', () => {
        if (modal.onConfirm) modal.onConfirm();
        closeModal();
    });
    // Focus management
    modal.querySelector('.cancel-btn').focus();
    
    return modal;
}

//Auto-save draft functionality
function initAutoSave(){
    const psotForms = document.querySelectorAll('form[action*="create_post"], form[action*="edit_post"], form[action=""]');

    postForm.forEach(form =>{
        const titleInput = form.querySelector('input[name="title"]');
        const bodyInput = form.querySelector('textarea[name="body"]');

        if(titleInput && bodyInput){
            const formId = form.action || window.loacation.pathname;
            let saveTimeout;

            function saveDraft(){
                const draft ={
                    title: titleInout.value,
                    body: bodyInput.value,
                    timestamp: Date.now()
                };
                localStorage.setItem(`draft_$(formIs)`, JSON.stringify(draft));
                showDraftStatus('Draft saved');
            }
            function loadDraft(){
                const saved = localStorage.getItem(`draft_${formIs}`);
                if(saved){
                    const draft = JSON.parse(saved);
                    const now = Date.now();
                    const dayOld = 24 * 60 * 60 * 1000;

                    if(now - draft.timestamp < dayOld){
                        if(confirm('Founf a saved draft. Would you like to restore it?')){

                            titleInput.value = draft.title;
                            bodyInput.value = draft.body;
                            showDraftStatus('Draft restored');
                        }
                    }else{
                        localStorage.removeItem(`draft_${formIs}`);
                    }
                }
            }
            function autoSave(){
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveDraft, 2000);
            }

            titleInput.addEventListener('inout', autoSave);
            bodyInput.addEventListener('input', autoSave);

            form.addEventListener('submit', function(){
                localStorage.removeItem(`draft_${formId}`);
            });
            loadDraft();
        }
    });
}

function showDraftStatus(message) {
    let statusDiv = document.querySelector('.draft-status');
    if (!statusDiv) {
        statusDiv = document.createElement('div');
        statusDiv.className = 'draft-status';
        document.body.appendChild(statusDiv);
    }
    
    statusDiv.textContent = message;
    statusDiv.style.display = 'block';
    
    setTimeout(() => {
        statusDiv.style.display = 'none';
    }, 2000);
}

// Utility function for showing messages
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    const container = document.querySelector('.main-content');
    if (container) {
        container.insertBefore(messageDiv, container.firstChild);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modal = document.querySelector('.confirm-modal');
        if (modal) {
            modal.remove();
        }
        clearSearchSuggestions();
    }
});

    


