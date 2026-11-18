/* =====================================================================
   ClassicCart - Frontend JavaScript (jQuery)
===================================================================== */
$(function () {

    /* ---------------- Mobile menu ---------------- */
    $('#navbarToggler').on('click', function () {
        $('#navbarMenu').toggleClass('open');
    });

    /* On mobile, allow dropdowns to open with a click. */
    $('.has-dropdown > a').on('click', function (e) {
        if ($(window).width() <= 992) {
            e.preventDefault();
            var parent = $(this).parent();
            $('.has-dropdown').not(parent).removeClass('open');
            parent.toggleClass('open');
        }
    });

    /* ---------------- Flash messages ---------------- */
    $('.flash').each(function () {
        var $el = $(this);
        setTimeout(function () { $el.fadeOut(300); }, 5000);
    });
    $('.flash-close').on('click', function () {
        $(this).closest('.flash').fadeOut(200);
    });

    /* ---------------- Toast helper ---------------- */
    window.showToast = function (message) {
        $('.toast').remove();
        var $t = $('<div class="toast">' + message + '</div>').appendTo('body');
        setTimeout(function () { $t.fadeOut(300, function () { $t.remove(); }); }, 2600);
    };

    /* ---------------- Update cart/wishlist badges ---------------- */
    window.updateBadges = function (cart, wishlist) {
        if (cart !== undefined && $('#cartCount').length) {
            $('#cartCount').text(cart);
        }
        if (wishlist !== undefined && $('#wishlistCount').length) {
            $('#wishlistCount').text(wishlist);
        }
    };

    /* ---------------- Add to cart (AJAX) ---------------- */
    $(document).on('click', '.add-to-cart', function () {
        var $btn = $(this);
        var productId = $btn.data('id');
        var qty = 1;
        var $qty = $btn.closest('.product-info').find('.qty-selector input');
        if ($qty.length) { qty = parseInt($qty.val()) || 1; }

        $btn.prop('disabled', true);
        $.ajax({
            url: 'cart.php',
            type: 'POST',
            dataType: 'json',
            data: { ajax: 1, action: 'add', product_id: productId, quantity: qty },
            success: function (res) {
                if (res.error) {
                    if (res.login) { window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); return; }
                    showToast(res.error);
                } else {
                    showToast(res.message || 'Product added to cart.');
                    updateBadges(res.cart_count);
                }
            },
            error: function () { showToast('Something went wrong. Please try again.'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    /* ---------------- Add / remove wishlist (AJAX) ---------------- */
    $(document).on('click', '.wishlist-btn', function () {
        var $btn = $(this);
        var productId = $btn.data('id');
        var active = $btn.hasClass('active');

        $.ajax({
            url: 'wishlist.php',
            type: 'POST',
            dataType: 'json',
            data: { ajax: 1, action: active ? 'remove' : 'add', product_id: productId },
            success: function (res) {
                if (res.error) {
                    if (res.login) { window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search); return; }
                    showToast(res.error);
                } else {
                    showToast(res.message);
                    $btn.toggleClass('active', active ? false : true);
                    if (res.wishlist_count !== undefined) { updateBadges(undefined, res.wishlist_count); }
                    if (res.removed && $btn.closest('.wishlist-item').length) {
                        $btn.closest('.wishlist-item').fadeOut(300, function () { $(this).remove(); refreshWishlistPage(); });
                    }
                }
            },
            error: function () { showToast('Something went wrong. Please try again.'); }
        });
    });

    function refreshWishlistPage() {
        if ($('.wishlist-item').length === 0) {
            window.location.reload();
        }
    }

    /* ---------------- Cart quantity update (AJAX) ---------------- */
    $(document).on('click', '.qty-controls .qty-minus, .qty-controls .qty-plus', function () {
        var $ctl = $(this).closest('.qty-controls');
        var $input = $ctl.find('.qty-input');
        var cartId = $ctl.data('id');
        var current = parseInt($input.val()) || 1;
        var next = $(this).hasClass('qty-plus') ? current + 1 : current - 1;
        if (next < 1) { next = 1; }
        $input.val(next);
        updateCartRow(cartId, next, $ctl);
    });

    $(document).on('change', '.qty-controls .qty-input', function () {
        var $ctl = $(this).closest('.qty-controls');
        var cartId = $ctl.data('id');
        var qty = parseInt($(this).val()) || 1;
        if (qty < 1) { qty = 1; }
        $(this).val(qty);
        updateCartRow(cartId, qty, $ctl);
    });

    function updateCartRow(cartId, qty, $ctl) {
        $ctl.prop('disabled', true);
        $.ajax({
            url: 'cart.php',
            type: 'POST',
            dataType: 'json',
            data: { ajax: 1, action: 'update', item_id: cartId, quantity: qty },
            success: function (res) {
                if (res.error) { showToast(res.error); window.location.reload(); return; }
                updateBadges(res.cart_count);
                if (res.item_subtotal !== undefined) {
                    $ctl.closest('tr').find('.subtotal-cell').text(res.item_subtotal);
                }
                if (res.subtotal !== undefined && $('#cartSubtotal').length) { $('#cartSubtotal').text(res.subtotal); }
                if (res.shipping !== undefined && $('#cartShipping').length) {
                    $('#cartShipping').text(res.shipping === '0.00' ? 'FREE' : res.shipping);
                }
                if (res.total !== undefined && $('#cartTotal').length) { $('#cartTotal').text(res.total); }
            },
            error: function () { showToast('Something went wrong.'); window.location.reload(); },
            complete: function () { $ctl.prop('disabled', false); }
        });
    }

    /* ---------------- Remove cart item (AJAX) ---------------- */
    $(document).on('click', '.cart-remove', function () {
        var cartId = $(this).data('id');
        var $row = $(this).closest('tr');
        $.ajax({
            url: 'cart.php',
            type: 'POST',
            dataType: 'json',
            data: { ajax: 1, action: 'remove', item_id: cartId },
            success: function (res) {
                if (res.error) { showToast(res.error); return; }
                showToast('Item removed from cart.');
                $row.fadeOut(250, function () {
                    $(this).remove();
                    window.location.reload();
                });
            },
            error: function () { showToast('Something went wrong.'); }
        });
    });

    /* ---------------- Move wishlist item to cart ---------------- */
    $(document).on('click', '.move-to-cart', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.ajax({
            url: 'wishlist.php',
            type: 'POST',
            dataType: 'json',
            data: { ajax: 1, action: 'move_to_cart', product_id: $btn.data('id') },
            success: function (res) {
                if (res.error) {
                    if (res.login) { window.location.href = 'login.php'; return; }
                    showToast(res.error);
                } else {
                    showToast(res.message || 'Moved to cart.');
                    updateBadges(res.cart_count, res.wishlist_count);
                    $btn.closest('.wishlist-item').fadeOut(300, function () {
                        $(this).remove();
                        refreshWishlistPage();
                    });
                }
            },
            error: function () { showToast('Something went wrong.'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    /* ---------------- Product page qty selector ---------------- */
    var $qty = $('.qty-selector');
    $qty.find('.qty-minus').on('click', function () {
        var $input = $qty.find('input');
        var val = parseInt($input.val()) || 1;
        if (val > 1) {
            $input.val(val - 1);
            $input.trigger('change');
        }
    });
    $qty.find('.qty-plus').on('click', function () {
        var $input = $qty.find('input');
        var max = parseInt($input.attr('max')) || 99;
        var val = parseInt($input.val()) || 1;
        if (val < max) {
            $input.val(val + 1);
            $input.trigger('change');
        }
    });

    /* ---------------- Shop sorting auto-submit ---------------- */
    $('#sortSelect').on('change', function () {
        $(this).closest('form').submit();
    });

    /* ---------------- Delete / dangerous action confirmation ---------------- */
    $(document).on('click', '[data-confirm]', function (e) {
        var msg = $(this).data('confirm') || 'Are you sure you want to continue?';
        if (!window.confirm(msg)) {
            e.preventDefault();
        }
    });

    $(document).on('submit', '[data-confirm-form]', function (e) {
        var msg = $(this).data('confirm-form') || 'Are you sure you want to continue?';
        if (!window.confirm(msg)) {
            e.preventDefault();
        }
    });

});