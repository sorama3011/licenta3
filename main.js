// Global variables
let cart = [];

// Check authentication status on page load
document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Check if user is logged in
        const authResponse = await API.Auth.checkSession();
        updateNavigation(authResponse.loggedIn, authResponse.user);
        
        // Get cart data
        const cartResponse = await API.Cart.getCart();
        if (cartResponse.success) {
            updateCartCount(cartResponse.totalQuantity || 0);
        }
        
        // Load featured products on homepage
        if (document.getElementById('featured-products')) {
            loadFeaturedProducts();
        }
        
        // Check if we're on account page and user is not logged in
        if (window.location.pathname.includes('account.html') && !authResponse.loggedIn) {
            localStorage.setItem('redirectAfterLogin', 'account.html');
            window.location.href = 'login.html';
            return;
        }
        
        // Handle hash navigation for account page
        if (window.location.hash && window.location.pathname.includes('account.html')) {
            setTimeout(() => {
                const targetElement = document.querySelector(window.location.hash);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
        }
        
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                
                // Skip if href is just '#' (invalid selector)
                if (href === '#') {
                    return;
                }
                
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add back to top button
        addBackToTopButton();
        
    } catch (error) {
        console.error('Error initializing page:', error);
    }
});

// Authentication functions
async function logout() {
    try {
        const response = await API.Auth.logout();
        if (response.success) {
            showNotification(response.message, 'info');
            
            // Redirect to home page if on account page
            if (window.location.pathname.includes('account.html')) {
                window.location.href = 'index.html';
            } else {
                // Just update navigation
                updateNavigation(false);
            }
        } else {
            showNotification('Eroare la deconectare: ' + response.message, 'danger');
        }
    } catch (error) {
        console.error('Logout error:', error);
        showNotification('A apărut o eroare la deconectare.', 'danger');
    }
}

function updateNavigation(isLoggedIn, userData = null) {
    const accountLinks = document.querySelectorAll('a[href="login.html"]');
    
    accountLinks.forEach(link => {
        const parentLi = link.closest('li');
        if (parentLi) {
            if (isLoggedIn && userData) {
                // User is logged in - show account menu
                parentLi.innerHTML = `
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-check"></i> ${userData.name || 'Contul Meu'}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="account.html">
                                <i class="bi bi-person me-2"></i>Contul Meu
                            </a></li>
                            <li><a class="dropdown-item" href="account.html#order-history">
                                <i class="bi bi-clock-history me-2"></i>Istoric Comenzi
                            </a></li>
                            <li><a class="dropdown-item" href="account.html#wishlist">
                                <i class="bi bi-heart me-2"></i>Lista de Dorințe
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()">
                                <i class="bi bi-box-arrow-right me-2"></i>Deconectare
                            </a></li>
                        </ul>
                    </div>
                `;
            } else {
                // User is not logged in - show login link
                parentLi.innerHTML = `
                    <a class="nav-link" href="login.html">
                        <i class="bi bi-person"></i> Cont
                    </a>
                `;
            }
        }
    });
}

function requireLogin(redirectUrl = null) {
    API.Auth.checkSession().then(response => {
        if (!response.loggedIn) {
            if (redirectUrl) {
                localStorage.setItem('redirectAfterLogin', redirectUrl);
            }
            window.location.href = 'login.html';
            return false;
        }
        return true;
    }).catch(error => {
        console.error('Session check error:', error);
        return false;
    });
}

// Featured products data
const featuredProducts = [
    {
        id: 1,
        name: "Dulceață de Căpșuni de Argeș",
        price: "18.99",
        weight: "350g",
        region: "Argeș",
        image: "https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Dulceata+Capsuni",
        description: "Dulceață tradițională din căpșuni proaspete de Argeș"
    },
    {
        id: 2,
        name: "Zacuscă de Buzău",
        price: "15.50",
        weight: "450g",
        region: "Buzău",
        image: "https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Zacusca+Buzau",
        description: "Zacuscă tradițională cu vinete și ardei copți"
    },
    {
        id: 3,
        name: "Brânză de Burduf",
        price: "32.00",
        weight: "500g",
        region: "Maramureș",
        image: "https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Branza+Burduf",
        description: "Brânză tradițională de oaie maturată în burduf"
    },
    {
        id: 4,
        name: "Țuică de Prune Hunedoara",
        price: "45.00",
        weight: "500ml",
        region: "Hunedoara",
        image: "https://via.placeholder.com/300x200/8B0000/FFFFFF?text=Tuica+Prune",
        description: "Țuică tradițională de prune, 52% alcool"
    }
];

// Load featured products
function loadFeaturedProducts() {
    const container = document.getElementById('featured-products');
    if (!container) return;
    
    container.innerHTML = '';
    
    featuredProducts.forEach(product => {
        const productCard = createProductCard(product);
        container.appendChild(productCard);
    });
}

// Create product card element
function createProductCard(product) {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-3';
    
    col.innerHTML = `
        <div class="card product-card h-100 shadow-sm">
            <img src="${product.image}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">
            <div class="card-body d-flex flex-column">
                <span class="badge region-badge mb-2 align-self-start">Produs local din ${product.region}</span>
                <h5 class="card-title">${product.name}</h5>
                <p class="card-text text-muted small">${product.description}</p>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="price">${product.price} RON</span>
                        <span class="text-muted small">${product.weight}</span>
                    </div>
                    <button class="btn btn-add-to-cart w-100" onclick="addToCart(${product.id}, '${product.name}', ${product.price}, '${product.image}', '${product.weight}')" aria-label="Adaugă ${product.name} în coș">
                        <i class="bi bi-basket"></i> Adaugă în Coș
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

// Add to cart function
async function addToCart(id, name, price, image, weight) {
    try {
        const response = await API.Cart.addToCart(id, 1);
        
        if (response.success) {
            updateCartCount(response.cartCount);
            showAddToCartNotification(name);
        } else {
            showNotification(response.message, 'danger');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification('A apărut o eroare la adăugarea în coș.', 'danger');
    }
}

// Update cart count in navigation
function updateCartCount(count) {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        
        if (count > 0) {
            cartCount.style.display = 'inline';
        } else {
            cartCount.style.display = 'none';
        }
    }
}

// Show add to cart notification
function showAddToCartNotification(productName) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-custom position-fixed';
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 1050; min-width: 300px;';
    notification.innerHTML = `
        <i class="bi bi-check-circle"></i> <strong>${productName}</strong> a fost adăugat în coș!
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

// Remove from cart
async function removeFromCart(id) {
    try {
        const response = await API.Cart.updateCart(id, 0);
        
        if (response.success) {
            updateCartCount(response.cartCount);
            
            // Reload cart page if we're on it
            if (window.location.pathname.includes('cart.html')) {
                loadCartItems();
            }
        } else {
            showNotification(response.message, 'danger');
        }
    } catch (error) {
        console.error('Remove from cart error:', error);
        showNotification('A apărut o eroare la eliminarea din coș.', 'danger');
    }
}

// Update cart item quantity
async function updateCartQuantity(id, quantity) {
    try {
        const response = await API.Cart.updateCart(id, parseInt(quantity));
        
        if (response.success) {
            updateCartCount(response.cartCount);
            
            // Update cart total if we're on cart page
            if (window.location.pathname.includes('cart.html')) {
                loadCartItems();
            }
        } else {
            showNotification(response.message, 'danger');
            
            // Reset quantity input to previous value
            if (window.location.pathname.includes('cart.html')) {
                loadCartItems();
            }
        }
    } catch (error) {
        console.error('Update cart error:', error);
        showNotification('A apărut o eroare la actualizarea coșului.', 'danger');
    }
}

// Load cart items (for cart page)
async function loadCartItems() {
    const cartContainer = document.getElementById('cart-items');
    if (!cartContainer) return;
    
    try {
        const response = await API.Cart.getCart();
        
        if (response.success) {
            if (response.items.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-basket text-muted" style="font-size: 4rem;"></i>
                        <h3 class="mt-3">Coșul este gol</h3>
                        <p class="text-muted">Nu aveți încă produse în coș</p>
                        <a href="products.html" class="btn btn-primary">Începe Cumpărăturile</a>
                    </div>
                `;
                document.getElementById('cart-summary').style.display = 'none';
                return;
            }
            
            cartContainer.innerHTML = '';
            
            response.items.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item border-bottom py-3';
                cartItem.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="${item.image}" alt="${item.name}" class="img-fluid rounded">
                        </div>
                        <div class="col-md-4">
                            <h6>${item.name}</h6>
                            <small class="text-muted">${item.weight}</small>
                        </div>
                        <div class="col-md-2">
                            <span class="price">${item.price.toFixed(2)} RON</span>
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1" 
                                   onchange="updateCartQuantity(${item.id}, this.value)" style="max-width: 80px;">
                        </div>
                        <div class="col-md-2 text-end">
                            <strong>${item.total.toFixed(2)} RON</strong>
                            <button class="btn btn-outline-danger btn-sm ms-2" onclick="removeFromCart(${item.id})" 
                                    aria-label="Elimină ${item.name} din coș">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                cartContainer.appendChild(cartItem);
            });
            
            // Update summary
            document.getElementById('subtotal').textContent = `${response.summary.subtotal.toFixed(2)} RON`;
            document.getElementById('shipping').textContent = response.summary.shipping === 0 ? 'Gratuit' : `${response.summary.shipping.toFixed(2)} RON`;
            
            // Show/hide discount row
            const discountRow = document.getElementById('discount-row');
            if (response.summary.discount > 0) {
                discountRow.style.display = 'flex';
                document.getElementById('discount').textContent = `-${response.summary.discount.toFixed(2)} RON`;
            } else {
                discountRow.style.display = 'none';
            }
            
            document.getElementById('total').textContent = `${response.summary.total.toFixed(2)} RON`;
            
            // Show cart summary
            document.getElementById('cart-summary').style.display = 'block';
            
            // Update shipping progress if function exists
            if (typeof updateShippingProgress === 'function') {
                updateShippingProgress();
            }
        } else {
            showNotification(response.message, 'danger');
        }
    } catch (error) {
        console.error('Load cart error:', error);
        showNotification('A apărut o eroare la încărcarea coșului.', 'danger');
    }
}

// Add back to top button
function addBackToTopButton() {
    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.innerHTML = '<i class="bi bi-arrow-up"></i>';
    backToTop.setAttribute('aria-label', 'Înapoi sus');
    
    document.body.appendChild(backToTop);
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });
    
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Newsletter subscription
async function subscribeNewsletter(email) {
    if (!email || !email.includes('@')) {
        showNotification('Vă rugăm introduceți o adresă de email validă.', 'danger');
        return false;
    }
    
    try {
        const response = await API.Newsletter.subscribe(email);
        
        if (response.success) {
            showNotification(response.message, 'success');
            return true;
        } else {
            showNotification(response.message, 'danger');
            return false;
        }
    } catch (error) {
        console.error('Newsletter subscription error:', error);
        showNotification('A apărut o eroare la abonare. Vă rugăm să încercați din nou.', 'danger');
        return false;
    }
}

// Generic notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-custom position-fixed`;
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 1050; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 4000);
}

// Search functionality
function searchProducts(query) {
    // Redirect to products page with search parameter
    window.location.href = `products.html?search=${encodeURIComponent(query)}`;
}

// Initialize search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchProducts(this.value);
            }
        });
    }
});