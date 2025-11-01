<?php
function getSetting($key, $default = '') {
    global $db;
    $query = "SELECT setting_value FROM settings WHERE setting_key = :key";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    $result = $stmt->fetchColumn();
    return $result ?: $default;
}

function updateSetting($key, $value) {
    global $db;
    $query = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) 
              ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':value', $value);
    return $stmt->execute();
}

function getCategoriesByType($type) {
    global $db;
    $query = "SELECT * FROM categories WHERE type = :type ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// تابع آپلود عکس محصول
function uploadProductImage($file) {
    $target_dir = "images/products/";
    
    // ایجاد پوشه اگر وجود ندارد
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // بررسی نوع فایل
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    // بررسی سایز فایل (حداکثر 5MB)
    if ($file["size"] > 5 * 1024 * 1024) {
        return false;
    }
    
    // آپلود فایل
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    
    return false;
}

// تابع حذف عکس محصول
function deleteProductImage($filename) {
    if ($filename && file_exists("images/products/" . $filename)) {
        unlink("images/products/" . $filename);
    }
}

// تابع گرفتن مسیر عکس محصول
function getProductImagePath($filename) {
    if ($filename && file_exists("images/products/" . $filename)) {
        return "images/products/" . $filename;
    }
    return "images/default-food.jpg";
}

function addProduct($name, $description, $price, $category_id, $is_available, $image = null) {
    global $db;
    $query = "INSERT INTO products (name, description, price, category_id, is_available, image) 
              VALUES (:name, :description, :price, :category_id, :is_available, :image)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':is_available', $is_available);
    $stmt->bindParam(':image', $image);
    return $stmt->execute();
}

function updateProduct($id, $name, $description, $price, $category_id, $is_available, $image = null) {
    global $db;
    
    if ($image) {
        $query = "UPDATE products SET name = :name, description = :description, price = :price, 
                  category_id = :category_id, is_available = :is_available, image = :image, updated_at = NOW() 
                  WHERE id = :id";
    } else {
        $query = "UPDATE products SET name = :name, description = :description, price = :price, 
                  category_id = :category_id, is_available = :is_available, updated_at = NOW() 
                  WHERE id = :id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':is_available', $is_available);
    
    if ($image) {
        $stmt->bindParam(':image', $image);
    }
    
    return $stmt->execute();
}

function addCategory($name, $type) {
    global $db;
    $query = "INSERT INTO categories (name, type) VALUES (:name, :type)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    return $stmt->execute();
}

function updateCategory($id, $name, $type) {
    global $db;
    $query = "UPDATE categories SET name = :name, type = :type WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    return $stmt->execute();
}

function updateSocialMedia($platform, $url) {
    global $db;
    
    // ابتدا بررسی می‌کنیم آیا پلتفرم وجود دارد
    $checkQuery = "SELECT id FROM social_media WHERE platform = :platform";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':platform', $platform);
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // اگر وجود دارد، آپدیت می‌کنیم
        $query = "UPDATE social_media SET url = :url WHERE platform = :platform";
    } else {
        // اگر وجود ندارد، insert می‌کنیم
        $query = "INSERT INTO social_media (platform, url) VALUES (:platform, :url)";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':platform', $platform);
    $stmt->bindParam(':url', $url);
    return $stmt->execute();
}

function changePassword($user_id, $current_password, $new_password) {
    global $db;
    
    // بررسی رمز عبور فعلی
    $query = "SELECT password FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        return false;
    }
    
    // به‌روزرسانی رمز عبور جدید
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET password = :password WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':user_id', $user_id);
    return $stmt->execute();
}

function deleteProduct($id) {
    global $db;
    
    // ابتدا اطلاعات محصول را بگیریم تا عکس آن را حذف کنیم
    $query = "SELECT image FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // حذف عکس محصول
    if ($product && $product['image']) {
        deleteProductImage($product['image']);
    }
    
    // حذف محصول از دیتابیس
    $query = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

function deleteCategory($id) {
    global $db;
    $query = "DELETE FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

function deleteSocial($id) {
    global $db;
    $query = "DELETE FROM social_media WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
?>