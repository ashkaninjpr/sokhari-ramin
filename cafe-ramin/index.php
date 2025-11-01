<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>کافه سوخاری رامین - صفحه اصلی</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="bg-image" style="background-image: url('images/BG.JPG');"></div>
        <div class="overlay"></div>
        
        <div class="content">
            <div class="welcome-section">
                <div class="hero-content">
                    <h1 class="hero-title">به کافه سوخاری رامین خوش آمدید</h1>
                    <p class="hero-subtitle">لذت طعم بی‌نظیر در فضایی دلنشین</p>
                    
                    <div class="menu-options">
                        <div class="option-card" onclick="window.location.href='coffee-menu.php'">
                            <div class="option-icon">
                                <i class="fas fa-mug-hot"></i>
                            </div>
                            <h3>منوی کافه</h3>
                            <p>نوشیدنی‌های گرم و سرد، دسرها و شیرینی‌جات</p>
                            <div class="option-arrow">
                                <i class="fas fa-arrow-left"></i>
                            </div>
                        </div>
                        
                        <div class="option-card" onclick="window.location.href='food-menu.php'">
                            <div class="option-icon">
                                <i class="fas fa-hamburger"></i>
                            </div>
                            <h3>منوی غذا</h3>
                            <p>غذاهای سوخاری، فست فود و ساندویچ‌های ویژه</p>
                            <div class="option-arrow">
                                <i class="fas fa-arrow-left"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>