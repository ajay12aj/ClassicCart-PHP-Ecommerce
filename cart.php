<?php
/**
 * ClassicCart - Shopping Cart
 * Handles AJAX actions: add, update, remove, clear.
 */
$page_title = 'My Cart - ClassicCart';
require_once 'includes/auth.php';

$user = current_user();

/* ---------------- AJAX / POST actions ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user) {
        if (is_ajax()) {
            json_out(array('error' => 'Please login to continue.', 'login' => true));
        }
        flash_set('warning', 'Please login to continue.');
        remember_redirect();
        header('Location: login.php');
        exit;
    }

    /* Non-AJAX request: only the "clear cart" form is allowed. */
    if (!isset($_POST['ajax']) || $_POST['ajax'] != 1) {
        if (($_POST['action'] ?? '') === 'clear' && verify_csrf()) {
            db()->prepare('DELETE FROM cart WHERE user_id = ?')->execute(array($user['id']));
            flash_set('success', 'Your cart has been cleared.');
        }
        header('Location: cart.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $itemId    = (int)($_POST['item_id'] ?? 0);

    switch ($action) {
        case 'add':
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            $p = get_product($productId, true);
            if (!$p) {
                json_out(array('error' => 'Product not found.'));
            }
            if ((int)$p['stock'] <= 0) {
                json_out(array('error' => 'This product is out of stock.'));
            }

            $stmt = db()->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->execute(array($user['id'], $productId));
            $row = $stmt->fetch();

            $newQty = $row ? ($row['quantity'] + $quantity) : $quantity;
            if ($newQty > (int)$p['stock']) {
                $newQty = (int)$p['stock'];
            }
            if ($row) {
                db()->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute(array($newQty, $row['id']));
            } else {
                db()->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')
                    ->execute(array($user['id'], $productId, $newQty));
            }
            json_out(array(
                'success' => true,
                'message' => 'Product added to cart.',
                'cart_count' => cart_count(),
            ));

        case 'update':
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            $stmt = db()->prepare(
                'SELECT c.id, p.stock FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ?'
            );
            $stmt->execute(array($itemId, $user['id']));
            $row = $stmt->fetch();
            if (!$row) {
                json_out(array('error' => 'Cart item not found.'));
            }
            if ($quantity > (int)$row['stock']) {
                $quantity = (int)$row['stock'];
            }
            db()->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute(array($quantity, $itemId));
            $totals = cart_totals();
            $sub = 0.0;
            foreach ($totals['items'] as $it) {
                if ((int)$it['cart_id'] === $itemId) {
                    $sub = product_price($it) * $quantity;
                }
            }
            json_out(array(
                'success' => true,
                'message'  => 'Cart updated.',
                'cart_count' => cart_count(),
                'item_subtotal' => money($sub),
                'subtotal' => money($totals['subtotal']),
                'shipping' => money($totals['shipping']),
                'total'    => money($totals['total']),
            ));

        case 'remove':
            db()->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?')->execute(array($itemId, $user['id']));
            json_out(array('success' => true, 'message' => 'Item removed from cart.', 'cart_count' => cart_count()));

        case 'clear':
            db()->prepare('DELETE FROM cart WHERE user_id = ?')->execute(array($user['id']));
            json_out(array('success' => true, 'message' => 'Cart cleared.', 'cart_count' => 0));

        default:
            json_out(array('error' => 'Invalid request.'));
    }
}

/* ---------------- Display cart ---------------- */
if (!$user) {
    flash_set('warning', 'Please login to view your cart.');
    remember_redirect();
    header('Location: login.php');
    exit;
}

$totals = cart_totals();
$items  = $totals['items'];

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>My Cart</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; Cart</div>
    </div>
</section>

<div class="container">
    <?php if (empty($items)) { ?>
        <div class="empty-state card">
            <div class="empty-icon">&#128722;</div>
            <h3>Your cart is empty</h3>
            <p>Looks like you have not added anything yet. Start shopping now!</p>
            <a href="shop.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php } else { ?>
        <div class="cart-layout">
            <div class="card" style="padding:0; overflow:hidden;">
                <div class="table-responsive">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="subtotal-head">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item) { ?>
                                <tr>
                                    <td>
                                        <div class="cart-item">
                                            <img src="<?php echo e(product_image($item['image'])); ?>" alt="<?php echo e($item['name']); ?>">
                                            <div>
                                                <div class="item-name"><a href="product.php?id=<?php echo (int)$item['id']; ?>"><?php echo e($item['name']); ?></a></div>
                                                <div class="item-cat"><?php echo e($item['category_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price-col"><?php echo money(product_price($item)); ?></td>
                                    <td>
                                        <div class="qty-controls" data-id="<?php echo (int)$item['cart_id']; ?>">
                                            <button type="button" class="qty-minus">&minus;</button>
                                            <input type="text" class="qty-input" value="<?php echo (int)$item['quantity']; ?>" max="<?php echo (int)$item['stock']; ?>">
                                            <button type="button" class="qty-plus">+</button>
                                        </div>
                                    </td>
                                    <td class="price-col subtotal-cell"><?php echo money(product_price($item) * (int)$item['quantity']); ?></td>
                                    <td>
                                        <button type="button" class="remove-btn cart-remove" data-id="<?php echo (int)$item['cart_id']; ?>" title="Remove item" aria-label="Remove item">&#128465;</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:14px 20px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <a href="shop.php" class="btn btn-outline btn-sm">&larr; Continue Shopping</a>
                    <form method="post" action="cart.php" data-confirm-form="Clear all items from your cart?">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-danger btn-sm">Clear Cart</button>
                    </form>
                </div>
            </div>

            <aside class="summary-box">
                <h3>Order Summary</h3>
                <div class="summary-row"><span>Subtotal</span> <b id="cartSubtotal"><?php echo money($totals['subtotal']); ?></b></div>
                <div class="summary-row"><span>Shipping</span>
                    <b id="cartShipping"><?php echo $totals['shipping'] == 0 ? '<span class="free">FREE</span>' : money($totals['shipping']); ?></b>
                </div>
                <div class="summary-row total"><span>Grand Total</span> <span id="cartTotal"><?php echo money($totals['total']); ?></span></div>
                <p class="text-muted" style="font-size:12px;">Free shipping on orders above Rs. 999.</p>
                <a href="checkout.php" class="btn btn-success btn-lg btn-block mt-3">Proceed to Checkout</a>
            </aside>
        </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>