document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity changes
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            
            if (this.classList.contains('plus')) {
                input.value = currentValue + 1;
            } else if (this.classList.contains('minus') && currentValue > 1) {
                input.value = currentValue - 1;
            }
            
            updateCartItem(input);
        });
    });

    // Handle manual quantity input
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            updateCartItem(this);
        });
    });

    // Handle remove item buttons
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            const productId = cartItem.dataset.id;
            
            fetch('carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cartItem.remove();
                    updateCartDisplay();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});

function updateCartItem(input) {
    const cartItem = input.closest('.cart-item');
    const productId = cartItem.dataset.id;
    const quantity = input.value;
    
    fetch('carrinho.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&id=${productId}&quantidade=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to update totals
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateCartDisplay() {
    const cartItems = document.querySelectorAll('.cart-item');
    if (cartItems.length === 0) {
        document.getElementById('cart-contents').innerHTML = '<p class="empty-cart">Seu carrinho está vazio</p>';
    }
}
