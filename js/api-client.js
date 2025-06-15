/**
 * API Client for Gusturi Românești
 * This file contains functions to interact with the PHP backend
 */

// Base API URL - adjust if needed
const API_BASE_URL = '';

/**
 * Generic function to make API requests
 * @param {string} endpoint - API endpoint
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {Object} data - Data to send (for POST, PUT requests)
 * @returns {Promise} - Promise that resolves with the API response
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    // Add request body for POST, PUT requests
    if (data && (method === 'POST' || method === 'PUT')) {
        if (data instanceof FormData) {
            options.body = data;
            delete options.headers['Content-Type']; // Let the browser set it
        } else {
            options.body = JSON.stringify(data);
        }
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API request error:', error);
        throw error;
    }
}

/**
 * Authentication functions
 */
const AuthAPI = {
    // Login user
    login: async (email, password, remember = false, redirect = '') => {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
        formData.append('remember', remember ? 'on' : 'off');
        if (redirect) formData.append('redirect', redirect);
        
        return apiRequest('auth/login.php', 'POST', formData);
    },
    
    // Register new user
    register: async (userData) => {
        const formData = new FormData();
        for (const [key, value] of Object.entries(userData)) {
            formData.append(key, value);
        }
        
        return apiRequest('auth/register.php', 'POST', formData);
    },
    
    // Logout user
    logout: async () => {
        return apiRequest('auth/logout.php', 'POST');
    },
    
    // Request password reset
    forgotPassword: async (email) => {
        const formData = new FormData();
        formData.append('email', email);
        
        return apiRequest('auth/forgot-password.php', 'POST', formData);
    },
    
    // Check if user is logged in
    checkSession: async () => {
        return apiRequest('auth/check-session.php');
    }
};

/**
 * Product functions
 */
const ProductAPI = {
    // Get all products with filters
    getProducts: async (filters = {}) => {
        const params = new URLSearchParams();
        
        for (const [key, value] of Object.entries(filters)) {
            if (value !== null && value !== undefined) {
                params.append(key, value);
            }
        }
        
        return apiRequest(`api/get-products.php?${params.toString()}`);
    },
    
    // Get single product by ID
    getProduct: async (productId) => {
        return apiRequest(`api/get-product.php?id=${productId}`);
    },
    
    // Get all categories
    getCategories: async () => {
        return apiRequest('api/get-categories.php');
    },
    
    // Get all regions
    getRegions: async () => {
        return apiRequest('api/get-regions.php');
    },
    
    // Get all tags
    getTags: async () => {
        return apiRequest('api/get-tags.php');
    },
    
    // Add product review
    addReview: async (productId, rating, title, comment) => {
        const formData = new FormData();
        formData.append('productId', productId);
        formData.append('rating', rating);
        formData.append('title', title);
        formData.append('comment', comment);
        
        return apiRequest('api/add-review.php', 'POST', formData);
    }
};

/**
 * Cart functions
 */
const CartAPI = {
    // Add product to cart
    addToCart: async (productId, quantity = 1) => {
        const formData = new FormData();
        formData.append('productId', productId);
        formData.append('quantity', quantity);
        
        return apiRequest('api/add-to-cart.php', 'POST', formData);
    },
    
    // Get cart contents
    getCart: async () => {
        return apiRequest('api/get-cart.php');
    },
    
    // Update cart item quantity
    updateCart: async (cartItemId, quantity) => {
        const formData = new FormData();
        formData.append('cartItemId', cartItemId);
        formData.append('quantity', quantity);
        
        return apiRequest('api/update-cart.php', 'POST', formData);
    },
    
    // Apply voucher to cart
    applyVoucher: async (code, subtotal = null) => {
        const formData = new FormData();
        formData.append('code', code);
        if (subtotal !== null) formData.append('subtotal', subtotal);
        
        return apiRequest('api/apply-voucher.php', 'POST', formData);
    },
    
    // Place order
    placeOrder: async (orderData) => {
        const formData = new FormData();
        for (const [key, value] of Object.entries(orderData)) {
            formData.append(key, value);
        }
        
        return apiRequest('api/place-order.php', 'POST', formData);
    }
};

/**
 * User account functions
 */
const UserAPI = {
    // Get user data
    getUserData: async () => {
        return apiRequest('api/get-user-data.php');
    },
    
    // Update user profile
    updateProfile: async (profileData) => {
        const formData = new FormData();
        for (const [key, value] of Object.entries(profileData)) {
            formData.append(key, value);
        }
        
        return apiRequest('api/update-user-profile.php', 'POST', formData);
    },
    
    // Add new address
    addAddress: async (addressData) => {
        const formData = new FormData();
        for (const [key, value] of Object.entries(addressData)) {
            formData.append(key, value);
        }
        
        return apiRequest('api/add-address.php', 'POST', formData);
    },
    
    // Update address
    updateAddress: async (addressData) => {
        const formData = new FormData();
        for (const [key, value] of Object.entries(addressData)) {
            formData.append(key, value);
        }
        
        return apiRequest('api/update-address.php', 'POST', formData);
    },
    
    // Delete address
    deleteAddress: async (addressId) => {
        const formData = new FormData();
        formData.append('addressId', addressId);
        
        return apiRequest('api/delete-address.php', 'POST', formData);
    },
    
    // Get order history
    getOrders: async () => {
        return apiRequest('api/get-orders.php');
    },
    
    // Get order details
    getOrderDetails: async (orderId) => {
        return apiRequest(`api/get-order-details.php?id=${orderId}`);
    },
    
    // Add product to favorites
    addToFavorites: async (productId) => {
        const formData = new FormData();
        formData.append('productId', productId);
        
        return apiRequest('api/add-to-favorites.php', 'POST', formData);
    },
    
    // Get favorites
    getFavorites: async () => {
        return apiRequest('api/get-favorites.php');
    },
    
    // Remove product from favorites
    removeFavorite: async (favoriteId) => {
        const formData = new FormData();
        formData.append('favoriteId', favoriteId);
        
        return apiRequest('api/remove-favorite.php', 'POST', formData);
    }
};

/**
 * Newsletter functions
 */
const NewsletterAPI = {
    // Subscribe to newsletter
    subscribe: async (email, name = '') => {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('name', name);
        
        return apiRequest('api/newsletter-subscribe.php', 'POST', formData);
    }
};

// Export all API functions
window.API = {
    Auth: AuthAPI,
    Products: ProductAPI,
    Cart: CartAPI,
    User: UserAPI,
    Newsletter: NewsletterAPI
};