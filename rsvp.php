<?php
// rsvp.php - v3 (Adds guest details pre-confirmation and image fallback)

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once 'db_config.php';

// --- Data Initialization ---
$guest_id = $_GET['id'] ?? '';
$event_data = null;
$guest_data = null;
$error_message = '';

if (empty($guest_id)) {
    $error_message = "رابط الدعوة غير مكتمل. يرجى استخدام الرابط الذي تم إرساله إليك.";
} else {
    // Fetch guest data
    $sql_guest = "SELECT * FROM guests WHERE guest_id = ?";
    if ($stmt_guest = $mysqli->prepare($sql_guest)) {
        $stmt_guest->bind_param("s", $guest_id);
        $stmt_guest->execute();
        $result_guest = $stmt_guest->get_result();
        
        if ($result_guest->num_rows === 1) {
            $guest_data = $result_guest->fetch_assoc();
            
            // Fetch the associated event data
            $sql_event = "SELECT * FROM events WHERE id = ?";
            if ($stmt_event = $mysqli->prepare($sql_event)) {
                $stmt_event->bind_param("i", $guest_data['event_id']);
                $stmt_event->execute();
                $result_event = $stmt_event->get_result();
                $event_data = $result_event->fetch_assoc();
                $stmt_event->close();
            }
        } else {
            $error_message = "رابط الدعوة غير صحيح أو أن الضيف غير موجود. يرجى التأكد من الرابط.";
        }
        $stmt_guest->close();
    }
}

// Combine all data for JavaScript usage
$json_data_for_js = json_encode(['guest' => $guest_data, 'event' => $event_data], JSON_UNESCAPED_UNICODE);

// Helper function to safely display data
function safe_html($value, $default = '') {
    return htmlspecialchars($value ?? $default);
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_data ? safe_html($event_data['event_name']) : 'دعوة' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        body { 
            font-family: 'Cairo', sans-serif; 
            background-color: #f0f2f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 20px;
        }
        .card-container { 
            max-width: 500px; 
            width: 100%; 
            background-color: #ffffff; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            overflow: hidden; 
        }
        .description-box {
            padding: 40px 25px;
            background-color: #f9fafb;
            text-align: center;
            color: #374151;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .card-content { 
            padding: 30px; 
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .action-buttons button {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            color: white;
            border: none;
            cursor: pointer;
        }
        .qr-code-section {
            background-color: #ffffff;
            padding: 25px;
            display: none; /* Initially hidden */
        }
        .qr-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .qr-title-box {
            grid-column: 1 / 2;
            grid-row: 1 / 2;
            background-color: #ffeedb; /* Beige/Orange color from image */
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .qr-instructions {
            grid-column: 2 / 3;
            grid-row: 1 / 2;
            text-align: right;
            padding-right: 10px;
        }
        .qr-brand-a {
            grid-column: 1 / 2;
            grid-row: 2 / 3;
            text-align: center;
            align-self: start;
            padding-top: 10px;
            color: #888;
        }
        .qr-code-container {
            grid-column: 2 / 3;
            grid-row: 2 / 3;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .qr-guest-count {
            grid-column: 1 / 2;
            grid-row: 3 / 4;
            text-align: center;
        }
        .qr-website-b {
            grid-column: 2 / 3;
            grid-row: 3 / 4;
            text-align: right;
            padding-right: 10px;
            color: #888;
            font-size: 0.9em;
        }
        .error-container { text-align: center; padding: 40px; }
    </style>
</head>
<body>
    <div class="card-container">
        <?php if (!empty($error_message)): ?>
            <div class="error-container">
                <p class="text-2xl font-bold text-red-600">⚠️</p>
                <p class="text-lg mt-4"><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php else: ?>
            
            <?php if (!empty($event_data['background_image_url'])): ?>
                <img src="<?= safe_html($event_data['background_image_url']) ?>" alt="<?= safe_html($event_data['event_name']) ?>" class="w-full h-auto">
            <?php else: ?>
                <div class="description-box">
                     <p><?= nl2br(safe_html($event_data['event_paragraph_ar'], 'مرحباً بكم في مناسبتنا الخاصة.')) ?></p>
                </div>
            <?php endif; ?>

            <div class="card-content">
                <p class="text-xl font-semibold text-center text-gray-800 mb-2">
                    أهلاً بك، <span class="text-blue-600"><?= safe_html($guest_data['name_ar'], 'الضيف الكريم') ?></span>
                </p>

                <div class="text-center text-sm text-gray-600 mb-4">
                    <span>عدد الضيوف: <?= safe_html($guest_data['guests_count'], '1') ?></span>
                    <?php if (!empty($guest_data['table_number'])): ?>
                        <span> | رقم الطاولة: <?= safe_html($guest_data['table_number']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border">
                    <a href="<?= safe_html($event_data['Maps_link'], '#') ?>" target="_blank" class="flex items-center justify-between text-gray-700 hover:text-blue-600">
                        <span class="font-medium"><?= safe_html($event_data['venue_ar'], 'مكان الحفل') ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </a>
                </div>

                <div id="action-buttons-section" class="action-buttons">
                    <button id="confirm-button" class="bg-green-500 hover:bg-green-600">تأكيد الحضور</button>
                    <button id="cancel-button" class="bg-red-500 hover:bg-red-600">الاعتذار عن الحضور</button>
                </div>
                 <p id="response-message" class="text-center font-semibold mt-4" style="display: none;"></p>
            </div>
            
            <div id="qr-code-section" class="qr-code-section">
                <div class="qr-grid">
                    <div class="qr-title-box">
                        <p class="font-bold text-lg"><?= safe_html($event_data['qr_card_title_ar'], 'بطاقة دخول') ?></p>
                        <p class="text-sm">Entry Code</p>
                    </div>
                    <div class="qr-instructions">
                        <p class="font-semibold"><?= safe_html($event_data['qr_show_code_instruction_ar'], 'يرجى إبراز الكود للدخول') ?></p>
                        <p class="text-sm">Please show code to enter</p>
                    </div>
                    <div class="qr-brand-a">
                        <p><?= safe_html($event_data['qr_brand_text_ar'], 'دعواتي') ?></p>
                    </div>
                    <div id="qrcode" class="qr-code-container"></div>
                    <div class="qr-guest-count">
                        <p class="text-sm">ضيوف</p>
                        <p class="font-bold text-xl"><?= safe_html($guest_data['guests_count'], '1') ?></p>
                        <p class="text-sm">Guest</p>
                    </div>
                     <div class="qr-website-b">
                        <p><?= safe_html($event_data['qr_website'], 'daawati.ai') ?></p>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php if (empty($error_message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allData = <?= $json_data_for_js ?>;
            const guestData = allData.guest;
            const eventData = allData.event;
            
            const confirmButton = document.getElementById('confirm-button');
            const cancelButton = document.getElementById('cancel-button');
            const qrCodeSection = document.getElementById('qr-code-section');
            const qrcodeContainer = document.getElementById('qrcode');
            const actionButtonsSection = document.getElementById('action-buttons-section');
            const responseMessageEl = document.getElementById('response-message');

            function generateQRCode(data) {
                qrcodeContainer.innerHTML = '';
                new QRCode(qrcodeContainer, { text: data, width: 150, height: 150, colorDark: "#000000", colorLight: "#ffffff" });
            }
            
            function showFinalState(status) {
                actionButtonsSection.style.display = 'none';
                if (status === 'confirmed') {
                    // Hide the main content and show the QR code section
                    const cardContent = document.querySelector('.card-content');
                    if(cardContent) cardContent.style.display = 'none'; 
                    qrCodeSection.style.display = 'block'; 
                    generateQRCode(guestData.guest_id);
                } else { // 'canceled'
                    responseMessageEl.textContent = 'تم تسجيل اعتذارك عن الحضور. شكراً لك.';
                    responseMessageEl.style.color = 'red';
                    responseMessageEl.style.display = 'block';
                }
            }

            async function sendRsvpResponse(status) {
                confirmButton.disabled = true;
                cancelButton.disabled = true;
                try {
                    const response = await fetch('api_rsvp_handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ guest_id: guestData.guest_id, status: status })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showFinalState(status);
                    } else { throw new Error(result.message); }
                } catch (error) {
                    alert('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.');
                    console.error('RSVP Error:', error);
                    confirmButton.disabled = false;
                    cancelButton.disabled = false;
                }
            }

            // Check initial status on page load
            if (guestData.status === 'confirmed' || guestData.status === 'canceled') {
                showFinalState(guestData.status);
            }

            confirmButton.addEventListener('click', () => sendRsvpResponse('confirmed'));
            cancelButton.addEventListener('click', () => sendRsvpResponse('canceled'));
        });
    </script>
    <?php endif; ?>
</body>
</html>