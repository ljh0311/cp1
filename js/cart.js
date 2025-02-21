// Cart functionality
document.addEventListener('DOMContentLoaded', () => {
    // Add click event listeners to all "Add to Cart" buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            try {
                const bookId = this.dataset.bookId;
                if (!bookId) {
                    throw new Error('Book ID is missing');
                }

                console.log('Adding book to cart:', bookId); // Debug log

                // Disable button while processing
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                const response = await fetch('/cart/add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id: bookId,
                        quantity: 1
                    })
                });

                // Log the raw response for debugging
                const responseText = await response.text();
                console.log('Raw server response:', responseText);

                // Try to parse the response as JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Failed to parse server response:', parseError);
                    throw new Error('Invalid server response');
                }
                
                // Create alert element
                const alert = document.createElement('div');
                alert.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
                alert.style.top = '20px';
                alert.style.right = '20px';
                alert.style.zIndex = '1050';
                
                alert.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${data.message}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Add alert to the page
                document.body.appendChild(alert);
                
                // Update cart count if successful
                if (data.success && data.cart_count !== undefined) {
                    const cartCount = document.getElementById('cartCount');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                        
                        // Animate the cart count
                        cartCount.classList.add('cart-count-animation');
                        setTimeout(() => {
                            cartCount.classList.remove('cart-count-animation');
                        }, 300);
                    }
                }

                // Remove alert after 3 seconds
                setTimeout(() => {
                    alert.remove();
                }, 3000);

            } catch (error) {
                console.error('Error details:', error);
                
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                alert.style.top = '20px';
                alert.style.right = '20px';
                alert.style.zIndex = '1050';
                
                alert.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${error.message || 'Failed to add item to cart. Please try again.'}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                document.body.appendChild(alert);
                
                setTimeout(() => {
                    alert.remove();
                }, 3000);
            } finally {
                // Re-enable button and restore text
                this.disabled = false;
                this.innerHTML = 'Add to Cart';
            }
        });
    });
}); 