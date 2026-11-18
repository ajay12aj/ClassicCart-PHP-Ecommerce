<?php
/**
 * Front-end shared footer: page footer and closing scripts.
 */
$footerCategories = get_categories(true);
?>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>About ClassicCart</h4>
                <p>ClassicCart is your trusted online store for quality products at affordable prices.
                Shop smart, shop simple &mdash; everything you need, delivered to your doorstep.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="wishlist.php">Wishlist</a></li>
                    <li><a href="orders.php">My Orders</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <ul>
                    <?php foreach ($footerCategories as $cat) { ?>
                        <li><a href="shop.php?category=<?php echo (int)$cat['id']; ?>"><?php echo e($cat['name']); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact Us</h4>
                <ul class="contact-list">
                    <li>&#9993; ajayvishwa7878@.com</li>
                    <li>&#9742; +91 840 194 2581</li>
                    <li>&#127968; ClassicCart, Bhestan, Surat, India</li>
                </ul>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook">&#119136;</a>
                    <a href="#" aria-label="Twitter">&#120143;</a>
                    <a href="#" aria-label="Instagram">&#128247;</a>
                    <a href="#" aria-label="YouTube">&#9654;</a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ClassicCart. All rights reserved. Built with Core PHP &amp; MySQL.</p>
        </div>
    </div>
</footer>

<script src="assets/vendor/jquery.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>