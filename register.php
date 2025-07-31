<?php
// register.php - Enhanced with security and languages, keeping original design
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
        'registration_instruction' => 'يرجى تسجيل بياناتك لتأكيد الحضور أو الاعتذار.',
        'name_label' => 'الاسم الكريم:',
        'phone_label' => 'رقم الجوال (مع رمز الدولة):',
        'guests_count_label' => 'عدد الضيوف (شاملاً لك):',
        'confirm_attendance' => 'تأكيد الحضور',
        'decline_attendance' => 'الاعتذار عن الحضور',
        'select_country' => 'اختر الدولة',
        'enter_local_number' => 'أدخل رقم الجوال المحلي فقط (مثال: 791234567)',
        'enter_full_number' => 'أدخل الرقم كاملاً مع رمز الدولة (مثال: +96279123456)',
        'choose_country_first' => 'اختر الدولة أولاً',
        'registration_success_confirm' => 'تم تأكيد حضورك بنجاح! سيتم الآن نقلك لصفحة الدعوة الخاصة بك للحصول على QR Code.',
        'registration_success_cancel' => 'شكراً لك، تم تسجيل اعتذارك عن الحضور.',
        'registration_error' => 'حدث خطأ أثناء التسجيل. قد يكون رقم الهاتف مسجلاً مسبقاً.',
        'fill_all_fields' => 'الرجاء إدخال جميع الحقول المطلوبة.',
        'invalid_phone_format' => 'يرجى إدخال رقم الجوال المحلي فقط (بدون رمز الدولة). مثال: 791234567',
        'invalid_phone_international' => 'يرجى إدخال الرقم مع رمز الدولة الكامل (مثال: 96279...).',
        'invalid_phone_general' => 'صيغة رقم الجوال غير صحيحة. يرجى إدخال الرقم كاملاً مع رمز الدولة.',
        'countries' => [
            '+962' => 'الأردن (+962)',
            '+966' => 'السعودية (+966)', 
            '+971' => 'الإمارات (+971)',
            '+965' => 'الكويت (+965)',
            '+974' => 'قطر (+974)',
            '+973' => 'البحرين (+973)',
            '+968' => 'عُمان (+968)',
            '+961' => 'لبنان (+961)',
            '+963' => 'سوريا (+963)',
            '+964' => 'العراق (+964)',
            '+970' => 'فلسطين (+970)',
            '+20' => 'مصر (+20)',
            '+1' => 'أمريكا/كندا (+1)',
            '+44' => 'بريطانيا (+44)',
            '+49' => 'ألمانيا (+49)',
            '+33' => 'فرنسا (+33)',
            '+90' => 'تركيا (+90)',
            'other' => 'دولة أخرى'
        ]
    ],
    'en' => [
        'registration_instruction' => 'Please enter your details to confirm or decline attendance',
        'name_label' => 'Full Name:',
        'phone_label' => 'Mobile Number (with country code):',
        'guests_count_label' => 'Number of Guests (including you):',
        'confirm_attendance' => 'Confirm Attendance',
        'decline_attendance' => 'Decline Attendance',
        'select_country' => 'Select Country',
        'enter_local_number' => 'Enter local mobile number only (example: 791234567)',
        'enter_full_number' => 'Enter full number with country code (example: +96279123456)',
        'choose_country_first' => 'Choose country first',
        'registration_success_confirm' => 'Your attendance has been confirmed successfully! You will now be redirected to your invitation page to get the QR Code.',
        'registration_success_cancel' => 'Thank you, your decline has been recorded.',
        'registration_error' => 'An error occurred during registration. The phone number may already be registered.',
        'fill_all_fields' => 'Please fill in all required fields.',
        'invalid_phone_format' => 'Please enter local mobile number only (without country code). Example: 791234567',
        'invalid_phone_international' => 'Please enter the number with full country code (example: 96279...).',
        'invalid_phone_general' => 'Invalid mobile number format. Please enter the full number with country code.',
        'countries' => [
            '+962' => 'Jordan (+962)',
            '+966' => 'Saudi Arabia (+966)', 
            '+971' => 'UAE (+971)',
            '+965' => 'Kuwait (+965)',
            '+974' => 'Qatar (+974)',
            '+973' => 'Bahrain (+973)',
            '+968' => 'Oman (+968)',
            '+961' => 'Lebanon (+961)',
            '+963' => 'Syria (+963)',
            '+964' => 'Iraq (+964)',
            '+970' => 'Palestine (+970)',
            '+20' => 'Egypt (+20)',
            '+1' => 'USA/Canada (+1)',
            '+44' => 'UK (+44)',
            '+49' => 'Germany (+49)',
            '+33' => 'France (+33)',
            '+90' => 'Turkey (+90)',
            'other' => 'Other Country'
        ]
    ]
];

$t = $texts[$lang];

// --- CSRF Protection ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Rate Limiting ---
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_key = 'rate_limit_' . md5($client_ip);
$current_time = time();

if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'first_attempt' => $current_time];
}

$message = '';
$messageType = '';
$registration_successful = false;
$redirect_url = '';

// --- Get Event ID from URL ---
$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    die('رابط التسجيل غير صالح: معرف الحفل مفقود.');
}

// --- Fetch Event Details ---
$event = null;
$stmt_event = $mysqli->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
if ($stmt_event) {
    $stmt_event->bind_param("i", $event_id);
    $stmt_event->execute();
    $result_event = $stmt_event->get_result();
    if ($result_event && $result_event->num_rows > 0) {
        $event = $result_event->fetch_assoc();
    } else {
        die('الحفل المطلوب غير موجود.');
    }
    $stmt_event->close();
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['switch_language'])) {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Security token mismatch. Please try again.';
        $messageType = 'error';
    } else {
        // Rate Limiting Check
        if ($current_time - $_SESSION[$rate_limit_key]['first_attempt'] > 300) {
            $_SESSION[$rate_limit_key] = ['count' => 0, 'first_attempt' => $current_time];
        }
        
        if ($_SESSION[$rate_limit_key]['count'] >= 5) {
            $message = 'تم إرسال طلبات كثيرة. يرجى الانتظار قبل المحاولة مرة أخرى.';
            $messageType = 'error';
        } else {
            $_SESSION[$rate_limit_key]['count']++;
            
            $name_ar = trim($_POST['name_ar'] ?? '');
            $country_code = trim($_POST['country_code'] ?? '');
            $phone_number_raw = trim($_POST['phone_number'] ?? '');
            $guests_count = filter_input(INPUT_POST, 'guests_count', FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 20]
            ]) ?: 1;
            $status = in_array($_POST['rsvp_status'] ?? '', ['confirmed', 'canceled']) ? $_POST['rsvp_status'] : 'canceled';

            if (empty($name_ar) || empty($country_code) || empty($phone_number_raw)) {
                $message = $t['fill_all_fields'];
                $messageType = 'error';
            } else {
                // Phone Number Validation Logic (same as before)
                $is_valid = false;
                $error_message = '';

                if ($country_code === 'other') {
                    $phone_to_validate = $phone_number_raw;
                    
                    if (substr($phone_to_validate, 0, 2) === '00') {
                        $phone_to_validate = substr($phone_to_validate, 2);
                    } elseif (substr($phone_to_validate, 0, 1) === '+') {
                        $phone_to_validate = substr($phone_to_validate, 1);
                    }
                    
                    if (substr($phone_number_raw, 0, 1) === '0' && substr($phone_number_raw, 0, 2) !== '00') {
                        $error_message = $t['invalid_phone_international'];
                    } elseif (!ctype_digit($phone_to_validate) || strlen($phone_to_validate) < 10 || strlen($phone_to_validate) > 15) {
                        $error_message = $t['invalid_phone_general'];
                    } else {
                        $is_valid = true;
                        $phone_number_normalized = '+' . $phone_to_validate;
                    }
                } else {
                    $local_number = $phone_number_raw;
                    
                    if (substr($local_number, 0, 1) === '0') {
                        $local_number = substr($local_number, 1);
                    }
                    
                    if (!ctype_digit($local_number) || strlen($local_number) < 7 || strlen($local_number) > 10) {
                        $error_message = $t['invalid_phone_format'];
                    } else {
                        $is_valid = true;
                        $phone_number_normalized = $country_code . $local_number;
                    }
                }
                
                if (!$is_valid) {
                    $message = $error_message;
                    $messageType = 'error';
                } else {
                    // Check for duplicate
                    $stmt_check = $mysqli->prepare("SELECT id FROM guests WHERE phone_number = ? AND event_id = ? LIMIT 1");
                    if ($stmt_check) {
                        $stmt_check->bind_param("si", $phone_number_normalized, $event_id);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();
                        
                        if ($result_check && $result_check->num_rows > 0) {
                            $message = $t['registration_error'];
                            $messageType = 'error';
                        } else {
                            // Insert new guest
                            $guest_id = substr(md5(uniqid($phone_number_normalized . microtime(), true)), 0, 4);
                            
                            $stmt = $mysqli->prepare("INSERT INTO guests (event_id, guest_id, name_ar, phone_number, guests_count, status) VALUES (?, ?, ?, ?, ?, ?)");
                            if ($stmt) {
                                $stmt->bind_param("isssis", $event_id, $guest_id, $name_ar, $phone_number_normalized, $guests_count, $status);
                                
                                if ($stmt->execute()) {
                                    $registration_successful = true;
                                    if ($status === 'confirmed') {
                                        $message = $t['registration_success_confirm'];
                                        $messageType = 'success';
                                        $redirect_url = "rsvp.php?id=" . $guest_id;
                                        
                                        // Webhook call
                                        $webhook_url = $event['n8n_confirm_webhook'] ?? null;
                                        if ($webhook_url && filter_var($webhook_url, FILTER_VALIDATE_URL)) {
                                            $n8n_payload = json_encode(['guest_id' => $guest_id, 'phone_number' => $phone_number_normalized]);
                                            $ch = curl_init($webhook_url);
                                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, $n8n_payload);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($n8n_payload)]);
                                            curl_exec($ch);
                                            curl_close($ch);
                                        }
                                    } else {
                                        $message = $t['registration_success_cancel'];
                                        $messageType = 'success';
                                    }
                                    
                                    // Reset rate limit on success
                                    unset($_SESSION[$rate_limit_key]);
                                } else {
                                    $message = $t['registration_error'];
                                    $messageType = 'error';
                                }
                                $stmt->close();
                            }
                        }
                        $stmt_check->close();
                    }
                }
            }
        }
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حضور: <?= htmlspecialchars($event['event_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .card-container { max-width: 500px; width: 100%; background-color: #ffffff; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .card-content { padding: 30px; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; }
        .phone-input-container { display: flex; gap: 10px; }
        .phone-input-container select { flex: 0 0 40%; }
        .phone-input-container input { flex: 1; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 8px; text-align: center; }
        .message.success { background-color: #d1fae5; color: #065f46; }
        .message.error { background-color: #fee2e2; color: #991b1b; }
        .help-text { font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; }
        #phone-help-text { min-height: 1.25rem; }
        
        /* Language Toggle Button - positioned at bottom */
        .language-toggle {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 16px;
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .language-toggle:hover {
            background-color: #e5e7eb;
            border-color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <?php if (!empty($event['background_image_url'])): ?>
            <img src="<?= htmlspecialchars($event['background_image_url']) ?>" alt="<?= htmlspecialchars($event['event_name']) ?>" class="w-full h-auto">
        <?php endif; ?>

        <div class="card-content">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($event['event_name']) ?></h1>
                <p class="text-lg text-gray-600 mt-2"><?= nl2br(htmlspecialchars($event['event_date_ar'])) ?></p>
            </div>
            
            <?php if ($message && !$redirect_url): ?>
                <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (!$registration_successful): ?>
                <form id="rsvpForm" method="POST" action="register.php?event_id=<?= $event_id ?>" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="rsvp_status" id="rsvp_status" value="confirmed">
                    <p class="text-center text-gray-700 mb-6"><?= $t['registration_instruction'] ?></p>
                    
                    <div class="form-group">
                        <label for="name_ar"><?= $t['name_label'] ?></label>
                        <input type="text" id="name_ar" name="name_ar" required 
                               value="<?= htmlspecialchars($_POST['name_ar'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country_code"><?= $t['phone_label'] ?></label>
                        <div class="phone-input-container">
                            <select id="country_code" name="country_code" required onchange="updatePhonePlaceholder()">
                                <option value=""><?= $t['select_country'] ?></option>
                                <?php foreach ($t['countries'] as $code => $name): ?>
                                    <option value="<?= htmlspecialchars($code) ?>" 
                                            <?= (($_POST['country_code'] ?? '+962') === $code) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="tel" id="phone_number" name="phone_number" required placeholder="791234567"
                                   value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>">
                        </div>
                        <div class="help-text" id="phone-help-text"><?= $t['enter_local_number'] ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="guests_count"><?= $t['guests_count_label'] ?></label>
                        <input type="number" id="guests_count" name="guests_count" 
                               value="<?= htmlspecialchars($_POST['guests_count'] ?? '1') ?>" min="1" max="20" required>
                    </div>
                    
                    <div class="flex gap-4 mt-6">
                        <button type="submit" onclick="document.getElementById('rsvp_status').value='confirmed';" 
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg">
                            <?= $t['confirm_attendance'] ?>
                        </button>
                        <button type="submit" onclick="document.getElementById('rsvp_status').value='canceled';" 
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-lg">
                            <?= $t['decline_attendance'] ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <!-- Language Toggle at Bottom -->
            <div class="text-center">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" name="switch_language" value="<?= $lang === 'ar' ? 'en' : 'ar' ?>" 
                            class="language-toggle">
                        <?= $lang === 'ar' ? 'English' : 'العربية' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($registration_successful && !empty($redirect_url)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert("<?= addslashes(htmlspecialchars($message)) ?>");
            window.location.href = "<?= $redirect_url ?>";
        });
    </script>
    <?php endif; ?>

    <script>
        const texts = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;
        const lang = '<?= $lang ?>';
        
        function updatePhonePlaceholder() {
            const countrySelect = document.getElementById('country_code');
            const phoneInput = document.getElementById('phone_number');
            const helpText = document.getElementById('phone-help-text');
            
            const selectedValue = countrySelect.value;
            
            if (selectedValue === 'other') {
                phoneInput.placeholder = '+96279123456';
                helpText.textContent = texts['enter_full_number'];
            } else if (selectedValue === '+962') {
                phoneInput.placeholder = '791234567';
                helpText.textContent = lang === 'ar' ? 'أدخل رقم الجوال الأردني (مثال: 791234567)' : 'Enter Jordanian mobile number (example: 791234567)';
            } else if (selectedValue === '+966') {
                phoneInput.placeholder = '501234567';
                helpText.textContent = lang === 'ar' ? 'أدخل رقم الجوال السعودي (مثال: 501234567)' : 'Enter Saudi mobile number (example: 501234567)';
            } else if (selectedValue === '+971') {
                phoneInput.placeholder = '501234567';
                helpText.textContent = lang === 'ar' ? 'أدخل رقم الجوال الإماراتي (مثال: 501234567)' : 'Enter UAE mobile number (example: 501234567)';
            } else if (selectedValue) {
                phoneInput.placeholder = '12345678';
                helpText.textContent = texts['enter_local_number'];
            } else {
                phoneInput.placeholder = '';
                helpText.textContent = texts['choose_country_first'];
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePhonePlaceholder();
        });
        
        // Simple form validation
        document.getElementById('rsvpForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name_ar').value.trim();
            const country = document.getElementById('country_code').value;
            const phone = document.getElementById('phone_number').value.trim();
            
            if (name.length < 2) {
                e.preventDefault();
                alert(lang === 'ar' ? 'يرجى إدخال اسم صحيح' : 'Please enter a valid name');
                return;
            }
            
            if (!country) {
                e.preventDefault();
                alert(lang === 'ar' ? 'يرجى اختيار الدولة' : 'Please select country');
                return;
            }
            
            if (phone.length < 7) {
                e.preventDefault();
                alert(lang === 'ar' ? 'يرجى إدخال رقم هاتف صحيح' : 'Please enter a valid phone number');
                return;
            }
        });
    </script>
</body>
</html>