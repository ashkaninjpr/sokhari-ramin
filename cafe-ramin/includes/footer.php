<?php
$database = new Database();
$db = $database->getConnection();

$query = "SELECT setting_value FROM settings WHERE setting_key = 'address'";
$stmt = $db->prepare($query);
$stmt->execute();
$address = $stmt->fetchColumn();

$query = "SELECT setting_value FROM settings WHERE setting_key = 'phone'";
$stmt = $db->prepare($query);
$stmt->execute();
$phone = $stmt->fetchColumn();

$query = "SELECT * FROM social_media WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$social_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>
                <i class="fas fa-leaf"></i>
                درباره کافه رامین
            </h3>
            <p>کافه سوخاری رامین با سال‌ها تجربه در ارائه بهترین نوشیدنی‌ها و غذاهای سوخاری در خدمت شماست.</p>
        </div>
        
        <div class="footer-section">
            <h3>
                <i class="fas fa-map-marker-alt"></i>
                اطلاعات تماس
            </h3>
            <div class="contact-info">
                <p><i class="fas fa-location-dot"></i> <?php echo $address ?: 'تهران، خیابان ولیعصر'; ?></p>
                <p><i class="fas fa-phone"></i> <?php echo $phone ?: '021-12345678'; ?></p>
            </div>
        </div>
        
        <div class="footer-section">
            <h3>
                <i class="fas fa-share-alt"></i>
                شبکه‌های اجتماعی
            </h3>
            <div class="social-links">
                <?php foreach($social_links as $social): ?>
                <a href="<?php echo $social['url']; ?>" target="_blank" class="social-link">
                    <i class="fab fa-<?php echo $social['platform']; ?>"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>
            <i class="fas fa-copyright"></i>
            ۲۰۲۴ کافه سوخاری رامین. تمام حقوق محفوظ است.
        </p>
    </div>
</footer>