<?php
/**
 * Admin sidebar navigation.
 */
?>
<aside class="sidebar" id="sidebar">
    <a href="dashboard.php" class="sidebar-brand">Classic<span>Cart</span> Admin</a>

    <div class="sidebar-label">Main Menu</div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
            <span class="nav-icon">&#9632;</span> Dashboard
        </a>
        <a href="products.php" class="<?php echo $page === 'products' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128230;</span> Products
        </a>
        <a href="categories.php" class="<?php echo $page === 'categories' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128193;</span> Categories
        </a>
        <a href="orders.php" class="<?php echo $page === 'orders' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128196;</span> Orders
        </a>
        <a href="users.php" class="<?php echo $page === 'users' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128101;</span> Users
        </a>
        <a href="settings.php" class="<?php echo $page === 'settings' ? 'active' : ''; ?>">
            <span class="nav-icon">&#9881;</span> Settings
        </a>
    </nav>

    <div class="sidebar-label">Account</div>
    <nav class="sidebar-nav">
        <a href="../index.php">&#127968; View Website</a>
        <a href="logout.php" class="logout-link">&#8617; Logout</a>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999;"></div>