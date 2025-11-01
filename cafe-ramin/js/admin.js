// مدیریت پنل مدیریت
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت منوی کاربر
    const userMenu = document.querySelector('.user-menu');
    
    if (userMenu) {
        let menuTimeout;
        
        userMenu.addEventListener('mouseenter', function() {
            clearTimeout(menuTimeout);
            this.querySelector('.dropdown-content').style.display = 'block';
        });
        
        userMenu.addEventListener('mouseleave', function() {
            const dropdown = this.querySelector('.dropdown-content');
            menuTimeout = setTimeout(() => {
                dropdown.style.display = 'none';
            }, 300);
        });
        
        // برای عناصر dropdown هم همین رفتار را اعمال کنید
        const dropdown = userMenu.querySelector('.dropdown-content');
        if (dropdown) {
            dropdown.addEventListener('mouseenter', function() {
                clearTimeout(menuTimeout);
            });
            
            dropdown.addEventListener('mouseleave', function() {
                menuTimeout = setTimeout(() => {
                    this.style.display = 'none';
                }, 300);
            });
        }
    }

    // مدیریت تب‌ها
    function openTab(tabName) {
        // مخفی کردن تمام تب‌ها
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // غیرفعال کردن تمام دکمه‌ها
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // نمایش تب انتخاب شده
        document.getElementById(tabName).classList.add('active');
        
        // فعال کردن دکمه مربوطه
        event.currentTarget.classList.add('active');
    }

    // قرار دادن تابع در scope全局
    window.openTab = openTab;

    // مدیریت مودال‌ها
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // بستن مودال با کلیک خارج از آن
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            hideModal(event.target.id);
        }
    });

    // مدیریت فرم‌ها
    function showAddProductForm() {
        showModal('addProductModal');
    }

    function showAddCategoryForm() {
        showModal('addCategoryModal');
    }

    function showAddSocialForm() {
        showModal('addSocialModal');
    }

    function showChangePassword() {
        showModal('changePasswordModal');
    }

    // تابع ویرایش محصول
    function editProduct(productId) {
        // پیدا کردن محصول در لیست
        const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
        if (!productCard) {
            showNotification('محصول یافت نشد', 'error');
            return;
        }

        // گرفتن اطلاعات محصول از کارت
        const productName = productCard.querySelector('h3').textContent;
        const productDescription = productCard.querySelector('p').textContent;
        const productPrice = productCard.querySelector('.price').textContent.replace(/[^0-9]/g, '');
        const productCategory = productCard.querySelector('.category').textContent;
        const isAvailable = productCard.querySelector('.status').classList.contains('available');

        // پیدا کردن آیدی دسته‌بندی از نام
        const categorySelect = document.getElementById('edit_product_category');
        let categoryId = '';
        for (let option of categorySelect.options) {
            if (option.text.includes(productCategory)) {
                categoryId = option.value;
                break;
            }
        }

        // پر کردن فرم ویرایش
        document.getElementById('edit_product_id').value = productId;
        document.getElementById('edit_product_name').value = productName;
        document.getElementById('edit_product_description').value = productDescription;
        document.getElementById('edit_product_price').value = productPrice;
        document.getElementById('edit_product_category').value = categoryId;
        document.getElementById('edit_product_available').checked = isAvailable;

        // نمایش مودال ویرایش
        showModal('editProductModal');
    }

    function deleteProduct(productId) {
        if (confirm('آیا از حذف این محصول مطمئن هستید؟')) {
            // شبیه‌سازی درخواست حذف
            const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
            if (productCard) {
                productCard.style.opacity = '0';
                productCard.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    productCard.remove();
                    showNotification('محصول با موفقیت حذف شد', 'success');
                }, 300);
            }
        }
    }

    // === توابع اصلاح شده برای دسته‌بندی‌ها ===

    // ویرایش دسته‌بندی - کاملاً اصلاح شده
    function editCategory(id, name, type) {
        console.log('ویرایش دسته‌بندی:', id, name, type);
        
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_category_name').value = name;
        document.getElementById('edit_category_type').value = type;
        
        showModal('editCategoryModal');
    }

    // حذف دسته‌بندی - کاملاً اصلاح شده
    function deleteCategory(id) {
        if (confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟')) {
            // ایجاد فرم برای ارسال درخواست حذف
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_category';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // ویرایش شبکه‌های اجتماعی - کاملاً اصلاح شده
    function editSocial(platform, url) {
        console.log('ویرایش شبکه اجتماعی:', platform, url);
        
        document.getElementById('edit_social_platform').value = platform;
        document.getElementById('edit_social_url').value = url;
        
        showModal('editSocialModal');
    }

    function deleteSocial(socialId) {
        if (confirm('آیا از حذف این شبکه اجتماعی مطمئن هستید؟')) {
            showNotification('شبکه اجتماعی با موفقیت حذف شد', 'success');
        }
    }

    // قرار دادن توابع در scope全局
    window.showAddProductForm = showAddProductForm;
    window.showAddCategoryForm = showAddCategoryForm;
    window.showAddSocialForm = showAddSocialForm;
    window.showChangePassword = showChangePassword;
    window.editProduct = editProduct;
    window.deleteProduct = deleteProduct;
    window.editCategory = editCategory;
    window.deleteCategory = deleteCategory;
    window.editSocial = editSocial;
    window.deleteSocial = deleteSocial;
    window.hideModal = hideModal;

    // آپلود عکس
    function handleImageUpload(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewId = input.id === 'edit_product_image' ? 'editImagePreview' : 'imagePreview';
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
            }
            reader.readAsDataURL(file);
        }
    }

    // اعتبارسنجی فرم‌ها
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.style.borderColor = '#f44336';
                isValid = false;
            } else {
                input.style.borderColor = '#ddd';
            }
        });

        return isValid;
    }

    // مدیریت ارسال فرم‌ها
    document.addEventListener('submit', function(event) {
        const form = event.target;
        
        if (form.id === 'settingsForm') {
            event.preventDefault();
            showNotification('تنظیمات با موفقیت ذخیره شد', 'success');
        }
        
        if (form.id === 'addProductForm') {
            event.preventDefault();
            
            if (!validateForm(form)) {
                showNotification('لطفا تمام فیلدهای ضروری را پر کنید', 'error');
                return;
            }

            showNotification('محصول با موفقیت اضافه شد', 'success');
            hideModal('addProductModal');
            form.reset();
            document.getElementById('imagePreview').innerHTML = '';
        }
        
        if (form.id === 'editProductForm') {
            event.preventDefault();
            
            if (!validateForm(form)) {
                showNotification('لطفا تمام فیلدهای ضروری را پر کنید', 'error');
                return;
            }

            const productId = document.getElementById('edit_product_id').value;
            
            // شبیه‌سازی به‌روزرسانی محصول
            const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
            if (productCard) {
                const productName = document.getElementById('edit_product_name').value;
                const productDescription = document.getElementById('edit_product_description').value;
                const productPrice = document.getElementById('edit_product_price').value;
                const productCategory = document.getElementById('edit_product_category');
                const categoryName = productCategory.options[productCategory.selectedIndex].text.split(' (')[0];
                const isAvailable = document.getElementById('edit_product_available').checked;

                // به‌روزرسانی اطلاعات در کارت
                productCard.querySelector('h3').textContent = productName;
                productCard.querySelector('p').textContent = productDescription;
                productCard.querySelector('.price').textContent = `${parseInt(productPrice).toLocaleString()} تومان`;
                productCard.querySelector('.category').textContent = categoryName;
                
                // به‌روزرسانی وضعیت
                const statusElement = productCard.querySelector('.status');
                statusElement.textContent = isAvailable ? 'فعال' : 'غیرفعال';
                statusElement.className = `status ${isAvailable ? 'available' : 'unavailable'}`;
                statusElement.innerHTML = `<i class="fas fa-circle"></i> ${isAvailable ? 'فعال' : 'غیرفعال'}`;
            }

            showNotification('محصول با موفقیت ویرایش شد', 'success');
            hideModal('editProductModal');
            form.reset();
            document.getElementById('editImagePreview').innerHTML = '';
        }
        
        if (form.id === 'addCategoryForm') {
            event.preventDefault();
            
            if (!validateForm(form)) {
                showNotification('لطفا تمام فیلدهای ضروری را پر کنید', 'error');
                return;
            }

            showNotification('دسته‌بندی با موفقیت اضافه شد', 'success');
            hideModal('addCategoryModal');
            form.reset();
        }

        if (form.id === 'editCategoryForm') {
            event.preventDefault();
            
            if (!validateForm(form)) {
                showNotification('لطفا تمام فیلدهای ضروری را پر کنید', 'error');
                return;
            }

            showNotification('دسته‌بندی با موفقیت ویرایش شد', 'success');
            hideModal('editCategoryModal');
        }
        
        if (form.id === 'changePasswordForm') {
            event.preventDefault();
            
            const newPassword = form.querySelector('#new_password').value;
            const confirmPassword = form.querySelector('#confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                showNotification('رمز عبور جدید و تکرار آن مطابقت ندارند', 'error');
                return;
            }
            
            showNotification('رمز عبور با موفقیت تغییر کرد', 'success');
            hideModal('changePasswordModal');
            form.reset();
        }
    });

    // مدیریت آپلود عکس
    document.addEventListener('change', function(event) {
        if (event.target.type === 'file' && event.target.accept.includes('image')) {
            handleImageUpload(event.target);
        }
    });

    // نمایش نوتیفیکیشن (اگر وجود ندارد)
    if (typeof showNotification === 'undefined') {
        window.showNotification = function(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        };
    }
});