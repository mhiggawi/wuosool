<?php
// rsvp.php - Enhanced version with security, multilingual support, and modern UX
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

session_start();
require_once 'db_config.php';

// --- Language System ---
$lang = $_SESSION['language'] ?? $_COOKIE['language'] ?? 'ar';
if (isset($_POST['switch_language'])) {
    $lang = $_POST['switch_language'] === 'en' ? 'en' : 'ar';
    $_SESSION['language'] = $lang;
    setcookie('language', $lang, time() + (365 * 24 * 60 * 60), '/');
    
    // Redirect to prevent form resubmission
    $currentUrl = $_SERVER['REQUEST_URI'];
    header("Location: $currentUrl");
    exit;
}

// Language texts
$texts = [
    'ar' => [
        'wedding_blessing' => 'بارك الله لهما وبارك عليهما وجمع بينهما بخير',
        'wedding_occasion' => 'وذلك بمناسبة حفل زفافهما المبارك',
        'location_in' => 'في',
        'guest_name_label' => 'السيد/ة',
        'guest_count' => 'عدد الضيوف',
        'table_number' => 'رقم الطاولة',
        'not_specified' => 'غير محدد',
        'confirm_attendance' => 'تأكيد الحضور',
        'decline_attendance' => 'إلغاء الحضور',
        'already_confirmed' => 'تم تأكيد حضورك مسبقاً',
        'already_declined' => 'تم تسجيل اعتذارك مسبقاً',
        'success_confirmed' => 'تم تأكيد حضورك بنجاح! ستصلك رسالة واتساب قريباً.',
        'success_declined' => 'تم تسجيل اعتذارك. شكراً لك.',
        'error_occurred' => 'حدث خطأ، يرجى المحاولة مرة أخرى.',
        'connection_error' => 'خطأ في الاتصال، يرجى التحقق من الإنترنت.',
        'invalid_link' => 'رابط الدعوة غير صحيح أو منتهي الصلاحية.',
        'processing' => 'جاري المعالجة...',
        'show_qr_instruction' => 'يرجى إظهار هذا الرمز عند الدخول',
        'download_qr' => 'تحميل رمز QR',
        'add_to_calendar' => 'إضافة للتقويم',
        'share_invitation' => 'مشاركة الدعوة',
        'view_location' => 'عرض الموقع',
        'guest_details' => 'تفاصيل الضيف',
        'qr_code' => 'رمز QR',
        'save_date' => 'احفظ التاريخ',
        'get_directions' => 'الحصول على الاتجاهات',
        'rate_limit_error' => 'الرجاء الانتظار قبل المحاولة مرة أخرى.',
        'csrf_error' => 'خطأ في التحقق من الأمان. يرجى إعادة تحميل الصفحة.',
        'welcome_guest' => 'أهلاً وسهلاً',
        'dear_guest' => 'الضيف الكريم',
        'entry_card' => 'بطاقة الدخول',
        'show_at_entrance' => 'يرجى إبراز هذا الكود عند الدخول',
        'powered_by' => 'مدعوم من'
    ],
    'en' => [
        'wedding_blessing' => 'May Allah bless them and unite them in goodness',
        'wedding_occasion' => 'On the occasion of their blessed wedding',
        'location_in' => 'at',
        'guest_name_label' => 'Mr./Mrs.',
        'guest_count' => 'Number of Guests',
        'table_number' => 'Table Number',
        'not_specified' => 'Not Specified',
        'confirm_attendance' => 'Confirm Attendance',
        'decline_attendance' => 'Decline Attendance',
        'already_confirmed' => 'Your attendance has already been confirmed',
        'already_declined' => 'Your decline has already been recorded',
        'success_confirmed' => 'Your attendance has been confirmed successfully! You will receive a WhatsApp message soon.',
        'success_declined' => 'Your decline has been recorded. Thank you.',
        'error_occurred' => 'An error occurred, please try again.',
        'connection_error' => 'Connection error, please check your internet.',
        'invalid_link' => 'Invalid or expired invitation link.',
        'processing' => 'Processing...',
        'show_qr_instruction' => 'Please show this code at the entrance',
        'download_qr' => 'Download QR Code',
        'add_to_calendar' => 'Add to Calendar',
        'share_invitation' => 'Share Invitation',
        'view_location' => 'View Location',
        'guest_details' => 'Guest Details',
        'qr_code' => 'QR Code',
        'save_date' => 'Save the Date',
        'get_directions' => 'Get Directions',
        'rate_limit_error' => 'Please wait before trying again.',
        'csrf_error' => 'Security verification error. Please reload the page.',
        'welcome_guest' => 'Welcome',
        'dear_guest' => 'Dear Guest',
        'entry_card' => 'Entry Card',
        'show_at_entrance' => 'Please show this code at the entrance',
        'powered_by' => 'Powered by'
    ]
];

$t = $texts[$lang];

// --- CSRF Protection ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Rate Limiting ---
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_key = 'rsvp_rate_limit_' . md5($client_ip);
$current_time = time();

if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'first_attempt' => $current_time];
}

// Reset rate limit after 5 minutes
if ($current_time - $_SESSION[$rate_limit_key]['first_attempt'] > 300) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'first_attempt' => $current_time];
}

// --- Data Initialization ---
$guest_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$event_data = null;
$guest_data = null;
$error_message = '';
$is_rate_limited = $_SESSION[$rate_limit_key]['count'] >= 10;

if (empty($guest_id)) {
    $error_message = $t['invalid_link'];
} else {
    // Fetch guest data with prepared statements
    $sql_guest = "SELECT g.*, e.* FROM guests g 
                  JOIN events e ON g.event_id = e.id 
                  WHERE g.guest_id = ? LIMIT 1";
    
    if ($stmt_guest = $mysqli->prepare($sql_guest)) {
        $stmt_guest->bind_param("s", $guest_id);
        $stmt_guest->execute();
        $result_guest = $stmt_guest->get_result();
        
        if ($result_guest->num_rows === 1) {
            $combined_data = $result_guest->fetch_assoc();
            
            // Separate guest and event data
            $guest_data = [
                'id' => $combined_data['id'],
                'guest_id' => $combined_data['guest_id'],
                'name_ar' => $combined_data['name_ar'],
                'phone_number' => $combined_data['phone_number'],
                'guests_count' => $combined_data['guests_count'],
                'table_number' => $combined_data['table_number'],
                'status' => $combined_data['status'],
                'checkin_status' => $combined_data['checkin_status']
            ];
            
            $event_data = [
                'id' => $combined_data['event_id'],
                'event_name' => $combined_data['event_name'],
                'bride_name_ar' => $combined_data['bride_name_ar'],
                'groom_name_ar' => $combined_data['groom_name_ar'],
                'event_date_ar' => $combined_data['event_date_ar'],
                'venue_ar' => $combined_data['venue_ar'],
                'Maps_link' => $combined_data['Maps_link'],
                'event_paragraph_ar' => $combined_data['event_paragraph_ar'],
                'background_image_url' => $combined_data['background_image_url'],
                'qr_card_title_ar' => $combined_data['qr_card_title_ar'],
                'qr_show_code_instruction_ar' => $combined_data['qr_show_code_instruction_ar'],
                'qr_brand_text_ar' => $combined_data['qr_brand_text_ar'],
                'qr_website' => $combined_data['qr_website'],
                'n8n_confirm_webhook' => $combined_data['n8n_confirm_webhook']
            ];
        } else {
            $error_message = $t['invalid_link'];
        }
        $stmt_guest->close();
    }
}

// --- Handle AJAX RSVP Response ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_rsvp']) && !isset($_POST['switch_language'])) {
    header('Content-Type: application/json');
    
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => $t['csrf_error']]);
        exit;
    }
    
    // Rate Limiting Check
    if ($is_rate_limited) {
        echo json_encode(['success' => false, 'message' => $t['rate_limit_error']]);
        exit;
    }
    
    $_SESSION[$rate_limit_key]['count']++;
    
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    $guest_id_post = filter_input(INPUT_POST, 'guest_id', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (!in_array($status, ['confirmed', 'canceled']) || empty($guest_id_post)) {
        echo json_encode(['success' => false, 'message' => $t['error_occurred']]);
        exit;
    }
    
    // Update guest status
    $sql_update = "UPDATE guests SET status = ?, checkin_time = CASE WHEN ? = 'confirmed' THEN NOW() ELSE checkin_time END WHERE guest_id = ?";
    
    if ($stmt_update = $mysqli->prepare($sql_update)) {
        $stmt_update->bind_param("sss", $status, $status, $guest_id_post);
        
        if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
            // Call webhook if confirmed and webhook exists
            if ($status === 'confirmed' && !empty($event_data['n8n_confirm_webhook'])) {
                $webhook_url = filter_var($event_data['n8n_confirm_webhook'], FILTER_VALIDATE_URL);
                if ($webhook_url) {
                    $webhook_payload = json_encode([
                        'guest_id' => $guest_id_post,
                        'phone_number' => $guest_data['phone_number'] ?? '',
                        'timestamp' => time()
                    ]);
                    
                    $ch = curl_init($webhook_url);
                    curl_setopt_array($ch, [
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => $webhook_payload,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_HTTPHEADER => [
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($webhook_payload)
                        ]
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }
            
            $message = $status === 'confirmed' ? $t['success_confirmed'] : $t['success_declined'];
            echo json_encode(['success' => true, 'message' => $message, 'status' => $status]);
        } else {
            echo json_encode(['success' => false, 'message' => $t['error_occurred']]);
        }
        $stmt_update->close();
    } else {
        echo json_encode(['success' => false, 'message' => $t['error_occurred']]);
    }
    
    $mysqli->close();
    exit;
}

// --- Helper Functions ---
function safe_html($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

function generate_calendar_link($event_data, $lang) {
    $title = urlencode($event_data['event_name'] ?? 'Event');
    $location = urlencode($event_data['venue_ar'] ?? '');
    $details = urlencode($event_data['event_paragraph_ar'] ?? '');
    
    // Simplified date - you might want to parse this properly based on your date format
    $date = date('Ymd\THis\Z', strtotime('+1 week')); // Default to next week
    
    return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$date}/{$date}&details={$details}&location={$location}";
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_data ? safe_html($event_data['event_name']) : 'دعوة' ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= safe_html($event_data['event_paragraph_ar'] ?? 'دعوة خاصة') ?>">
    <meta name="keywords" content="دعوة,حفل,زفاف,invitation,wedding">
    <meta name="author" content="Dawwaty">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= safe_html($event_data['event_name'] ?? 'دعوة') ?>">
    <meta property="og:description" content="<?= safe_html($event_data['event_paragraph_ar'] ?? 'دعوة خاصة') ?>">
    <meta property="og:image" content="<?= safe_html($event_data['background_image_url'] ?? '') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    
    <!-- Fonts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: <?= $lang === 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" ?>; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px;
        }
        
        .card-container { 
            max-width: 500px; 
            width: 100%; 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .language-toggle {
            position: absolute;
            top: 15px;
            <?= $lang === 'ar' ? 'left: 15px' : 'right: 15px' ?>;
            z-index: 10;
        }
        
        .language-toggle button {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .language-toggle button:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-1px);
        }
        
        .description-box {
            padding: 40px 25px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            text-align: center;
            color: #374151;
            font-size: 1.1rem;
            line-height: 1.8;
            position: relative;
        }
        
        .card-content { 
            padding: 30px; 
            background: white;
        }
        
        .guest-welcome {
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border-radius: 15px;
            border: 1px solid #60a5fa;
        }
        
        .guest-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .detail-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .detail-value {
            font-weight: bold;
            color: #374151;
        }
        
        .location-card {
            padding: 20px;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-radius: 12px;
            border: 1px solid #22c55e;
            margin: 20px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .action-buttons button {
            flex: 1;
            padding: 15px;
            border-radius: 12px;
            font-weight: bold;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .btn-decline {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .action-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .action-buttons button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .qr-code-section {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            padding: 30px;
            display: none;
            text-align: center;
            border-top: 1px solid #f59e0b;
        }
        
        .qr-code-section.active {
            display: block;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .qr-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            grid-template-rows: auto auto auto;
            gap: 15px;
            align-items: center;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .qr-title-box {
            grid-column: 1 / 4;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        
        .qr-code-container {
            grid-column: 2 / 3;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .qr-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .share-button {
            padding: 10px 15px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .share-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-calendar { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
        .btn-share { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .btn-download { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .btn-location { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(16, 185, 129, 0.95);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            background: rgba(239, 68, 68, 0.95);
        }
        
        .error-container { 
            text-align: center; 
            padding: 60px 40px;
            background: white;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #ef4444;
            margin-bottom: 20px;
        }
        
        /* صور الحدث - خيارات متعددة */
        .event-image-container {
            position: relative;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .event-image {
            width: 100%;
            height: auto;
            display: block;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        /* الخيار 1: صورة بارتفاع ثابت مع object-fit */
        .event-image.fixed-height {
            height: 250px;
            object-fit: cover;
        }
        
        /* الخيار 2: صورة بنسبة عرض للارتفاع محددة */
        .event-image.aspect-ratio {
            aspect-ratio: 16/9;
            object-fit: cover;
        }
        
        /* الخيار 3: صورة كاملة بحد أقصى للارتفاع */
        .event-image.max-height {
            max-height: 400px;
            object-fit: contain;
            background: white;
        }
        
        /* الخيار 4: صورة متجاوبة بالكامل */
        .event-image.responsive {
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }
        
        /* hover effect */
        .event-image:hover {
            transform: scale(1.02);
        }
        
        /* Full screen image modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            cursor: pointer;
        }
        
        .image-modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .image-modal .close-button {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            z-index: 10000;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 640px) {
            .card-container {
                margin: 10px;
                max-width: calc(100vw - 20px);
            }
            
            .guest-details {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .share-buttons {
                flex-direction: column;
            }
            
            .qr-grid {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto auto;
            }
            
            .qr-code-container {
                grid-column: 1 / 2;
            }
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Language Toggle -->
        <div class="language-toggle">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" name="switch_language" value="<?= $lang === 'ar' ? 'en' : 'ar' ?>">
                    <i class="fas fa-globe"></i>
                    <?= $lang === 'ar' ? 'EN' : 'عربي' ?>
                </button>
            </form>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-container">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= $t['invalid_link'] ?></h2>
                <p class="text-lg text-gray-600"><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php else: ?>
            
            <!-- Event Image or Description -->
            <?php if (!empty($event_data['background_image_url'])): ?>
                <div class="event-image-container">
                    <img src="<?= safe_html($event_data['background_image_url']) ?>" 
                         alt="<?= safe_html($event_data['event_name']) ?>" 
                         class="event-image"
                         loading="lazy"
                         onclick="toggleImageView(this)">
                </div>
            <?php else: ?>
                <div class="description-box">
                    <p><?= nl2br(safe_html($event_data['event_paragraph_ar'] ?? 'مرحباً بكم في مناسبتنا الخاصة.')) ?></p>
                </div>
            <?php endif; ?>

            <div class="card-content" id="main-content">
                <!-- Guest Welcome Section -->
                <div class="guest-welcome">
                    <h2 class="text-xl font-bold text-blue-800 mb-2">
                        <i class="fas fa-heart text-red-500"></i>
                        <?= $t['welcome_guest'] ?>
                    </h2>
                    <p class="text-lg font-semibold text-blue-700">
                        <?= safe_html($guest_data['name_ar'] ?? $t['dear_guest']) ?>
                    </p>
                </div>

                <!-- Guest Details -->
                <div class="guest-details">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-users"></i>
                            <?= $t['guest_count'] ?>
                        </div>
                        <div class="detail-value"><?= safe_html($guest_data['guests_count'] ?? '1') ?></div>
                    </div>
                    
                    <?php if (!empty($guest_data['table_number'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-chair"></i>
                            <?= $t['table_number'] ?>
                        </div>
                        <div class="detail-value"><?= safe_html($guest_data['table_number']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Location Card -->
                <?php if (!empty($event_data['venue_ar']) || !empty($event_data['Maps_link'])): ?>
                <div class="location-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-green-800 mb-1">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= safe_html($event_data['venue_ar'] ?? $t['view_location']) ?>
                            </h3>
                            <?php if (!empty($event_data['event_date_ar'])): ?>
                            <p class="text-sm text-green-700">
                                <i class="fas fa-calendar"></i>
                                <?= safe_html($event_data['event_date_ar']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($event_data['Maps_link'])): ?>
                        <a href="<?= safe_html($event_data['Maps_link']) ?>" 
                           target="_blank" 
                           class="text-green-600 hover:text-green-800 transition-colors">
                            <i class="fas fa-external-link-alt text-xl"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div id="action-buttons-section" class="action-buttons">
                    <button id="confirm-button" class="btn-confirm" onclick="handleRSVP('confirmed')">
                        <div class="spinner" id="confirm-spinner"></div>
                        <span id="confirm-text">
                            <i class="fas fa-check"></i>
                            <?= $t['confirm_attendance'] ?>
                        </span>
                    </button>
                    <button id="cancel-button" class="btn-decline" onclick="handleRSVP('canceled')">
                        <div class="spinner" id="cancel-spinner"></div>
                        <span id="cancel-text">
                            <i class="fas fa-times"></i>
                            <?= $t['decline_attendance'] ?>
                        </span>
                    </button>
                </div>
                
                <!-- Response Message -->
                <div id="response-message" class="hidden mt-6 p-4 rounded-lg text-center font-semibold"></div>
                
                <!-- Share Buttons -->
                <div class="share-buttons">
                    <button onclick="addToCalendar()" class="share-button btn-calendar">
                        <i class="fas fa-calendar-plus"></i>
                        <?= $t['add_to_calendar'] ?>
                    </button>
                    
                    <button onclick="shareInvitation()" class="share-button btn-share">
                        <i class="fas fa-share-alt"></i>
                        <?= $t['share_invitation'] ?>
                    </button>
                    
                    <?php if (!empty($event_data['Maps_link'])): ?>
                    <button onclick="openLocation()" class="share-button btn-location">
                        <i class="fas fa-map-marked-alt"></i>
                        <?= $t['get_directions'] ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- QR Code Section -->
            <div id="qr-code-section" class="qr-code-section">
                <div class="qr-grid">
                    <div class="qr-title-box">
                        <h3 class="text-xl font-bold text-amber-800 mb-2">
                            <i class="fas fa-qrcode"></i>
                            <?= safe_html($event_data['qr_card_title_ar'] ?? $t['entry_card']) ?>
                        </h3>
                        <p class="text-sm text-amber-700"><?= $t['qr_code'] ?></p>
                    </div>
                    
                    <div class="qr-info qr-info-left">
                        <div class="text-center">
                            <div class="text-xs text-gray-600 mb-1"><?= $t['guest_count'] ?></div>
                            <div class="text-2xl font-bold text-gray-800"><?= safe_html($guest_data['guests_count'] ?? '1') ?></div>
                        </div>
                        <div class="text-xs text-gray-600 mt-4">
                            <?= safe_html($event_data['qr_brand_text_ar'] ?? 'دعواتي') ?>
                        </div>
                    </div>
                    
                    <div id="qrcode" class="qr-code-container"></div>
                    
                    <div class="qr-info qr-info-right text-center">
                        <p class="text-sm font-semibold text-gray-700 mb-2">
                            <?= safe_html($event_data['qr_show_code_instruction_ar'] ?? $t['show_at_entrance']) ?>
                        </p>
                        <div class="text-xs text-gray-600">
                            <?= safe_html($event_data['qr_website'] ?? 'dawwaty.com') ?>
                        </div>
                    </div>
                </div>
                
                <!-- QR Action Buttons -->
                <div class="share-buttons mt-6">
                    <button onclick="downloadQR()" class="share-button btn-download">
                        <i class="fas fa-download"></i>
                        <?= $t['download_qr'] ?>
                    </button>
                    
                    <button onclick="shareQR()" class="share-button btn-share">
                        <i class="fas fa-share"></i>
                        <?= $t['share_invitation'] ?>
                    </button>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- Image Modal for full screen view -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="close-button" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" src="" alt="Full size image">
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div id="toast-message"></div>
    </div>

    <?php if (empty($error_message)): ?>
    <script>
        // Configuration and Data
        const CONFIG = {
            guestData: <?= json_encode($guest_data, JSON_UNESCAPED_UNICODE) ?>,
            eventData: <?= json_encode($event_data, JSON_UNESCAPED_UNICODE) ?>,
            texts: <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>,
            lang: '<?= $lang ?>',
            csrfToken: '<?= htmlspecialchars($_SESSION['csrf_token']) ?>',
            calendarLink: '<?= generate_calendar_link($event_data, $lang) ?>'
        };

        // Global state
        let qrCodeGenerated = false;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkInitialStatus();
            preloadQRLibrary();
        });

        // Check initial guest status
        function checkInitialStatus() {
            const status = CONFIG.guestData.status;
            
            if (status === 'confirmed') {
                showSuccessState('confirmed');
            } else if (status === 'canceled') {
                showSuccessState('canceled');
            }
        }

        // Handle RSVP response
        async function handleRSVP(status) {
            const confirmBtn = document.getElementById('confirm-button');
            const cancelBtn = document.getElementById('cancel-button');
            const spinner = document.getElementById(status === 'confirmed' ? 'confirm-spinner' : 'cancel-spinner');
            const text = document.getElementById(status === 'confirmed' ? 'confirm-text' : 'cancel-text');
            
            // Disable buttons and show loading
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            spinner.style.display = 'inline-block';
            
            try {
                const formData = new FormData();
                formData.append('ajax_rsvp', '1');
                formData.append('status', status);
                formData.append('guest_id', CONFIG.guestData.guest_id);
                formData.append('csrf_token', CONFIG.csrfToken);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showSuccessState(status);
                    showToast(result.message, 'success');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('RSVP Error:', error);
                showToast(error.message || CONFIG.texts.connection_error, 'error');
                
                // Re-enable buttons
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
            } finally {
                spinner.style.display = 'none';
            }
        }

        // Show success state
        function showSuccessState(status) {
            const actionButtons = document.getElementById('action-buttons-section');
            const responseMessage = document.getElementById('response-message');
            const qrSection = document.getElementById('qr-code-section');
            
            actionButtons.style.display = 'none';
            
            if (status === 'confirmed') {
                responseMessage.className = 'mt-6 p-4 rounded-lg text-center font-semibold bg-green-100 text-green-800';
                responseMessage.innerHTML = `
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    ${CONFIG.texts.already_confirmed}
                `;
                responseMessage.style.display = 'block';
                
                // Show QR code section
                qrSection.classList.add('active');
                generateQRCode();
            } else {
                responseMessage.className = 'mt-6 p-4 rounded-lg text-center font-semibold bg-red-100 text-red-800';
                responseMessage.innerHTML = `
                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                    ${CONFIG.texts.already_declined}
                `;
                responseMessage.style.display = 'block';
            }
        }

        // Generate QR Code
        function generateQRCode() {
            if (qrCodeGenerated) return;
            
            const qrcodeContainer = document.getElementById('qrcode');
            qrcodeContainer.innerHTML = '';
            
            try {
                new QRCode(qrcodeContainer, {
                    text: CONFIG.guestData.guest_id,
                    width: 150,
                    height: 150,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
                qrCodeGenerated = true;
            } catch (error) {
                console.error('QR Generation Error:', error);
                qrcodeContainer.innerHTML = '<div class="text-red-500">QR Code generation failed</div>';
            }
        }

        // Preload QR library for better performance
        function preloadQRLibrary() {
            if (CONFIG.guestData.status === 'confirmed') {
                generateQRCode();
            }
        }

        // Download QR Code
        function downloadQR() {
            try {
                const qrCanvas = document.querySelector('#qrcode canvas');
                if (!qrCanvas) {
                    showToast('QR Code not generated yet', 'error');
                    return;
                }

                const link = document.createElement('a');
                link.download = `invitation-qr-${CONFIG.guestData.guest_id}.png`;
                link.href = qrCanvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showToast('QR Code downloaded successfully!', 'success');
            } catch (error) {
                console.error('Download Error:', error);
                showToast('Download failed', 'error');
            }
        }

        // Share QR Code
        async function shareQR() {
            try {
                const qrCanvas = document.querySelector('#qrcode canvas');
                if (!qrCanvas) {
                    showToast('QR Code not generated yet', 'error');
                    return;
                }

                if (navigator.share && navigator.canShare) {
                    qrCanvas.toBlob(async (blob) => {
                        const file = new File([blob], 'invitation-qr.png', { type: 'image/png' });
                        
                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: CONFIG.eventData.event_name,
                                text: `${CONFIG.texts.share_invitation} - ${CONFIG.eventData.event_name}`,
                                files: [file]
                            });
                        } else {
                            fallbackShare();
                        }
                    });
                } else {
                    fallbackShare();
                }
            } catch (error) {
                console.error('Share Error:', error);
                fallbackShare();
            }
        }

        // Share invitation
        async function shareInvitation() {
            const shareData = {
                title: CONFIG.eventData.event_name,
                text: `${CONFIG.texts.share_invitation} - ${CONFIG.eventData.event_name}`,
                url: window.location.href
            };

            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                } else {
                    await navigator.clipboard.writeText(window.location.href);
                    showToast('Link copied to clipboard!', 'success');
                }
            } catch (error) {
                console.error('Share Error:', error);
                fallbackShare();
            }
        }

        // Fallback share method
        function fallbackShare() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard!', 'success');
            }).catch(() => {
                // Final fallback - show URL in prompt
                prompt('Copy this link:', url);
            });
        }

        // Add to calendar
        function addToCalendar() {
            const calendarUrl = CONFIG.calendarLink;
            window.open(calendarUrl, '_blank');
            showToast('Opening calendar...', 'success');
        }

        // Open location
        function openLocation() {
            if (CONFIG.eventData.Maps_link) {
                window.open(CONFIG.eventData.Maps_link, '_blank');
                showToast('Opening maps...', 'success');
            }
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            toastMessage.textContent = message;
            toast.className = `toast ${type === 'error' ? 'error' : ''}`;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Prevent multiple submissions
        let isSubmitting = false;
        window.addEventListener('beforeunload', function() {
            if (isSubmitting) {
                return 'Please wait while processing...';
            }
        });

        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('Global Error:', e.error);
            showToast(CONFIG.texts.error_occurred, 'error');
        });

        // Performance optimization - lazy load non-critical features
        if ('IntersectionObserver' in window) {
            const qrSection = document.getElementById('qr-code-section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !qrCodeGenerated) {
                        generateQRCode();
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            if (qrSection) {
                observer.observe(qrSection);
            }
        }

        // Accessibility improvements
        document.addEventListener('keydown', function(e) {
            // Enter key on buttons
            if (e.key === 'Enter' && e.target.tagName === 'BUTTON') {
                e.target.click();
            }
            
            // Escape key to close modals/toasts
            if (e.key === 'Escape') {
                const toast = document.getElementById('toast');
                if (toast.classList.contains('show')) {
                    toast.classList.remove('show');
                }
            }
        });

        // Progressive Web App features
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Register service worker for offline functionality (if available)
                navigator.serviceWorker.register('/sw.js').catch(function(error) {
                    console.log('ServiceWorker registration failed: ', error);
                });
            });
        }

        // Enhanced security - disable right-click context menu on sensitive elements
        document.querySelectorAll('#qrcode, .qr-code-container').forEach(element => {
            element.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });
        });

        // Analytics tracking (optional)
        function trackEvent(action, category = 'RSVP') {
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: category,
                    event_label: CONFIG.eventData.event_name
                });
            }
        }

        // Track initial page view
        trackEvent('page_view', 'Invitation');

        // Image handling functions
        function toggleImageView(img) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            
            modalImg.src = img.src;
            modal.classList.add('active');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('active');
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Apply image style based on preference
        function setImageStyle(style) {
            const img = document.querySelector('.event-image');
            if (!img) return;
            
            // Remove all style classes
            img.classList.remove('fixed-height', 'aspect-ratio', 'max-height', 'responsive');
            
            // Add selected style
            if (style && style !== 'default') {
                img.classList.add(style);
            }
            
            // Save preference
            localStorage.setItem('preferred_image_style', style);
        }

        // Load saved image style preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedStyle = localStorage.getItem('preferred_image_style') || 'responsive';
            setImageStyle(savedStyle);
        });
    </script>
    <?php endif; ?>
</body>
</html>
