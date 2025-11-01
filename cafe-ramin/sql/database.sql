-- ایجاد دیتابیس
CREATE DATABASE IF NOT EXISTS cafe_ramin CHARACTER SET utf8 COLLATE utf8_persian_ci;
USE cafe_ramin;

-- جدول کاربران
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول دسته‌بندی‌ها
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('coffee', 'food') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول محصولات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- جدول تنظیمات
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول شبکه‌های اجتماعی
CREATE TABLE social_media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- درج کاربر پیش‌فرض (رمز: password)
INSERT INTO users (username, password, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم');

-- درج دسته‌بندی‌های پیش‌فرض
INSERT INTO categories (name, type) VALUES 
('نوشیدنی گرم', 'coffee'),
('نوشیدنی سرد', 'coffee'),
('دسر و شیرینی', 'coffee'),
('سوخاری مرغ', 'food'),
('سوخاری ماهی', 'food'),
('پیتزا', 'food'),
('ساندویچ', 'food'),
('پیش غذا', 'food');

-- درج محصولات پیش‌فرض
INSERT INTO products (name, description, price, image, category_id) VALUES 
('اسپرسو', 'قهوه اسپرسو خالص و غلیظ', 45000, 'espresso.jpg', 1),
('لاته', 'ترکیب اسپرسو و شیر بخارپز شده', 65000, 'latte.jpg', 1),
('کاپوچینو', 'اسپرسو با فوم شیر و پودر کاکائو', 60000, 'cappuccino.jpg', 1),
('چای سیاه', 'چای سیاه مرغوب ایرانی', 30000, 'black-tea.jpg', 1),
('آیس لته', 'لاته سرد با یخ', 70000, 'ice-latte.jpg', 2),
('موکا', 'ترکیب قهوه، شکلات و شیر', 75000, 'mocha.jpg', 1),
('سوخاری مرغ ویژه', 'مرغ سوخاری با سس مخصوص', 120000, 'chicken-special.jpg', 4),
('نوگت مرغ', 'نوگت مرغ ترد و طلایی', 90000, 'chicken-nuggets.jpg', 4),
('فیله ماهی سوخاری', 'فیله ماهی سوخاری با سس تارتار', 110000, 'fish-fillet.jpg', 5),
('پیتزا مخلوط', 'پیتزا با پپرونی، قارچ و فلفل', 85000, 'mixed-pizza.jpg', 6),
('همبرگر ویژه', 'همبرگر با گوشت گوساله و پنیر چدار', 85000, 'special-burger.jpg', 7),
('سیب زمینی سرخ کرده', 'سیب زمینی طلایی و ترد', 35000, 'fries.jpg', 8),
('پنکیک شکلاتی', 'پنکیک با سس شکلات و بستنی', 55000, 'chocolate-pancake.jpg', 3),
('چیزکیک', 'چیزکیک خامه‌ای با توت فرنگی', 60000, 'cheesecake.jpg', 3);

-- درج تنظیمات پیش‌فرض
INSERT INTO settings (setting_key, setting_value) VALUES 
('address', 'تهران، خیابان ولیعصر، پلاک ۱۲۳'),
('phone', '۰۲۱-۱۲۳۴۵۶۷۸'),
('working_hours', 'همه روزه از ۸ صبح تا ۱۲ شب'),
('description', 'کافه سوخاری رامین با سال‌ها تجربه در ارائه بهترین نوشیدنی‌ها و غذاهای سوخاری');

-- درج شبکه‌های اجتماعی پیش‌فرض
INSERT INTO social_media (platform, url) VALUES 
('instagram', 'https://instagram.com/cafe_ramin'),
('telegram', 'https://t.me/cafe_ramin'),
('whatsapp', 'https://wa.me/989121234567');