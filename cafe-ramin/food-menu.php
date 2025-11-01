<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php'; // اضافه کردن فایل functions

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, c.name as category_name, c.type as category_type 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE c.type = 'food' AND p.is_available = 1 
          ORDER BY c.name, p.name";
$stmt = $db->prepare($query);
$stmt->execute();
$food_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_products = [];
foreach($food_products as $product) {
    $category = $product['category_name'];
    if(!isset($grouped_products[$category])) {
        $grouped_products[$category] = [];
    }
    $grouped_products[$category][] = $product;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منوی غذا - کافه سوخاری رامین</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="bg-image" style="background-image: url('images/food.jpg');"></div>
        <div class="overlay"></div>
        
        <div class="content">
            <div class="page-header">
                <h1>منوی غذا</h1>
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به صفحه اصلی
                </a>
            </div>
            
            <?php foreach($grouped_products as $category_name => $products): ?>
            <div class="category-section">
                <h2 class="category-title">
                    <i class="fas fa-tag"></i>
                    <?php echo $category_name; ?>
                </h2>
                <div class="menu-grid">
                    <?php foreach($products as $product): ?>
                    <div class="menu-item">
                        <div class="item-image">
                            <img src="<?php echo getProductImagePath($product['image']); ?>" alt="<?php echo $product['name']; ?>">
                            <?php if($product['is_available'] == 0): ?>
                            <div class="out-of-stock">ناموجود</div>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <h3><?php echo $product['name']; ?></h3>
                            <p><?php echo $product['description']; ?></p>
                            <div class="item-meta">
                                <span class="item-price"><?php echo number_format($product['price']); ?> تومان</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/menu.js"></script>
</body>
</html>