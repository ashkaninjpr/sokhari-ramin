<?php
$current_page = basename($_SERVER['PHP_SELF']);

$database = new Database();
$db = $database->getConnection();

$query = "SELECT setting_value FROM settings WHERE setting_key = 'working_hours'";
$stmt = $db->prepare($query);
$stmt->execute();
$working_hours = $stmt->fetchColumn();

$query = "SELECT setting_value FROM settings WHERE setting_key = 'phone'";
$stmt = $db->prepare($query);
$stmt->execute();
$phone = $stmt->fetchColumn();
?>
<header class="main-header">
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php" class="logo">
                <i class="fas fa-leaf"></i>
                <span>کافه رامین</span>
            </a>
        </div>
        
        <div class="nav-links">
            <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                صفحه اصلی
            </a>
            <a href="coffee-menu.php" class="<?php echo $current_page == 'coffee-menu.php' ? 'active' : ''; ?>">
                <i class="fas fa-mug-hot"></i>
                منوی کافه
            </a>
            <a href="food-menu.php" class="<?php echo $current_page == 'food-menu.php' ? 'active' : ''; ?>">
                <i class="fas fa-hamburger"></i>
                منوی غذا
            </a>
            <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
            <a href="admin.php" class="<?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                پنل مدیریت
            </a>
            <?php else: ?>
            <a href="login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i>
                ورود مدیریت
            </a>
            <?php endif; ?>
        </div>
        
        <div class="nav-contact">
            <div class="working-hours">
                <i class="fas fa-clock"></i>
                <span><?php echo $working_hours ?: '۸ صبح تا ۱۲ شب'; ?></span>
            </div>
            <a href="tel:<?php echo $phone ?: '02112345678'; ?>" class="contact-info">
                <i class="fas fa-phone"></i>
                <span><?php echo $phone ?: '021-12345678'; ?></span>
            </a>
        </div>
        
        <div class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </div>
    </nav>
    
    <div class="mobile-menu">
        <div class="mobile-menu-header">
            <span>منو</span>
            <button class="close-mobile-menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mobile-nav-links">
            <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                صفحه اصلی
            </a>
            <a href="coffee-menu.php" class="<?php echo $current_page == 'coffee-menu.php' ? 'active' : ''; ?>">
                <i class="fas fa-mug-hot"></i>
                منوی کافه
            </a>
            <a href="food-menu.php" class="<?php echo $current_page == 'food-menu.php' ? 'active' : ''; ?>">
                <i class="fas fa-hamburger"></i>
                منوی غذا
            </a>
            <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
            <a href="admin.php" class="<?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                پنل مدیریت
            </a>
            <?php else: ?>
            <a href="login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i>
                ورود مدیریت
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>