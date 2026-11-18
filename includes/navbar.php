<?php
/**
 * Front-end navigation bar.
 */
$navCategories = get_categories(true);
$cartCount     = 0;
$wishlistTotal = 0;
if (!empty($current_user)) {
    $cartCount     = cart_count();
    $wishlistTotal = wishlist_count();
}
$active = basename($_SERVER['SCRIPT_NAME']);
?>
<header class="navbar">
    <div class="container navbar-inner">
        <button type="button" class="navbar-toggler" id="navbarToggler" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <a href="index.php" class="navbar-brand">
            <span class="brand-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </span>
            <span class="brand-text">Classic<span>Cart</span></span>
        </a>

        <nav class="navbar-menu" id="navbarMenu">
            <ul class="navbar-nav">
                <li><a href="index.php" class="<?php echo $active === 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="shop.php" class="<?php echo $active === 'shop.php' ? 'active' : ''; ?>">Shop</a></li>
                <li class="has-dropdown">
                    <a href="shop.php">Categories <span class="caret">&#9662;</span></a>
                    <ul class="dropdown">
                        <?php foreach ($navCategories as $cat) { ?>
                            <li><a href="shop.php?category=<?php echo (int)$cat['id']; ?>"><?php echo e($cat['name']); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <li><a href="shop.php?sort=newest">New Arrivals</a></li>
            </ul>
        </nav>

        <div class="navbar-actions">
            <a href="wishlist.php" class="action-link" title="Wishlist">
                <span class="action-icon">&#9829;</span>
                <?php if (!empty($current_user)) { ?><span class="count-badge" id="wishlistCount"><?php echo (int)$wishlistTotal; ?></span><?php } ?>
            </a>
            <a href="cart.php" class="action-link" title="Cart">
                <span class="action-icon">&#128722;</span>
                <?php if (!empty($current_user)) { ?><span class="count-badge" id="cartCount"><?php echo (int)$cartCount; ?></span><?php } ?>
            </a>

            <?php if (!empty($current_user)) { ?>
                <div class="user-menu has-dropdown">
                    <a href="profile.php" class="user-btn">
                        <span class="action-icon">&#128100;</span>
                        <span class="user-name"><?php echo e($current_user['name']); ?></span>
                        <span class="caret">&#9662;</span>
                    </a>
                    <ul class="dropdown dropdown-right">
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="orders.php">My Orders</a></li>
                        <li><a href="wishlist.php">My Wishlist</a></li>
                        <li><a href="cart.php">My Cart</a></li>
                        <li><hr></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php } else { ?>
                <a href="login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="register.php" class="btn btn-primary btn-sm">Register</a>
            <?php } ?>
        </div>
    </div>
</header>