/**
 * StudyTrack API Client
 * Handles all API communication with loading states and error handling
 */

const StudyTrackAPI = {
    // API Base URL
    baseUrl: '/api',
    
    /**
     * Make API request with error handling
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Request failed');
            }
            
            return data;
            
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    /**
     * Events API
     */
    events: {
        async list(filters = {}) {
            const params = new URLSearchParams(filters);
            return StudyTrackAPI.request(`/events/list.php?${params}`);
        },
        
        async create(eventData) {
            return StudyTrackAPI.request('/events/create.php', {
                method: 'POST',
                body: JSON.stringify(eventData)
            });
        },
        
        async update(eventId, updates) {
            return StudyTrackAPI.request('/events/update.php', {
                method: 'PUT',
                body: JSON.stringify({ event_id: eventId, ...updates })
            });
        },
        
        async delete(eventId) {
            return StudyTrackAPI.request('/events/delete.php', {
                method: 'DELETE',
                body: JSON.stringify({ event_id: eventId })
            });
        },
        
        async approve(eventId) {
            return StudyTrackAPI.request('/events/approve.php', {
                method: 'POST',
                body: JSON.stringify({ event_id: eventId })
            });
        },
        
        async reject(eventId) {
            return StudyTrackAPI.request('/events/reject.php', {
                method: 'POST',
                body: JSON.stringify({ event_id: eventId })
            });
        }
    },
    
    /**
     * Sections API
     */
    sections: {
        async join(sectionId) {
            return StudyTrackAPI.request('/sections/join.php', {
                method: 'POST',
                body: JSON.stringify({ section_id: sectionId })
            });
        },
        
        async leave(sectionId) {
            return StudyTrackAPI.request('/sections/leave.php', {
                method: 'POST',
                body: JSON.stringify({ section_id: sectionId })
            });
        }
    },
    
    /**
     * User API
     */
    user: {
        async getStats() {
            return StudyTrackAPI.request('/user/stats.php');
        }
    }
};

/**
 * UI Helper Functions
 */
const StudyTrackUI = {
    /**
     * Show loading spinner
     */
    showLoading(target = document.body) {
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner';
        spinner.innerHTML = `
            <div class="spinner"></div>
            <div class="spinner-text">Loading...</div>
        `;
        target.appendChild(spinner);
        return spinner;
    },
    
    /**
     * Remove loading spinner
     */
    hideLoading(spinner) {
        if (spinner && spinner.parentNode) {
            spinner.parentNode.removeChild(spinner);
        }
    },
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },
    
    /**
     * Show error message
     */
    showError(message) {
        this.showToast(message, 'error');
    },
    
    /**
     * Show success message
     */
    showSuccess(message) {
        this.showToast(message, 'success');
    },
    
    /**
     * Confirm dialog
     */
    async confirm(message) {
        return window.confirm(message);
    }
};

// Add CSS for loading spinner and toasts
const style = document.createElement('style');
style.textContent = `
    .loading-spinner {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background: var(--bg-secondary);
        padding: var(--spacing-xl);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-md);
    }
    
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid var(--bg-tertiary);
        border-top-color: var(--brand-blue);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .spinner-text {
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }
    
    .toast {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: var(--spacing-md) var(--spacing-lg);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        max-width: 90%;
        width: 400px;
        text-align: center;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    
    .toast-success {
        border-left: 4px solid var(--success);
    }
    
    .toast-error {
        border-left: 4px solid var(--error);
    }
    
    .toast-warning {
        border-left: 4px solid var(--warning);
    }
`;
document.head.appendChild(style);

// Export for use in other scripts
window.StudyTrackAPI = StudyTrackAPI;
window.StudyTrackUI = StudyTrackUI;
