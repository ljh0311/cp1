class Cart {
    constructor() {
        this.items = new Map();
        this.loadCart();
        this.bindEvents();
    }

    loadCart() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            const cartData = JSON.parse(savedCart);
            cartData.forEach(item => {
                this.items.set(item.id, item);
            });
            this.updateCartCount();
        }
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(Array.from(this.items.values())));
        this.updateCartCount();
    }

    addItem(bookId, title, price) {
        const existingItem = this.items.get(bookId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.set(bookId, {
                id: bookId,
                title: title,
                price: price,
                quantity: 1
            });
        }
        this.saveCart();
        this.showToast(`Added "${title}" to cart`);
    }

    removeItem(bookId) {
        this.items.delete(bookId);
        this.saveCart();
    }

    updateCartCount() {
        const count = Array.from(this.items.values())
            .reduce((sum, item) => sum + item.quantity, 0);
        document.getElementById('cartCount').textContent = count;
    }

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '11';
        toast.innerHTML = `
            <div class="toast show rounded-lg" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Success!</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    bindEvents() {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookId = e.target.dataset.bookId;
                const card = e.target.closest('.card');
                const title = card.querySelector('.card-title').textContent;
                const price = parseFloat(card.querySelector('.h5').textContent.replace('$', ''));
                this.addItem(bookId, title, price);
            });
        });

        document.getElementById('viewCart').addEventListener('click', () => {
            window.location.href = 'cart.php';
        });
    }
}

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Cart();
});
