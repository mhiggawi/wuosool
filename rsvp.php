<?php
// rsvp.php - نسخة محسّنة مع أمان وميزات إضافية
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

session_start();
require_once 'db_config.php';

// --- نظام اللغة المحسّن ---
$lang = $_SESSION['language'] ?? $_COOKIE['language'] ?? 'ar';
if (isset($_POST['switch_language'])) {
    $lang = $_POST['switch_language'] === 'en' ? 'en' : 'ar';
    $_SESSION['language'] = $lang;
    setcookie('language', $lang, time() + (365 * 24 * 60 * 60), '/');
    // إعادة تحميل الصفحة مع اللغة الجديدة
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// نصوص متعددة اللغات
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
        'processing' => 'جاري المعالجة...',
        'success_confirmed' => 'تم تأكيد حضورك بنجاح!',
        'success_declined' => 'تم تسجيل اعتذارك.',
        'error_occurred' => 'حدث خطأ، يرجى المحاولة مرة أخرى.',
        'connection_error' => 'خطأ في الاتصال، يرجى التحقق من الإنترنت.',
        'invalid_link' => 'رابط الدعوة غير صحيح أو منتهي الصلاحية.',
        'show_qr_instruction' => 'يرجى إظهار هذا الرمز عند الدخول',
        'download_qr' => 'تحميل رمز QR',
        'add_to_calendar' => 'إضافة للتقويم',
        'share_invitation' => 'مشاركة الدعوة',
        'view_location' => 'عرض الموقع',
        'guest_details' => 'تفاصيل الضيف'
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
        'processing' => 'Processing...',
        'success_confirmed' => 'Your attendance has been confirmed successfully!',
        'success_declined' => 'Your decline has been recorded.',
        'error_occurred' => 'An error occurred, please try again.',
        'connection_error' => 'Connection error, please check your internet.',
        'invalid_link' => 'Invalid or expired invitation link.',
        'show_qr_instruction' => 'Please show this code at the entrance',
        'download_qr' => 'Download QR Code',
        'add_to_calendar' => 'Add to Calendar',
        'share_invitation' => 'Share Invitation',
        'view_location' => 'View Location',
        'guest_details' => 'Guest Details'
    ]
];

$t = $texts[$lang];

// --- حماية CSRF ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Rate Limiting ---
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_key = 'rsvp_' . md5($client_ip);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'time' => time()];
}

// إعادة تعيين العداد كل دقيقة
if (time() - $_SESSION[$rate_limit_key]['time'] > 60) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'time' => time()];
}

// --- متغيرات البيانات ---
$guest_id = filter_var(trim($_GET['id'] ?? ''), FILTER_SANITIZE_STRING);
$event_data = null;
$guest_data = null;
$error_message = '';
$success_message = '';

// --- التحقق من صحة guest_id ---
if (empty($guest_id) || strlen($guest_id) < 3 || strlen($guest_id) > 10) {
    $error_message = $t['invalid_link'];
} else {
    // جلب بيانات الضيف مع التحقق الآمن
    $sql_guest = "SELECT g.*, e.* FROM guests g 
                  JOIN events e ON g.event_id = e.id 
                  WHERE g.guest_id = ? LIMIT 1";
    
    if ($stmt_guest = $mysqli->prepare($sql_guest)) {
        $stmt_guest->bind_param("s", $guest_id);
        $stmt_guest->execute();
        $result_guest = $stmt_guest->get_result();
        
        if ($result_guest && $result_guest->num_rows === 1) {
            $combined_data = $result_guest->fetch_assoc();
            
            // فصل بيانات الضيف عن بيانات الحدث
            $guest_data = [
                'id' => $combined_data['id'],
                'guest_id' => $combined_data['guest_id'],
                'name_ar' => $combined_data['name_ar'],
                'phone_number' => $combined_data['phone_number'],
                'guests_count' => $combined_data['guests_count'],
                'table_number' => $combined_data['table_number'],
                'status' => $combined_data['status'],
                'event_id' => $combined_data['event_id']
            ];
            
            $event_data = [
                'id' => $combined_data['event_id'],
                'event_name' => $combined_data['event_name'],
                'bride_name_ar' => $combined_data['bride_name_ar'],
                'groom_name_ar' => $combined_data['groom_name_ar'],
                'event_date_ar' => $combined_data['event_date_ar'],
                'event_date_en' => $combined_data['event_date_en'],
                'venue_ar' => $combined_data['venue_ar'],
                'venue_en' => $combined_data['venue_en'],
                'Maps_link' => $combined_data['Maps_link'],
                'background_image_url' => $combined_data['background_image_url'],
                'event_paragraph_ar' => $combined_data['event_paragraph_ar'],
                'event_paragraph_en' => $combined_data['event_paragraph_en'],
                'qr_card_title_ar' => $combined_data['qr_card_title_ar'],
                'qr_card_title_en' => $combined_data['qr_card_title_en'],
                'qr_show_code_instruction_ar' => $combined_data['qr_show_code_instruction_ar'],
                'qr_show_code_instruction_en' => $combined_data['qr_show_code_instruction_en'],
                'qr_brand_text_ar' => $combined_data['qr_brand_text_ar'],
                'qr_brand_text_en' => $combined_data['qr_brand_text_en']
            ];
        } else {
            $error_message = $t['invalid_link'];
        }
        $stmt_guest->close();
    } else {
        $error_message = $t['connection_error'];
    }
}

// إعداد البيانات للـ JavaScript
$json_data = json_encode([
    'guest' => $guest_data, 
    'event' => $event_data,
    'texts' => $t,
    'lang' => $lang,
    'csrf_token' => $_SESSION['csrf_token']
], JSON_UNESCAPED_UNICODE);

// دالة مساعدة آمنة
function safe_html($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

// دالة للتحقق من وجود الصورة
function hasValidImage($image_url) {
    return !empty($image_url) && 
           $image_url !== 'NULL' && 
           $image_url !== 'null' &&
           filter_var($image_url, FILTER_VALIDATE_URL) !== false;
}

// دالة لإنشاء رابط Google Calendar
function generateCalendarLink($event_data, $lang) {
    if (empty($event_data)) return '#';
    
    $event_name = $lang === 'ar' ? $event_data['event_name'] : $event_data['event_name'];
    $venue = $lang === 'ar' ? $event_data['venue_ar'] : $event_data['venue_en'];
    $date = $lang === 'ar' ? $event_data['event_date_ar'] : $event_data['event_date_en'];
    
    $details = urlencode("$event_name - $date في $venue");
    
    return "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . 
           urlencode($event_name) . "&details=" . $details;
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="<?= $event_data ? safe_html($event_data['event_name']) : 'دعوة' ?>">
    <meta property="og:description" content="<?= $t['wedding_blessing'] ?>">
    <?php if ($event_data && hasValidImage($event_data['background_image_url'])): ?>
    <meta property="og:image" content="<?= safe_html($event_data['background_image_url']) ?>">
    <?php endif; ?>
    
    <title><?= $event_data ? safe_html($event_data['event_name']) : 'دعوة' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    
    <style>
        body { 
            font-family: <?= $lang === 'ar' ? "'Cairo', 'Inter', sans-serif" : "'Inter', 'Cairo', sans-serif" ?>; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .container { 
            max-width: 600px; 
            width: 100%; 
            background-color: #ffffff; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); 
            overflow: hidden; 
            direction: <?= $lang === 'ar' ? 'rtl' : 'ltr' ?>; 
            text-align: <?= $lang === 'ar' ? 'right' : 'left' ?>;
            backdrop-filter: blur(10px);
        }
        
        .header { 
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            padding: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .language-toggle { 
            background: rgba(102, 126, 234, 0.1);
            color: #667eea; 
            border: 1px solid rgba(102, 126, 234, 0.3);
            padding: 8px 16px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .language-toggle:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-1px);
        }
        
        .invitation-card {
            background-image: url('<?= $event_data && hasValidImage($event_data['background_image_url']) ? safe_html($event_data['background_image_url']) : '' ?>');
            background-size: cover; 
            background-position: center; 
            padding: 40px 30px; 
            color: #333; 
            position: relative;
            min-height: 400px;
        }
        
        .invitation-card::before { 
            content: ''; 
            position: absolute; 
            top: 0; left: 0; right: 0; bottom: 0; 
            background: linear-gradient(to bottom, 
                rgba(255,255,255,0.85) 0%, 
                rgba(255,255,255,0.90) 50%,
                rgba(255,255,255,0.85) 100%
            ); 
            z-index: 1; 
        }
        
        .invitation-content { 
            position: relative; 
            z-index: 2; 
            text-align: center;
        }
        
        .button-section { 
            padding: 25px; 
            border-top: 1px solid #eee; 
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
        }
        
        .qr-code-section { 
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 30px; 
            border-top: 1px solid #eee; 
            display: none; 
            flex-direction: column; 
            align-items: center; 
            text-align: center; 
        }
        
        .qr-code-container { 
            padding: 20px; 
            background: #fff; 
            border-radius: 15px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
            margin: 20px auto;
            border: 3px solid #667eea;
        }
        
        .location-section {
            padding: 20px 25px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .footer { 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-align: center; 
            font-weight: 600;
        }
        
        .error-container { 
            text-align: center; 
            padding: 60px 20px; 
        }
        
        .error-icon { 
            font-size: 4rem; 
            margin-bottom: 1rem; 
        }
        
        .btn {
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            min-width: 120px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-decline {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .loading-spinner {
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
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .toast.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .guest-details-card {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        @media (max-width: 640px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .invitation-card {
                padding: 30px 20px;
            }
            
            .btn {
                font-size: 14px;
                padding: 12px 20px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error_message)): ?>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= $t['invalid_link'] ?></h2>
                <p class="text-gray-600"><?= htmlspecialchars($error_message) ?></p>
                <div class="mt-6">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" name="switch_language" value="<?= $lang === 'ar' ? 'en' : 'ar' ?>" 
                                class="language-toggle">
                            <?= $lang === 'ar' ? 'English' : 'العربية' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Header Section -->
            <div class="header">
                <span class="font-bold text-lg text-gray-800">
                    <?= safe_html($lang === 'ar' ? $event_data['qr_brand_text_ar'] : $event_data['qr_brand_text_en'], 'دعواتي') ?>
                </span>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" name="switch_language" value="<?= $lang === 'ar' ? 'en' : 'ar' ?>" 
                            class="language-toggle">
                        <?= $lang === 'ar' ? 'English' : 'العربية' ?>
                    </button>
                </form>
            </div>

            <!-- Invitation Card Section -->
            <div class="invitation-card">
                <div class="invitation-content">
                    <?php if (!hasValidImage($event_data['background_image_url'])): ?>
                        <!-- عرض النص عند عدم وجود صورة -->
                        <div class="guest-details-card">
                            <div class="detail-row">
                                <span class="font-medium text-gray-700"><?= $t['guest_count'] ?>:</span>
                                <span class="text-gray-900"><?= safe_html($guest_data['guests_count'], '1') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="font-medium text-gray-700"><?= $t['table_number'] ?>:</span>
                                <span class="text-gray-900"><?= safe_html($guest_data['table_number'], $t['not_specified']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <?php if (!empty($event_data['venue_ar']) || !empty($event_data['Maps_link'])): ?>
            <div class="location-section">
                <?php if (!empty($event_data['Maps_link']) && filter_var($event_data['Maps_link'], FILTER_VALIDATE_URL)): ?>
                    <a href="<?= safe_html($event_data['Maps_link']) ?>" target="_blank" 
                       class="flex items-center justify-between text-gray-700 hover:text-blue-600 transition-colors">
                        <div>
                            <div class="font-medium text-lg">
                                <?= safe_html($lang === 'ar' ? $event_data['venue_ar'] : $event_data['venue_en'], 'مكان الحفل') ?>
                            </div>
                            <div class="text-sm text-gray-500 mt-1"><?= $t['view_location'] ?></div>
                        </div>
                        <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                    </a>
                <?php else: ?>
                    <div class="text-center">
                        <div class="font-medium text-lg text-gray-700">
                            <?= safe_html($lang === 'ar' ? $event_data['venue_ar'] : $event_data['venue_en'], 'مكان الحفل') ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div id="action-buttons" class="button-section">
                <?php if ($guest_data['status'] === 'confirmed'): ?>
                    <div class="text-center">
                        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
                            <strong><?= $t['already_confirmed'] ?></strong>
                        </div>
                    </div>
                <?php elseif ($guest_data['status'] === 'canceled'): ?>
                    <div class="text-center">
                        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
                            <strong><?= $t['already_declined'] ?></strong>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="action-buttons">
                        <button id="confirm-button" class="btn btn-confirm">
                            <span class="loading-spinner" id="confirm-spinner"></span>
                            <span id="confirm-text"><?= $t['confirm_attendance'] ?></span>
                        </button>
                        <button id="decline-button" class="btn btn-decline">
                            <span class="loading-spinner" id="decline-spinner"></span>
                            <span id="decline-text"><?= $t['decline_attendance'] ?></span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <?php if (!empty($event_data['Maps_link'])): ?>
                        <a href="<?= generateCalendarLink($event_data, $lang) ?>" target="_blank" 
                           class="btn btn-outline btn-small">
                            📅 <?= $t['add_to_calendar'] ?>
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="shareInvitation()" class="btn btn-outline btn-small">
                        📤 <?= $t['share_invitation'] ?>
                    </button>
                </div>
            </div>

            <!-- QR Code Section -->
            <div id="qr-code-section" class="qr-code-section" 
                 style="<?= $guest_data['status'] === 'confirmed' ? 'display: flex;' : 'display: none;' ?>">
                <h3 class="text-xl font-bold mb-2 text-gray-800">
                    <?= safe_html($lang === 'ar' ? $event_data['qr_card_title_ar'] : $event_data['qr_card_title_en']) ?>
                </h3>
                <p class="text-gray-600 mb-4">
                    <?= safe_html($lang === 'ar' ? $event_data['qr_show_code_instruction_ar'] : $event_data['qr_show_code_instruction_en']) ?>
                </p>
                
                <div id="qrcode" class="qr-code-container"></div>
                
                <div class="flex gap-2 mt-4">
                    <button onclick="downloadQR()" class="btn btn-secondary btn-small">
                        📥 <?= $t['download_qr'] ?>
                    </button>
                    <button onclick="shareQR()" class="btn btn-outline btn-small">
                        📤 مشاركة QR
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="text-center">
                    <span class="text-lg">
                        <?= safe_html($lang === 'ar' ? $event_data['qr_brand_text_ar'] : $event_data['qr_brand_text_en'], 'دعواتي') ?>
                    </span>
                    <?php if (!empty($event_data['qr_website'])): ?>
                        <div class="text-sm mt-1 opacity-90">
                            <?= safe_html($event_data['qr_website']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <?php if (empty($error_message)): ?>
    <script>
        // البيانات والنصوص
        const allData = <?= $json_data ?>;
        const guestData = allData.guest;
        const eventData = allData.event;
        const texts = allData.texts;
        const currentLang = allData.lang;
        const csrfToken = allData.csrf_token;
        
        // العناصر
        const confirmButton = document.getElementById('confirm-button');
        const declineButton = document.getElementById('decline-button');
        const qrCodeSection = document.getElementById('qr-code-section');
        const qrcodeContainer = document.getElementById('qrcode');
        const actionButtonsSection = document.getElementById('action-buttons');
        const toastElement = document.getElementById('toast');
        
        let qrCodeGenerated = false;

        // إنشاء QR Code
        function generateQRCode(data) {
            if (qrCodeGenerated) return;
            
            qrcodeContainer.innerHTML = '';
            try {
                new QRCode(qrcodeContainer, { 
                    text: data, 
                    width: 200, 
                    height: 200,
                    colorDark: "#667eea",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                qrCodeGenerated = true;
            } catch (error) {
                console.error('Error generating QR code:', error);
                showToast(texts.error_occurred, 'error');
            }
        }

        // عرض التوست
        function showToast(message, type = 'success') {
            toastElement.textContent = message;
            toastElement.className = `toast ${type}`;
            toastElement.classList.add('show');
            
            setTimeout(() => {
                toastElement.classList.remove('show');
            }, 4000);
        }

        // إرسال استجابة RSVP
        async function sendRsvpResponse(status) {
            const button = status === 'confirmed' ? confirmButton : declineButton;
            const spinner = document.getElementById(status === 'confirmed' ? 'confirm-spinner' : 'decline-spinner');
            const textElement = document.getElementById(status === 'confirmed' ? 'confirm-text' : 'decline-text');
            
            // تعطيل الأزرار
            if (confirmButton) confirmButton.disabled = true;
            if (declineButton) declineButton.disabled = true;
            
            // إظهار اللودينج
            if (spinner) spinner.style.display = 'inline-block';
            if (textElement) textElement.textContent = texts.processing;
            
            try {
                const response = await fetch('api_rsvp_handler.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ 
                        guest_id: guestData.guest_id, 
                        status: status 
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    if (status === 'confirmed') {
                        actionButtonsSection.style.display = 'none';
                        qrCodeSection.style.display = 'flex';
                        generateQRCode(guestData.guest_id);
                        showToast(texts.success_confirmed, 'success');
                        
                        // تحديث حالة الضيف محلياً
                        guestData.status = 'confirmed';
                    } else {
                        actionButtonsSection.style.display = 'none';
                        showToast(texts.success_declined, 'success');
                        
                        // تحديث حالة الضيف محلياً
                        guestData.status = 'canceled';
                        
                        // إظهار رسالة الاعتذار
                        setTimeout(() => {
                            actionButtonsSection.innerHTML = `
                                <div class="text-center">
                                    <div class="bg-red-100 text-red-800 p-4 rounded-lg">
                                        <strong>${texts.already_declined}</strong>
                                    </div>
                                </div>
                            `;
                            actionButtonsSection.style.display = 'block';
                        }, 2000);
                    }
                } else {
                    throw new Error(result.message || texts.error_occurred);
                }
            } catch (error) {
                console.error('RSVP Error:', error);
                
                let errorMessage = texts.error_occurred;
                if (error.message.includes('fetch')) {
                    errorMessage = texts.connection_error;
                }
                
                showToast(errorMessage, 'error');
                
                // إعادة تفعيل الأزرار
                if (confirmButton) confirmButton.disabled = false;
                if (declineButton) declineButton.disabled = false;
                
                // إخفاء اللودينج
                if (spinner) spinner.style.display = 'none';
                if (textElement) {
                    textElement.textContent = status === 'confirmed' ? texts.confirm_attendance : texts.decline_attendance;
                }
            }
        }

        // تحميل QR Code
        function downloadQR() {
            const canvas = qrcodeContainer.querySelector('canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = `qr-code-${guestData.guest_id}.png`;
                link.href = canvas.toDataURL();
                link.click();
                
                showToast('تم تحميل رمز QR بنجاح', 'success');
            }
        }

        // مشاركة QR Code
        async function shareQR() {
            const canvas = qrcodeContainer.querySelector('canvas');
            if (canvas && navigator.share) {
                try {
                    const blob = await new Promise(resolve => canvas.toBlob(resolve));
                    const file = new File([blob], `qr-code-${guestData.guest_id}.png`, { type: 'image/png' });
                    
                    await navigator.share({
                        title: eventData.event_name,
                        text: texts.show_qr_instruction,
                        files: [file]
                    });
                } catch (error) {
                    console.log('Share cancelled or failed:', error);
                }
            } else {
                // fallback للمتصفحات التي لا تدعم Web Share API
                downloadQR();
            }
        }

        // مشاركة الدعوة
        async function shareInvitation() {
            const shareData = {
                title: eventData.event_name,
                text: `${texts.wedding_blessing} - ${eventData.event_name}`,
                url: window.location.href
            };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                } catch (error) {
                    console.log('Share cancelled:', error);
                }
            } else {
                // fallback - نسخ الرابط
                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(window.location.href);
                    showToast('تم نسخ رابط الدعوة', 'success');
                }
            }
        }

        // ربط الأحداث
        if (confirmButton) {
            confirmButton.addEventListener('click', () => sendRsvpResponse('confirmed'));
        }
        
        if (declineButton) {
            declineButton.addEventListener('click', () => sendRsvpResponse('canceled'));
        }

        // إنشاء QR Code إذا كان الضيف مؤكد مسبقاً
        if (guestData.status === 'confirmed' && qrcodeContainer) {
            generateQRCode(guestData.guest_id);
        }

        // تحسين الأداء - preload
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة preload للخطوط
            const fontLink = document.createElement('link');
            fontLink.rel = 'preload';
            fontLink.href = 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap';
            fontLink.as = 'style';
            document.head.appendChild(fontLink);
        });

        // تتبع الأخطاء
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
        });

        // منع الإرسال المتكرر
        let isProcessing = false;
        
        function preventDoubleSubmit(callback) {
            return function(...args) {
                if (isProcessing) return;
                isProcessing = true;
                
                Promise.resolve(callback.apply(this, args))
                    .finally(() => {
                        setTimeout(() => { isProcessing = false; }, 1000);
                    });
            };
        }

        // تطبيق الحماية على الأزرار
        if (confirmButton) {
            confirmButton.addEventListener('click', preventDoubleSubmit(() => sendRsvpResponse('confirmed')));
        }
        
        if (declineButton) {
            declineButton.addEventListener('click', preventDoubleSubmit(() => sendRsvpResponse('canceled')));
        }

        // إضافة خاصية التراجع
        let lastAction = null;
        
        function addUndoOption(action) {
            // يمكن إضافة خيار التراجع هنا مستقبلاً
            lastAction = action;
        }

        // تحسين إمكانية الوصول
        document.addEventListener('keydown', function(e) {
            // Enter أو Space لتأكيد الحضور
            if ((e.key === 'Enter' || e.key === ' ') && e.target === confirmButton) {
                e.preventDefault();
                confirmButton.click();
            }
            
            // Escape لإلغاء
            if (e.key === 'Escape' && e.target === declineButton) {
                e.preventDefault();
                declineButton.click();
            }
        });

        // تحسين الأداء للأجهزة المحمولة
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(console.error);
        }

    </script>
    <?php endif; ?>
</body>
</html>="text-content">
                            <?php 
                            $paragraph = $lang === 'ar' ? $event_data['event_paragraph_ar'] : $event_data['event_paragraph_en'];
                            if (!empty($paragraph) && $paragraph !== 'يرجى التحديث من لوحة التحكم'):
                            ?>
                                <p class="text-lg leading-relaxed text-gray-800"><?= nl2br(safe_html($paragraph)) ?></p>
                            <?php else: ?>
                                <!-- محتوى افتراضي للزفاف -->
                                <p class="text-xl font-bold mb-4"><?= $t['wedding_blessing'] ?></p>
                                
                                <?php if (!empty($event_data['bride_name_ar']) && !empty($event_data['groom_name_ar']) && 
                                          $event_data['bride_name_ar'] !== 'يرجى التحديث من لوحة التحكم'): ?>
                                    <p class="text-2xl font-semibold text-pink-600 my-2">
                                        <?= safe_html($event_data['bride_name_ar']) ?>
                                    </p>
                                    <p class="text-lg mt-4">و</p>
                                    <p class="text-2xl font-semibold text-blue-600 my-2">
                                        <?= safe_html($event_data['groom_name_ar']) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-lg text-center my-6">
                                    <?= $t['wedding_occasion'] ?><br>
                                    <?= nl2br(safe_html($lang === 'ar' ? $event_data['event_date_ar'] : $event_data['event_date_en'])) ?><br>
                                    <?= $t['location_in'] ?> <?= safe_html($lang === 'ar' ? $event_data['venue_ar'] : $event_data['venue_en']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <hr class="my-6 border-gray-300">
                    
                    <!-- معلومات الضيف -->
                    <div class="guest-section">
                        <p class="text-lg font-semibold text-gray-800 mb-4">
                            <?= $t['guest_name_label'] ?> 
                            <span class="text-blue-600"><?= safe_html($guest_data['name_ar'], 'الضيف الكريم') ?></span>
                        </p>
                        
                        <div class
