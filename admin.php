<?php
// admin.php - Enhanced with languages, permissions, and improved functionality
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_config.php';

// --- Language System ---
$lang = $_SESSION['language'] ?? $_COOKIE['language'] ?? 'ar';
if (isset($_POST['switch_language'])) {
    $lang = $_POST['switch_language'] === 'en' ? 'en' : 'ar';
    $_SESSION['language'] = $lang;
    setcookie('language', $lang, time() + (365 * 24 * 60 * 60), '/');
}

// Language texts
$texts = [
    'ar' => [
        'administration' => 'إدارة',
        'logout' => 'تسجيل الخروج',
        'all_events' => 'كل الحفلات',
        'dashboard' => 'لوحة المتابعة',
        'manage_guests' => 'إدارة الضيوف',
        'send_invitations' => 'إرسال الدعوات',
        'checkin' => 'تسجيل الدخول',
        'view_registration' => 'عرض صفحة التسجيل',
        'event_settings' => 'إعدادات المناسبة',
        'user_management' => 'إدارة المستخدمين',
        'event_details' => 'تفاصيل المناسبة',
        'qr_settings' => 'إعدادات بطاقة QR',
        'webhook_settings' => 'إعدادات Webhook (n8n)',
        'save_all_settings' => 'حفظ كل الإعدادات',
        'add_new_user' => 'إضافة مستخدم جديد',
        'current_users' => 'المستخدمون الحاليون',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'role' => 'الدور',
        'assigned_event' => 'الحفل المخصص',
        'actions' => 'إجراءات',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'add_user' => 'إضافة مستخدم',
        'admin' => 'مسؤول',
        'viewer' => 'مشاهد',
        'checkin_user' => 'مسجل دخول',
        'all_events_access' => '(كل الحفلات)',
        'not_specified' => 'غير محدد',
        'confirm_delete_user' => 'هل أنت متأكد من حذف هذا المستخدم؟',
        'event_name' => 'اسم المناسبة',
        'google_maps_link' => 'رابط خرائط Google',
        'event_date_ar' => 'تاريخ المناسبة (عربي)',
        'event_date_en' => 'تاريخ المناسبة (إنجليزي)',
        'venue_ar' => 'مكان الحفل (عربي)',
        'venue_en' => 'مكان الحفل (إنجليزي)',
        'event_description_ar' => 'وصف المناسبة (عربي) - يظهر في حال عدم وجود صورة',
        'event_description_en' => 'وصف المناسبة (إنجليزي)',
        'background_image' => 'صورة الخلفية',
        'current_image' => 'الصورة الحالية',
        'remove_current_image' => 'إزالة الصورة الحالية عند الحفظ',
        'upload_new_image' => 'رفع صورة جديدة',
        'new_image_preview' => 'معاينة الصورة الجديدة',
        'cancel_selection' => 'إلغاء الاختيار',
        'qr_card_title_ar' => 'عنوان بطاقة QR (عربي)',
        'qr_card_title_en' => 'عنوان بطاقة QR (إنجليزي)',
        'qr_instructions_ar' => 'تعليمات إظهار الكود (عربي)',
        'qr_instructions_en' => 'تعليمات إظهار الكود (إنجليزي)',
        'qr_brand_ar' => 'نص العلامة التجارية (عربي)',
        'qr_brand_en' => 'نص العلامة التجارية (إنجليزي)',
        'qr_website' => 'موقع الويب على البطاقة',
        'webhook_confirm' => 'Webhook لتأكيد الحضور',
        'webhook_invite' => 'Webhook للدعوات الأولية',
        'choose_event' => '-- اختر الحفل --',
        'user_added_success' => 'تم إضافة المستخدم بنجاح.',
        'user_updated_success' => 'تم تحديث المستخدم بنجاح.',
        'user_deleted_success' => 'تم حذف المستخدم بنجاح.',
        'settings_saved_success' => 'تم حفظ الإعدادات بنجاح.',
        'image_saved_success' => 'تم حفظ الإعدادات والصورة بنجاح.',
        'image_removed_success' => 'تم حذف الصورة بنجاح.',
        'username_exists' => 'اسم المستخدم موجود بالفعل.',
        'fill_all_fields' => 'الرجاء إدخال كل الحقول.',
        'error_occurred' => 'حدث خطأ.',
        'settings_error' => 'حدث خطأ أثناء حفظ الإعدادات.'
    ],
    'en' => [
        'administration' => 'Administration',
        'logout' => 'Logout',
        'all_events' => 'All Events',
        'dashboard' => 'Dashboard',
        'manage_guests' => 'Manage Guests',
        'send_invitations' => 'Send Invitations',
        'checkin' => 'Check-in',
        'view_registration' => 'View Registration Page',
        'event_settings' => 'Event Settings',
        'user_management' => 'User Management',
        'event_details' => 'Event Details',
        'qr_settings' => 'QR Card Settings',
        'webhook_settings' => 'Webhook Settings (n8n)',
        'save_all_settings' => 'Save All Settings',
        'add_new_user' => 'Add New User',
        'current_users' => 'Current Users',
        'username' => 'Username',
        'password' => 'Password',
        'role' => 'Role',
        'assigned_event' => 'Assigned Event',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'add_user' => 'Add User',
        'admin' => 'Admin',
        'viewer' => 'Viewer',
        'checkin_user' => 'Check-in User',
        'all_events_access' => '(All Events)',
        'not_specified' => 'Not Specified',
        'confirm_delete_user' => 'Are you sure you want to delete this user?',
        'event_name' => 'Event Name',
        'google_maps_link' => 'Google Maps Link',
        'event_date_ar' => 'Event Date (Arabic)',
        'event_date_en' => 'Event Date (English)',
        'venue_ar' => 'Venue (Arabic)',
        'venue_en' => 'Venue (English)',
        'event_description_ar' => 'Event Description (Arabic) - Shown when no image',
        'event_description_en' => 'Event Description (English)',
        'background_image' => 'Background Image',
        'current_image' => 'Current Image',
        'remove_current_image' => 'Remove current image when saving',
        'upload_new_image' => 'Upload New Image',
        'new_image_preview' => 'New Image Preview',
        'cancel_selection' => 'Cancel Selection',
        'qr_card_title_ar' => 'QR Card Title (Arabic)',
        'qr_card_title_en' => 'QR Card Title (English)',
        'qr_instructions_ar' => 'Show Code Instructions (Arabic)',
        'qr_instructions_en' => 'Show Code Instructions (English)',
        'qr_brand_ar' => 'Brand Text (Arabic)',
        'qr_brand_en' => 'Brand Text (English)',
        'qr_website' => 'Website on Card',
        'webhook_confirm' => 'Confirmation Webhook',
        'webhook_invite' => 'Initial Invite Webhook',
        'choose_event' => '-- Choose Event --',
        'user_added_success' => 'User added successfully.',
        'user_updated_success' => 'User updated successfully.',
        'user_deleted_success' => 'User deleted successfully.',
        'settings_saved_success' => 'Settings saved successfully.',
        'image_saved_success' => 'Settings and image saved successfully.',
        'image_removed_success' => 'Image removed successfully.',
        'username_exists' => 'Username already exists.',
        'fill_all_fields' => 'Please fill in all fields.',
        'error_occurred' => 'An error occurred.',
        'settings_error' => 'Error occurred while saving settings.'
    ]
];

$t = $texts[$lang];

// --- Security Check & Get Event ID ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- CSRF Protection ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header('Location: events.php');
    exit;
}

$message = '';
$messageType = '';

// --- Default QR and Webhook Settings ---
$default_settings = [
    'qr_card_title_ar' => 'دعوة حفل زفاف',
    'qr_card_title_en' => 'Wedding Invitation',
    'qr_show_code_instruction_ar' => 'يرجى إظهار هذا الرمز عند الدخول',
    'qr_show_code_instruction_en' => 'Please show this code at entrance',
    'qr_brand_text_ar' => 'دعواتي',
    'qr_brand_text_en' => 'Dawwaty',
    'qr_website' => 'dawwaty.com',
    'n8n_confirm_webhook' => 'https://your-n8n-instance.com/webhook/confirm',
    'n8n_initial_invite_webhook' => 'https://your-n8n-instance.com/webhook/invite'
];

// --- Handle Form Submission ---
// User Management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action']) && !isset($_POST['switch_language'])) {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Security token mismatch.'; $messageType = 'error';
    } else {
        $action = $_POST['user_action'];
        $username = trim($_POST['username'] ?? '');
        
        if ($action === 'add' || $action === 'edit') {
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            $user_event_id = !empty($_POST['user_event_id']) ? filter_input(INPUT_POST, 'user_event_id', FILTER_VALIDATE_INT) : NULL;
            if ($role === 'admin') { $user_event_id = NULL; }
        }

        switch ($action) {
            case 'add':
                if (!empty($username) && !empty($password) && !empty($role)) {
                    $stmt_check = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt_check->bind_param("s", $username);
                    $stmt_check->execute();
                    $stmt_check->store_result();
                    if ($stmt_check->num_rows > 0) {
                        $message = $t['username_exists']; $messageType = 'error';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_insert = $mysqli->prepare("INSERT INTO users (username, password_hash, role, event_id) VALUES (?, ?, ?, ?)");
                        $stmt_insert->bind_param("sssi", $username, $hashedPassword, $role, $user_event_id);
                        if ($stmt_insert->execute()) { 
                            $message = $t['user_added_success']; $messageType = 'success'; 
                        } else { 
                            $message = $t['error_occurred']; $messageType = 'error'; 
                        }
                        $stmt_insert->close();
                    }
                    $stmt_check->close();
                } else { 
                    $message = $t['fill_all_fields']; $messageType = 'error'; 
                }
                break;
                
            case 'edit':
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
                if (!empty($user_id) && !empty($username) && !empty($role)) {
                    if (!empty($password)) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $mysqli->prepare("UPDATE users SET username = ?, password_hash = ?, role = ?, event_id = ? WHERE id = ?");
                        $stmt->bind_param("sssii", $username, $hashedPassword, $role, $user_event_id, $user_id);
                    } else {
                        $stmt = $mysqli->prepare("UPDATE users SET username = ?, role = ?, event_id = ? WHERE id = ?");
                        $stmt->bind_param("ssii", $username, $role, $user_event_id, $user_id);
                    }
                    if ($stmt->execute()) { 
                        $message = $t['user_updated_success']; $messageType = 'success'; 
                    } else { 
                        $message = $t['error_occurred']; $messageType = 'error'; 
                    }
                    $stmt->close();
                }
                break;
                
            case 'delete':
                if (!empty($username)) {
                    $stmt = $mysqli->prepare("DELETE FROM users WHERE username = ? AND username != ?");
                    $stmt->bind_param("ss", $username, $_SESSION['username']);
                    if ($stmt->execute()) { 
                        $message = $t['user_deleted_success']; $messageType = 'success'; 
                    } else { 
                        $message = $t['error_occurred']; $messageType = 'error'; 
                    }
                    $stmt->close();
                }
                break;
        }
        header('Location: admin.php?event_id=' . $event_id . '&message=' . urlencode($message) . '&messageType=' . $messageType . '&tab=user-management');
        exit;
    }
}

// Event Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event_settings']) && !isset($_POST['switch_language'])) {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Security token mismatch.'; $messageType = 'error';
    } else {
        $event_name = trim($_POST['event_name'] ?? '');
        $date_ar = trim($_POST['date_ar'] ?? '');
        $date_en = trim($_POST['date_en'] ?? '');
        $venue_ar = trim($_POST['venue_ar'] ?? '');
        $venue_en = trim($_POST['venue_en'] ?? '');
        $maps_link = trim($_POST['maps_link'] ?? '');
        $event_paragraph_ar = trim($_POST['event_paragraph_ar'] ?? '');
        $event_paragraph_en = trim($_POST['event_paragraph_en'] ?? '');
        
        // QR Settings with defaults
        $qr_card_title_ar = trim($_POST['qr_card_title_ar'] ?? '') ?: $default_settings['qr_card_title_ar'];
        $qr_card_title_en = trim($_POST['qr_card_title_en'] ?? '') ?: $default_settings['qr_card_title_en'];
        $qr_instruction_ar = trim($_POST['qr_show_code_instruction_ar'] ?? '') ?: $default_settings['qr_show_code_instruction_ar'];
        $qr_instruction_en = trim($_POST['qr_show_code_instruction_en'] ?? '') ?: $default_settings['qr_show_code_instruction_en'];
        $qr_brand_ar = trim($_POST['qr_brand_text_ar'] ?? '') ?: $default_settings['qr_brand_text_ar'];
        $qr_brand_en = trim($_POST['qr_brand_text_en'] ?? '') ?: $default_settings['qr_brand_text_en'];
        $qr_website = trim($_POST['qr_website'] ?? '') ?: $default_settings['qr_website'];
        
        // Webhook Settings with defaults
        $n8n_confirm_webhook = trim($_POST['n8n_confirm_webhook'] ?? '') ?: $default_settings['n8n_confirm_webhook'];
        $n8n_initial_invite_webhook = trim($_POST['n8n_initial_invite_webhook'] ?? '') ?: $default_settings['n8n_initial_invite_webhook'];
        
        $current_bg_image = $_POST['current_background_image'] ?? '';

        // Handle image upload/removal
        if (isset($_FILES['background_image_upload']) && $_FILES['background_image_upload']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['background_image_upload']['type'];
            $file_size = $_FILES['background_image_upload']['size'];
            
            if (in_array($file_type, $allowed_types) && $file_size <= 5000000) { // 5MB limit
                $upload_dir = './uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $fileTmpPath = $_FILES['background_image_upload']['tmp_name'];
                $fileName = $_FILES['background_image_upload']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = 'event_' . $event_id . '_' . time() . '.' . $fileExtension;
                $destPath = $upload_dir . $newFileName;
                
                if(move_uploaded_file($fileTmpPath, $destPath)) {
                    // Remove old image
                    if (!empty($current_bg_image) && file_exists($current_bg_image)) { 
                        unlink($current_bg_image); 
                    }
                    $current_bg_image = $destPath;
                    $message = $t['image_saved_success']; $messageType = 'success';
                }
            }
        } elseif (isset($_POST['remove_background_image']) && $_POST['remove_background_image'] === '1') {
            if (!empty($current_bg_image) && file_exists($current_bg_image)) { 
                unlink($current_bg_image); 
            }
            $current_bg_image = '';
            $message = $t['image_removed_success']; $messageType = 'success';
        }

        $stmt = $mysqli->prepare("UPDATE events SET 
            event_name=?, event_date_ar=?, event_date_en=?, venue_ar=?, venue_en=?, Maps_link=?, 
            event_paragraph_ar=?, event_paragraph_en=?, background_image_url=?, 
            qr_card_title_ar=?, qr_card_title_en=?, qr_show_code_instruction_ar=?, 
            qr_show_code_instruction_en=?, qr_brand_text_ar=?, qr_brand_text_en=?, qr_website=?, 
            n8n_confirm_webhook=?, n8n_initial_invite_webhook=?
            WHERE id=?");
        
        $stmt->bind_param("ssssssssssssssssssi", 
            $event_name, $date_ar, $date_en, $venue_ar, $venue_en, $maps_link,
            $event_paragraph_ar, $event_paragraph_en, $current_bg_image, 
            $qr_card_title_ar, $qr_card_title_en, $qr_instruction_ar,
            $qr_instruction_en, $qr_brand_ar, $qr_brand_en, $qr_website, 
            $n8n_confirm_webhook, $n8n_initial_invite_webhook, $event_id
        );

        if ($stmt->execute()) { 
            if (empty($message)) { 
                $message = $t['settings_saved_success']; $messageType = 'success'; 
            } 
        } else { 
            $message = $t['settings_error']; $messageType = 'error'; 
        }
        $stmt->close();
        header('Location: admin.php?event_id=' . $event_id . '&message=' . urlencode($message) . '&messageType=' . $messageType . '&tab=general-settings');
        exit;
    }
}

// --- Data Fetching ---
$users = [];
$result_users = $mysqli->query("SELECT id, username, role, event_id FROM users ORDER BY username ASC");
if ($result_users) { $users = $result_users->fetch_all(MYSQLI_ASSOC); }

$all_events = [];
$result_events_list = $mysqli->query("SELECT id, event_name FROM events ORDER BY event_name ASC");
if ($result_events_list) { $all_events = $result_events_list->fetch_all(MYSQLI_ASSOC); }

$event = [];
$stmt_event = $mysqli->prepare("SELECT * FROM events WHERE id = ?");
$stmt_event->bind_param("i", $event_id);
$stmt_event->execute();
$result_event = $stmt_event->get_result();
if ($result_event->num_rows > 0) { 
    $event = $result_event->fetch_assoc(); 
    
    // Apply defaults for QR and Webhook settings if empty
    foreach ($default_settings as $key => $default_value) {
        if (empty($event[$key])) {
            $event[$key] = $default_value;
        }
    }
} else { 
    header('Location: events.php'); exit; 
}
$stmt_event->close();

if (isset($_GET['message'])) {
    $message = htmlspecialchars(urldecode($_GET['message']));
    $messageType = htmlspecialchars($_GET['messageType']);
}

function safe_html($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['administration'] ?>: <?= safe_html($event['event_name'] ?? 'حفل') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: <?= $lang === 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" ?>; 
            background-color: #f0f2f5; 
        }
        .container { max-width: 1000px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 30px; }
        .main-nav { display: flex; flex-wrap: wrap; gap: 10px; background-color: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .main-nav a { padding: 10px 15px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: background-color 0.2s; }
        .main-nav a:hover { background-color: #e9ecef; }
        .tabs { display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
        .tab-button { padding: 12px 20px; cursor: pointer; border: none; background-color: transparent; font-size: 1.1rem; font-weight: 600; color: #64748b; }
        .tab-button.active { color: #3b82f6; border-bottom: 2px solid #3b82f6; }
        .tab-content { display: none; padding-top: 20px; }
        .tab-content.active { display: block; }
        .accordion-header { cursor: pointer; padding: 15px; background-color: #f3f4f6; border-radius: 8px; font-weight: bold; margin-top: 15px; display: flex; justify-content: space-between; align-items: center; }
        .accordion-content { display: none; padding: 20px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
        .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .user-table th, .user-table td { border: 1px solid #ddd; padding: 10px; text-align: <?= $lang === 'ar' ? 'right' : 'left' ?>; }
        .user-table .actions-cell { display: flex; gap: 5px; }
        .image-preview { max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd; margin-top: 10px; }
        
        
        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal.active { display: flex; justify-content: center; align-items: center; }
        .modal-content { background-color: white; margin: 15% auto; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px; }
    </style>
</head>
<body class="p-5">
    <div class="container">
        
        <div class="flex justify-between items-center mb-4">
             <h1 class="text-3xl font-bold text-gray-800"><?= $t['administration'] ?>: "<?= safe_html($event['event_name']) ?>"</h1>
             <div class="flex gap-3 items-center">
                 <form method="POST" style="display: inline;">
                     <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                     <button type="submit" name="switch_language" value="<?= $lang === 'ar' ? 'en' : 'ar' ?>" 
                             class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg border border-gray-300 transition-colors">
                         <?= $lang === 'ar' ? 'English' : 'العربية' ?>
                     </button>
                 </form>
                 <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg"><?= $t['logout'] ?></a>
             </div>
        </div>
        
        <nav class="main-nav">
            <a href="events.php" class="text-blue-600"><?= $t['all_events'] ?></a>
            <a href="dashboard.php?event_id=<?= $event_id ?>" class="text-blue-600"><?= $t['dashboard'] ?></a>
            <a href="guests.php?event_id=<?= $event_id ?>" class="text-blue-600"><?= $t['manage_guests'] ?></a>
            <a href="send_invitations.php?event_id=<?= $event_id ?>" class="text-blue-600"><?= $t['send_invitations'] ?></a>
            <a href="checkin.php?event_id=<?= $event_id ?>" class="text-blue-600"><?= $t['checkin'] ?></a>
            <a href="register.php?event_id=<?= $event_id ?>" target="_blank" class="text-green-600 font-bold"><?= $t['view_registration'] ?></a>
        </nav>
        
        <?php if ($message): ?>
            <div class="p-4 mb-4 text-sm rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-button active" data-tab="general-settings"><?= $t['event_settings'] ?></button>
            <button class="tab-button" data-tab="user-management"><?= $t['user_management'] ?></button>
        </div>
        
        <div id="general-settings" class="tab-content active">
            <form id="event-settings-form" method="POST" action="admin.php?event_id=<?= $event_id ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="update_event_settings" value="1">
                
                <div class="accordion-header" onclick="toggleAccordion(this)"><?= $t['event_details'] ?> <span class="toggle-icon">▼</span></div>
                <div class="accordion-content" style="display: block;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label><?= $t['event_name'] ?>:</label>
                            <input type="text" name="event_name" value="<?= safe_html($event['event_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= $t['google_maps_link'] ?>:</label>
                            <input type="url" name="maps_link" value="<?= safe_html($event['Maps_link']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['event_date_ar'] ?>:</label>
                            <input type="text" name="date_ar" value="<?= safe_html($event['event_date_ar']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['event_date_en'] ?>:</label>
                            <input type="text" name="date_en" value="<?= safe_html($event['event_date_en']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['venue_ar'] ?>:</label>
                            <input type="text" name="venue_ar" value="<?= safe_html($event['venue_ar']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['venue_en'] ?>:</label>
                            <input type="text" name="venue_en" value="<?= safe_html($event['venue_en']) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= $t['event_description_ar'] ?>:</label>
                        <textarea name="event_paragraph_ar" rows="4"><?= safe_html($event['event_paragraph_ar']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= $t['event_description_en'] ?>:</label>
                        <textarea name="event_paragraph_en" rows="4"><?= safe_html($event['event_paragraph_en']) ?></textarea>
                    </div>
                    
                    <!-- Enhanced Image Management -->
                    <div class="form-group mt-6">
                        <label class="font-bold"><?= $t['background_image'] ?></label>
                        
                        <?php if(!empty($event['background_image_url'])): ?>
                            <div class="my-4 p-4 border rounded-lg bg-gray-50">
                                <p class="font-semibold mb-2"><?= $t['current_image'] ?>:</p>
                                <img src="<?= safe_html($event['background_image_url']) ?>" alt="<?= $t['current_image'] ?>" class="image-preview">
                                <div class="mt-3">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="remove_background_image" value="1" class="mx-2" onchange="toggleImageUpload(this)">
                                        <?= $t['remove_current_image'] ?>
                                    </label>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Show description when no image -->
                            <?php if(!empty($event['event_paragraph_ar']) || !empty($event['event_paragraph_en'])): ?>
                                <div class="my-4 p-4 border rounded-lg bg-blue-50">
                                    <p class="text-sm text-blue-600 mb-2">معاينة النص عند عدم وجود صورة:</p>
                                    <div class="bg-white p-4 rounded border">
                                        <?php if($lang === 'ar' && !empty($event['event_paragraph_ar'])): ?>
                                            <p><?= nl2br(safe_html($event['event_paragraph_ar'])) ?></p>
                                        <?php elseif($lang === 'en' && !empty($event['event_paragraph_en'])): ?>
                                            <p><?= nl2br(safe_html($event['event_paragraph_en'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div id="new-image-upload-section" class="mt-2">
                             <label class="block font-medium"><?= $t['upload_new_image'] ?>:</label>
                             <input type="file" id="background_image_upload" name="background_image_upload" accept="image/*" class="mt-1" onchange="previewNewImage(this)">
                             <p class="text-sm text-gray-600 mt-1">حد أقصى: 5MB، الأنواع المدعومة: JPG, PNG, GIF, WebP</p>
                        </div>

                        <div id="new-image-preview-container" class="my-2" style="display: none;">
                             <p class="font-semibold"><?= $t['new_image_preview'] ?>:</p>
                             <img id="new-image-preview" src="#" alt="<?= $t['new_image_preview'] ?>" class="image-preview">
                             <button type="button" id="cancel-image-selection" class="mt-2 text-sm text-red-600 hover:underline" onclick="cancelImageSelection()"><?= $t['cancel_selection'] ?></button>
                        </div>
                        <input type="hidden" name="current_background_image" value="<?= safe_html($event['background_image_url']) ?>">
                    </div>
                </div>

                <div class="accordion-header" onclick="toggleAccordion(this)"><?= $t['qr_settings'] ?> <span class="toggle-icon">▼</span></div>
                <div class="accordion-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label><?= $t['qr_card_title_ar'] ?>:</label>
                            <input type="text" name="qr_card_title_ar" value="<?= safe_html($event['qr_card_title_ar']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['qr_card_title_en'] ?>:</label>
                            <input type="text" name="qr_card_title_en" value="<?= safe_html($event['qr_card_title_en']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['qr_instructions_ar'] ?>:</label>
                            <input type="text" name="qr_show_code_instruction_ar" value="<?= safe_html($event['qr_show_code_instruction_ar']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['qr_instructions_en'] ?>:</label>
                            <input type="text" name="qr_show_code_instruction_en" value="<?= safe_html($event['qr_show_code_instruction_en']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['qr_brand_ar'] ?>:</label>
                            <input type="text" name="qr_brand_text_ar" value="<?= safe_html($event['qr_brand_text_ar']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['qr_brand_en'] ?>:</label>
                            <input type="text" name="qr_brand_text_en" value="<?= safe_html($event['qr_brand_text_en']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['qr_website'] ?>:</label>
                            <input type="text" name="qr_website" value="<?= safe_html($event['qr_website']) ?>">
                        </div>
                    </div>
                </div>

                <div class="accordion-header" onclick="toggleAccordion(this)"><?= $t['webhook_settings'] ?> <span class="toggle-icon">▼</span></div>
                <div class="accordion-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label><?= $t['webhook_confirm'] ?>:</label>
                            <input type="url" name="n8n_confirm_webhook" value="<?= safe_html($event['n8n_confirm_webhook']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= $t['webhook_invite'] ?>:</label>
                            <input type="url" name="n8n_initial_invite_webhook" value="<?= safe_html($event['n8n_initial_invite_webhook']) ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg"><?= $t['save_all_settings'] ?></button>
            </form>
        </div>

        <div id="user-management" class="tab-content">
            <h3 class="text-xl font-semibold mt-4 mb-4"><?= $t['add_new_user'] ?></h3>
            <form method="POST" action="admin.php?event_id=<?= $event_id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="user_action" value="add">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="form-group">
                        <label><?= $t['username'] ?>:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label><?= $t['password'] ?>:</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label><?= $t['role'] ?>:</label>
                        <select name="role" required onchange="toggleEventSelect(this.value, 'add_event_select_container')">
                            <option value="admin"><?= $t['admin'] ?></option>
                            <option value="viewer"><?= $t['viewer'] ?></option>
                            <option value="checkin_user"><?= $t['checkin_user'] ?></option>
                        </select>
                    </div>
                    <div class="form-group" id="add_event_select_container" style="display:none;">
                        <label><?= $t['assigned_event'] ?>:</label>
                        <select name="user_event_id">
                            <option value=""><?= $t['choose_event'] ?></option>
                            <?php foreach($all_events as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['event_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg"><?= $t['add_user'] ?></button>
            </form>
            
            <hr class="my-8">
            
            <h3 class="text-xl font-semibold mt-8 mb-4"><?= $t['current_users'] ?></h3>
            <div class="overflow-x-auto">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th><?= $t['username'] ?></th>
                            <th><?= $t['role'] ?></th>
                            <th><?= $t['assigned_event'] ?></th>
                            <th><?= $t['actions'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <em><?= $t['all_events_access'] ?></em>
                                <?php else: ?>
                                    <?php 
                                    $event_name = $t['not_specified'];
                                    foreach ($all_events as $e) {
                                        if ($e['id'] == $user['event_id']) {
                                            $event_name = $e['event_name'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($event_name);
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded" onclick='openEditModal(<?= json_encode($user) ?>)'><?= $t['edit'] ?></button>
                                <?php if ($_SESSION['username'] !== $user['username']): ?>
                                <form method="POST" action="admin.php?event_id=<?= $event_id ?>" onsubmit="return confirm('<?= $t['confirm_delete_user'] ?>');" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="user_action" value="delete">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"><?= $t['delete'] ?></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold mb-4"><?= $t['edit'] ?> <?= $t['username'] ?></h2>
            <form method="POST" action="admin.php?event_id=<?= $event_id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="user_action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label><?= $t['username'] ?>:</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label><?= $t['password'] ?> (اتركه فارغاً لعدم التغيير):</label>
                    <input type="password" name="password" id="edit_password">
                </div>
                <div class="form-group">
                    <label><?= $t['role'] ?>:</label>
                    <select name="role" id="edit_role" required onchange="toggleEventSelect(this.value, 'edit_event_select_container')">
                        <option value="admin"><?= $t['admin'] ?></option>
                        <option value="viewer"><?= $t['viewer'] ?></option>
                        <option value="checkin_user"><?= $t['checkin_user'] ?></option>
                    </select>
                </div>
                <div class="form-group" id="edit_event_select_container" style="display:none;">
                    <label><?= $t['assigned_event'] ?>:</label>
                    <select name="user_event_id" id="edit_user_event_id">
                        <option value=""><?= $t['choose_event'] ?></option>
                        <?php foreach($all_events as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['event_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">إلغاء</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"><?= $t['save_all_settings'] ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Tab Logic
            const tabs = document.querySelectorAll('.tab-button');
            const contents = document.querySelectorAll('.tab-content');
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'general-settings';

            function switchTab(tabId) {
                contents.forEach(content => content.classList.remove('active'));
                tabs.forEach(tab => tab.classList.remove('active'));
                const contentToShow = document.getElementById(tabId);
                const tabToActivate = document.querySelector(`[data-tab='${tabId}']`);
                if(contentToShow) contentToShow.classList.add('active');
                if(tabToActivate) tabToActivate.classList.add('active');
            }
            
            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tabId = tab.dataset.tab;
                    switchTab(tabId);
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tabId);
                    window.history.pushState({}, '', url);
                });
            });
            switchTab(activeTab);
        });

        // Accordion Logic
        function toggleAccordion(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.toggle-icon');
            const isOpen = content.style.display === 'block';
            
            content.style.display = isOpen ? 'none' : 'block';
            icon.textContent = isOpen ? '▼' : '▲';
        }

        // Image Management Functions
        function previewNewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('new-image-preview').src = e.target.result;
                    document.getElementById('new-image-preview-container').style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function cancelImageSelection() {
            document.getElementById('background_image_upload').value = '';
            document.getElementById('new-image-preview-container').style.display = 'none';
        }

        function toggleImageUpload(checkbox) {
            const uploadSection = document.getElementById('new-image-upload-section');
            if (checkbox.checked) {
                uploadSection.style.display = 'none';
                cancelImageSelection();
            } else {
                uploadSection.style.display = 'block';
            }
        }

        // User Management Functions
        function toggleEventSelect(role, containerId) { 
            const container = document.getElementById(containerId);
            if(container) {
                container.style.display = (role === 'viewer' || role === 'checkin_user') ? 'block' : 'none';
            }
        }

        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_user_event_id').value = user.event_id || '';
            
            toggleEventSelect(user.role, 'edit_event_select_container');
            document.getElementById('editUserModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('editUserModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>