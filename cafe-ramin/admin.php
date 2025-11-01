<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth-check.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// دریافت محصولات
$query = "SELECT p.*, c.name as category_name, c.type as category_type 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY c.type, c.name, p.name";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دریافت دسته‌بندی‌ها
$query = "SELECT * FROM categories ORDER BY type, name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دریافت تنظیمات
$query = "SELECT * FROM settings";
$stmt = $db->prepare($query);
$stmt->execute();
$settings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$settings = [];
foreach($settings_data as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// دریافت شبکه‌های اجتماعی
$query = "SELECT * FROM social_media ORDER BY platform";
$stmt = $db->prepare($query);
$stmt->execute();
$social_media = $stmt->fetchAll(PDO::FETCH_ASSOC);

// گروه‌بندی دسته‌بندی‌ها
$coffee_categories = array_filter($categories, function($cat) {
    return $cat['type'] == 'coffee';
});
$food_categories = array_filter($categories, function($cat) {
    return $cat['type'] == 'food';
});

// پردازش فرم‌ها
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                $success = true;
                foreach (['address', 'phone', 'working_hours', 'description'] as $key) {
                    if (isset($_POST[$key])) {
                        if (!updateSetting($key, $_POST[$key])) {
                            $success = false;
                        }
                    }
                }
                if ($success) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'تنظیمات با موفقیت به‌روزرسانی شد'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در به‌روزرسانی تنظیمات'];
                }
                header('Location: admin.php');
                exit;
                
            case 'add_product':
                if (isset($_POST['name']) && isset($_POST['price']) && isset($_POST['category_id'])) {
                    $description = $_POST['description'] ?? '';
                    $is_available = isset($_POST['is_available']) ? 1 : 0;
                    $image = null;
                    
                    // آپلود عکس
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $image = uploadProductImage($_FILES['image']);
                        if (!$image) {
                            $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در آپلود عکس'];
                            header('Location: admin.php');
                            exit;
                        }
                    }
                    
                    if (addProduct($_POST['name'], $description, $_POST['price'], $_POST['category_id'], $is_available, $image)) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'محصول با موفقیت اضافه شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در افزودن محصول'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'update_product':
                if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['price']) && isset($_POST['category_id'])) {
                    $description = $_POST['description'] ?? '';
                    $is_available = isset($_POST['is_available']) ? 1 : 0;
                    $image = null;
                    
                    // آپلود عکس جدید
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $image = uploadProductImage($_FILES['image']);
                        if (!$image) {
                            $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در آپلود عکس'];
                            header('Location: admin.php');
                            exit;
                        }
                        
                        // حذف عکس قبلی
                        $query = "SELECT image FROM products WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id', $_POST['id']);
                        $stmt->execute();
                        $old_product = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($old_product && $old_product['image']) {
                            deleteProductImage($old_product['image']);
                        }
                    }
                    
                    if (updateProduct($_POST['id'], $_POST['name'], $description, $_POST['price'], $_POST['category_id'], $is_available, $image)) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'محصول با موفقیت به‌روزرسانی شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در به‌روزرسانی محصول'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'update_social':
                if (isset($_POST['platform']) && isset($_POST['url'])) {
                    if (updateSocialMedia($_POST['platform'], $_POST['url'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'شبکه اجتماعی با موفقیت به‌روزرسانی شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در به‌روزرسانی شبکه اجتماعی'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'add_category':
                if (isset($_POST['name']) && isset($_POST['type'])) {
                    if (addCategory($_POST['name'], $_POST['type'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'دسته‌بندی با موفقیت اضافه شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در افزودن دسته‌بندی'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'update_category':
                if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['type'])) {
                    if (updateCategory($_POST['id'], $_POST['name'], $_POST['type'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'دسته‌بندی با موفقیت به‌روزرسانی شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در به‌روزرسانی دسته‌بندی'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'change_password':
                if (isset($_POST['current_password']) && isset($_POST['new_password'])) {
                    if (changePassword($_SESSION['user_id'], $_POST['current_password'], $_POST['new_password'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'رمز عبور با موفقیت تغییر کرد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'رمز عبور فعلی اشتباه است'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'delete_product':
                if (isset($_POST['id'])) {
                    if (deleteProduct($_POST['id'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'محصول با موفقیت حذف شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در حذف محصول'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'delete_category':
                if (isset($_POST['id'])) {
                    if (deleteCategory($_POST['id'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'دسته‌بندی با موفقیت حذف شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در حذف دسته‌بندی'];
                    }
                }
                header('Location: admin.php');
                exit;
                
            case 'delete_social':
                if (isset($_POST['id'])) {
                    if (deleteSocial($_POST['id'])) {
                        $_SESSION['message'] = ['type' => 'success', 'text' => 'شبکه اجتماعی با موفقیت حذف شد'];
                    } else {
                        $_SESSION['message'] = ['type' => 'error', 'text' => 'خطا در حذف شبکه اجتماعی'];
                    }
                }
                header('Location: admin.php');
                exit;
        }
    }
}

// نمایش پیام
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت - کافه سوخاری رامین</title>
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
            <?php if (isset($message)): ?>
            <div class="notification <?php echo $message['type']; ?>">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check' : 'exclamation'; ?>"></i>
                <span><?php echo $message['text']; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="admin-panel">
                <div class="admin-header">
                    <h1>
                        <i class="fas fa-cogs"></i>
                        پنل مدیریت
                    </h1>
                    <div class="admin-actions">
                        <div class="user-menu">
                            <span class="user-welcome">
                                <i class="fas fa-user-circle"></i>
                                <?php echo $_SESSION['username']; ?>
                            </span>
                            <div class="dropdown-content">
                                <a href="#" onclick="showChangePassword()">
                                    <i class="fas fa-key"></i>
                                    تغییر رمز عبور
                                </a>
                                <a href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i>
                                    خروج
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="admin-tabs">
                    <button class="tab-btn active" onclick="openTab('products')">
                        <i class="fas fa-utensils"></i>
                        مدیریت منو
                    </button>
                    <button class="tab-btn" onclick="openTab('categories')">
                        <i class="fas fa-tags"></i>
                        دسته‌بندی‌ها
                    </button>
                    <button class="tab-btn" onclick="openTab('settings')">
                        <i class="fas fa-cog"></i>
                        تنظیمات
                    </button>
                    <button class="tab-btn" onclick="openTab('social')">
                        <i class="fas fa-share-alt"></i>
                        شبکه‌های اجتماعی
                    </button>
                </div>
                
                <!-- تب مدیریت محصولات -->
                <div id="products" class="tab-content active">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-list"></i>
                            مدیریت محصولات
                        </h2>
                        <button class="btn btn-primary" onclick="showAddProductForm()">
                            <i class="fas fa-plus"></i>
                            افزودن محصول جدید
                        </button>
                    </div>
                    
                    <div class="products-grid">
                        <?php foreach($products as $product): ?>
                        <div class="product-card" data-id="<?php echo $product['id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo getProductImagePath($product['image']); ?>" alt="<?php echo $product['name']; ?>">
                                <div class="product-badge <?php echo $product['category_type']; ?>">
                                    <?php echo $product['category_type'] == 'coffee' ? 'کافه' : 'غذا'; ?>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?php echo $product['name']; ?></h3>
                                <p><?php echo $product['description']; ?></p>
                                <div class="product-meta">
                                    <span class="price"><?php echo number_format($product['price']); ?> تومان</span>
                                    <span class="category"><?php echo $product['category_name']; ?></span>
                                    <span class="status <?php echo $product['is_available'] ? 'available' : 'unavailable'; ?>">
                                        <i class="fas fa-circle"></i>
                                        <?php echo $product['is_available'] ? 'فعال' : 'غیرفعال'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-edit" onclick="editProduct(<?php echo $product['id']; ?>, '<?php echo $product['name']; ?>', '<?php echo addslashes($product['description']); ?>', <?php echo $product['price']; ?>, <?php echo $product['category_id']; ?>, <?php echo $product['is_available']; ?>, '<?php echo $product['image']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- تب مدیریت دسته‌بندی‌ها -->
                <div id="categories" class="tab-content">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-folder"></i>
                            مدیریت دسته‌بندی‌ها
                        </h2>
                        <button class="btn btn-primary" onclick="showAddCategoryForm()">
                            <i class="fas fa-plus"></i>
                            افزودن دسته‌بندی جدید
                        </button>
                    </div>
                    
                    <div class="categories-container">
                        <div class="category-type-section">
                            <h3>دسته‌بندی‌های کافه</h3>
                            <div class="categories-grid">
                                <?php foreach($coffee_categories as $category): ?>
                                <div class="category-card">
                                    <div class="category-icon">
                                        <i class="fas fa-mug-hot"></i>
                                    </div>
                                    <div class="category-info">
                                        <h4><?php echo $category['name']; ?></h4>
                                        <span class="category-type">منوی کافه</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="btn btn-edit" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>', '<?php echo $category['type']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="category-type-section">
                            <h3>دسته‌بندی‌های غذا</h3>
                            <div class="categories-grid">
                                <?php foreach($food_categories as $category): ?>
                                <div class="category-card">
                                    <div class="category-icon">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                    <div class="category-info">
                                        <h4><?php echo $category['name']; ?></h4>
                                        <span class="category-type">منوی غذا</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="btn btn-edit" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>', '<?php echo $category['type']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- تب تنظیمات -->
                <div id="settings" class="tab-content">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-sliders-h"></i>
                            تنظیمات عمومی
                        </h2>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="update_settings">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    آدرس:
                                </label>
                                <textarea id="address" name="address" rows="3"><?php echo $settings['address'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i>
                                    شماره تماس:
                                </label>
                                <input type="text" id="phone" name="phone" value="<?php echo $settings['phone'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="working_hours">
                                    <i class="fas fa-clock"></i>
                                    ساعات کاری:
                                </label>
                                <input type="text" id="working_hours" name="working_hours" value="<?php echo $settings['working_hours'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="description">
                                    <i class="fas fa-info-circle"></i>
                                    توضیحات درباره کافه:
                                </label>
                                <textarea id="description" name="description" rows="4"><?php echo $settings['description'] ?? ''; ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            ذخیره تنظیمات
                        </button>
                    </form>
                </div>
                
                <!-- تب شبکه‌های اجتماعی -->
                <div id="social" class="tab-content">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-hashtag"></i>
                            مدیریت شبکه‌های اجتماعی
                        </h2>
                        <button class="btn btn-primary" onclick="showAddSocialForm()">
                            <i class="fas fa-plus"></i>
                            افزودن شبکه اجتماعی
                        </button>
                    </div>
                    
                    <div class="social-grid">
                        <?php foreach($social_media as $social): ?>
                        <div class="social-card">
                            <div class="social-icon">
                                <i class="fab fa-<?php echo $social['platform']; ?>"></i>
                            </div>
                            <div class="social-info">
                                <h4><?php echo ucfirst($social['platform']); ?></h4>
                                <p><?php echo $social['url']; ?></p>
                            </div>
                            <div class="social-actions">
                                <button class="btn btn-edit" onclick="editSocial('<?php echo $social['platform']; ?>', '<?php echo $social['url']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_social">
                                    <input type="hidden" name="id" value="<?php echo $social['id']; ?>">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('آیا از حذف این شبکه اجتماعی مطمئن هستید؟')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال‌ها -->
    <!-- مودال افزودن محصول -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-plus"></i>
                    افزودن محصول جدید
                </h3>
                <button class="close-btn" onclick="hideModal('addProductModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <div class="form-group">
                    <label for="product_name">نام محصول:</label>
                    <input type="text" id="product_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="product_description">توضیحات:</label>
                    <textarea id="product_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="product_price">قیمت (تومان):</label>
                        <input type="number" id="product_price" name="price" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_category">دسته‌بندی:</label>
                        <select id="product_category" name="category_id" required>
                            <option value="">انتخاب دسته‌بندی</option>
                            <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?> (<?php echo $category['type'] == 'coffee' ? 'کافه' : 'غذا'; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="product_image">عکس محصول:</label>
                    <input type="file" id="product_image" name="image" accept="image/*">
                    <small>فرمت‌های مجاز: JPG, JPEG, PNG, GIF, WebP (حداکثر 5MB)</small>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_available" value="1" checked>
                        <span class="checkmark"></span>
                        محصول فعال است
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('addProductModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره محصول</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال ویرایش محصول -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-edit"></i>
                    ویرایش محصول
                </h3>
                <button class="close-btn" onclick="hideModal('editProductModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" id="edit_product_id" name="id">
                
                <div class="form-group">
                    <label for="edit_product_name">نام محصول:</label>
                    <input type="text" id="edit_product_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_description">توضیحات:</label>
                    <textarea id="edit_product_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_product_price">قیمت (تومان):</label>
                        <input type="number" id="edit_product_price" name="price" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_product_category">دسته‌بندی:</label>
                        <select id="edit_product_category" name="category_id" required>
                            <option value="">انتخاب دسته‌بندی</option>
                            <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?> (<?php echo $category['type'] == 'coffee' ? 'کافه' : 'غذا'; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_product_image">عکس محصول:</label>
                    <input type="file" id="edit_product_image" name="image" accept="image/*">
                    <small>فرمت‌های مجاز: JPG, JPEG, PNG, GIF, WebP (حداکثر 5MB)</small>
                    <div id="current_image_preview" class="image-preview"></div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_product_available" name="is_available" value="1">
                        <span class="checkmark"></span>
                        محصول فعال است
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('editProductModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال افزودن دسته‌بندی -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-plus"></i>
                    افزودن دسته‌بندی جدید
                </h3>
                <button class="close-btn" onclick="hideModal('addCategoryModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_category">
                <div class="form-group">
                    <label for="category_name">نام دسته‌بندی:</label>
                    <input type="text" id="category_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="category_type">نوع منو:</label>
                    <select id="category_type" name="type" required>
                        <option value="">انتخاب نوع</option>
                        <option value="coffee">منوی کافه</option>
                        <option value="food">منوی غذا</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('addCategoryModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره دسته‌بندی</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال ویرایش دسته‌بندی -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-edit"></i>
                    ویرایش دسته‌بندی
                </h3>
                <button class="close-btn" onclick="hideModal('editCategoryModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="update_category">
                <input type="hidden" id="edit_category_id" name="id">
                
                <div class="form-group">
                    <label for="edit_category_name">نام دسته‌بندی:</label>
                    <input type="text" id="edit_category_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_category_type">نوع منو:</label>
                    <select id="edit_category_type" name="type" required>
                        <option value="">انتخاب نوع</option>
                        <option value="coffee">منوی کافه</option>
                        <option value="food">منوی غذا</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('editCategoryModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال ویرایش شبکه اجتماعی -->
    <div id="editSocialModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-edit"></i>
                    ویرایش شبکه اجتماعی
                </h3>
                <button class="close-btn" onclick="hideModal('editSocialModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="update_social">
                <div class="form-group">
                    <label for="edit_social_platform">پلتفرم:</label>
                    <input type="text" id="edit_social_platform" name="platform" readonly>
                </div>
                
                <div class="form-group">
                    <label for="edit_social_url">لینک:</label>
                    <input type="url" id="edit_social_url" name="url" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('editSocialModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال افزودن شبکه اجتماعی -->
    <div id="addSocialModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-plus"></i>
                    افزودن شبکه اجتماعی
                </h3>
                <button class="close-btn" onclick="hideModal('addSocialModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="update_social">
                <div class="form-group">
                    <label for="social_platform">پلتفرم:</label>
                    <select id="social_platform" name="platform" required>
                        <option value="">انتخاب پلتفرم</option>
                        <option value="instagram">اینستاگرام</option>
                        <option value="telegram">تلگرام</option>
                        <option value="whatsapp">واتس‌اپ</option>
                        <option value="twitter">توییتر</option>
                        <option value="facebook">فیسبوک</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="social_url">لینک:</label>
                    <input type="url" id="social_url" name="url" required placeholder="https://...">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('addSocialModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره شبکه اجتماعی</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال تغییر رمز عبور -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-key"></i>
                    تغییر رمز عبور
                </h3>
                <button class="close-btn" onclick="hideModal('changePasswordModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="current_password">رمز عبور فعلی:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">رمز عبور جدید:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تکرار رمز عبور جدید:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('changePasswordModal')">لغو</button>
                    <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/admin.js"></script>
</body>
</html>