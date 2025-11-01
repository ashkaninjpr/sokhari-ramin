// فیلتر و جستجو در منو
document.addEventListener('DOMContentLoaded', function() {
    // ایجاد فیلتر برای دسته‌بندی‌ها (اگر لازم باشد)
    const menuGrid = document.querySelector('.menu-grid');
    if (!menuGrid) return;

    // افزودن قابلیت جستجو
    const searchContainer = document.createElement('div');
    searchContainer.className = 'search-container';
    searchContainer.innerHTML = `
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="menu-search" placeholder="جستجو در منو...">
        </div>
    `;

    const pageHeader = document.querySelector('.page-header');
    if (pageHeader) {
        pageHeader.parentNode.insertBefore(searchContainer, pageHeader.nextSibling);
    }

    // استایل جستجو
    const searchStyle = document.createElement('style');
    searchStyle.textContent = `
        .search-container {
            margin: 20px 0 40px;
        }
        
        .search-box {
            position: relative;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2E7D32;
        }
        
        #menu-search {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Vazirmatn', sans-serif;
        }
        
        #menu-search:focus {
            outline: none;
            border-color: #2E7D32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
    `;
    document.head.appendChild(searchStyle);

    // عملکرد جستجو
    const searchInput = document.getElementById('menu-search');
    const menuItems = document.querySelectorAll('.menu-item');

    if (searchInput && menuItems.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            menuItems.forEach(item => {
                const itemName = item.querySelector('h3').textContent.toLowerCase();
                const itemDescription = item.querySelector('p').textContent.toLowerCase();
                
                if (itemName.includes(searchTerm) || itemDescription.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // انیمیشن برای آیتم‌های منو
    const menuObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
            }
        });
    }, {
        threshold: 0.1
    });

    menuItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.animationDelay = `${index * 0.1}s`;
        menuObserver.observe(item);
    });

    // تعریف انیمیشن
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(animationStyle);
});