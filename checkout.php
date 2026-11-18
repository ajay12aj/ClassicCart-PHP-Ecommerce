<?php
/**
 * ClassicCart - Checkout Page
 */
$page_title = 'Checkout - ClassicCart';
require_once 'includes/auth.php';
require_login();

$user  = current_user();
$totals = cart_totals();
$items  = $totals['items'];

/* Empty cart guard */
if (empty($items)) {
    flash_set('warning', 'Your cart is empty. Add some products before checking out.');
    header('Location: cart.php');
    exit;
}

$errors = array();
$input = array(
    'name'    => $user['name'],
    'email'   => $user['email'],
    'phone'   => $user['phone'] ?? '',
    'address' => $user['address'] ?? '',
    'city'    => $user['city'] ?? '',
    'state'   => $user['state'] ?? '',
    'pincode' => $user['pincode'] ?? '',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: checkout.php');
        exit;
    }

    $input = array(
        'name'    => trim($_POST['name'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
        'phone'   => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city'    => trim($_POST['city'] ?? ''),
        'state'   => trim($_POST['state'] ?? ''),
        'pincode' => trim($_POST['pincode'] ?? ''),
    );

    if ($input['name'] === '' || strlen($input['name']) < 3) {
        $errors[] = 'Please enter your full name.';
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($input['phone'] === '' || !preg_match('/^[0-9]{10}$/', $input['phone'])) {
        $errors[] = 'Please enter a valid 10 digit phone number.';
    }
    if ($input['address'] === '') {
        $errors[] = 'Please enter your delivery address.';
    }
    if ($input['city'] === '') {
        $errors[] = 'Please enter your city.';
    }
    if ($input['state'] === '') {
        $errors[] = 'Please enter your state.';
    }
    if (!preg_match('/^[0-9]{5,6}$/', $input['pincode'])) {
        $errors[] = 'Please enter a valid pincode.';
    }

    if (empty($errors)) {
        $subtotal = $totals['subtotal'];
        $shipping = $totals['shipping'];
        $total    = $totals['total'];

        db()->beginTransaction();
        try {
            /* Create order record */
            $stmt = db()->prepare(
                'INSERT INTO orders (order_number, user_id, name, email, phone, address, city, state, pincode, subtotal, shipping, total, payment_method, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute(array(
                'CC-' . date('Ymd') . '-0000',
                $user['id'],
                $input['name'],
                $input['email'],
                $input['phone'],
                $input['address'],
                $input['city'],
                $input['state'],
                $input['pincode'],
                $subtotal,
                $shipping,
                $total,
                'Cash on Delivery',
                'Pending',
            ));
            $orderId = (int)db()->lastInsertId();
            $orderNumber = 'CC-' . date('Ymd') . '-' . (1000 + $orderId);
            db()->prepare('UPDATE orders SET order_number = ? WHERE id = ?')->execute(array($orderNumber, $orderId));

            /* Insert order items and validate/decrease stock */
            foreach ($items as $item) {
                $productId = (int)$item['id'];
                $qty       = (int)$item['quantity'];
                $stock     = (int)$item['stock'];

                if ($stock < $qty) {
                    throw new Exception('Insufficient stock for "' . $item['name'] . '".');
                }

                $price = product_price($item);
                db()->prepare(
                    'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)'
                )->execute(array($orderId, $productId, $item['name'], $price, $qty, $price * $qty));

                db()->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute(array($qty, $productId));
            }

            /* Clear user cart */
            db()->prepare('DELETE FROM cart WHERE user_id = ?')->execute(array($user['id']));

            db()->commit();

            flash_set('success', 'Your order has been placed successfully.');
            header('Location: order-success.php?order=' . $orderId);
            exit;
        } catch (Exception $ex) {
            db()->rollBack();
            $errors[] = 'Something went wrong while placing your order: ' . $ex->getMessage();
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $err) {
            flash_set('error', $err);
        }
    }
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; <a href="cart.php">Cart</a> &raquo; Checkout</div>
    </div>
</section>

<div class="container">
    <div class="checkout-layout">
        <div class="card">
            <div class="card-header">
                <h3>Shipping Details</h3>
            </div>
            <form method="post" action="checkout.php" id="checkoutForm" novalidate>
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($input['name']); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo e($input['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($input['phone']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" required><?php echo e($input['address']); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?php echo e($input['city']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" class="form-control" value="<?php echo e($input['state']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode" class="form-control" value="<?php echo e($input['pincode']); ?>" maxlength="6" required>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="method-box">
                        <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                        <span><b>Cash on Delivery</b><br><small class="text-muted">Pay in cash when your order arrives.</small></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg btn-block">Place Order</button>
            </form>
        </div>

        <aside class="card delivery-summary">
            <h3 style="margin-bottom:14px;">Order Summary</h3>
            <?php foreach ($items as $item) { ?>
                <div class="summary-item">
                    <img src="<?php echo e(product_image($item['image'])); ?>" alt="<?php echo e($item['name']); ?>">
                    <div style="flex:1;">
                        <div class="s-name"><?php echo e($item['name']); ?></div>
                        <div class="s-price"><?php echo money(product_price($item)); ?> &times; <?php echo (int)$item['quantity']; ?></div>
                    </div>
                    <div class="s-price" style="font-weight:600;"><?php echo money(product_price($item) * (int)$item['quantity']); ?></div>
                </div>
            <?php } ?>
            <div class="summary-row" style="margin-top:12px;"><span>Subtotal</span> <b><?php echo money($totals['subtotal']); ?></b></div>
            <div class="summary-row"><span>Shipping</span>
                <b><?php echo $totals['shipping'] == 0 ? '<span class="free">FREE</span>' : money($totals['shipping']); ?></b>
            </div>
            <div class="summary-row total"><span>Grand Total</span> <span><?php echo money($totals['total']); ?></span></div>
        </aside>
    </div>
</div>

<?php include 'includes/footer.php'; ?>