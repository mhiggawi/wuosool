<?php
// rsvp.php - v3 (Final Fix)

// We will hide deprecated warnings for a cleaner look, but keep other errors visible.
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

// Helper function to safely display data and avoid errors with null values
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        /* Restoring original full-page styles */
        body { font-family: 'Cairo', 'Inter', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .container { max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 20px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); overflow: hidden; direction: rtl; text-align: right; }
        .container[lang="en"] { direction: ltr; text-align: left; }
        .header { background-color: #f9f9f9; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; }
        .language-toggle { background-color: #e0e0e0; color: #333; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .invitation-card {
            background-image: url('<?= $event_data && !empty($event_data['background_image_url']) ? safe_html($event_data['background_image_url']) : 'https://placehold.co/600x400/F8F8F8/888888?text=Event+Background' ?>');
            background-size: cover; background-position: center; padding: 30px; color: #333; position: relative;
        }
        .invitation-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(255,255,255,0.7) 0%, rgba(255,255,255,0.9) 100%); z-index: 1; }
        .invitation-content { position: relative; z-index: 2; }
        .button-section { padding: 20px; border-top: 1px solid #eee; display: flex; flex-direction: column; gap: 15px; }
        .qr-code-section { background-color: #ffffff; padding: 20px; border-top: 1px solid #eee; display: none; flex-direction: column; align-items: center; text-align: center; }
        .qr-code-container { padding: 15px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin: 20px auto; }
        .footer { padding: 15px 20px; background-color: #f9f9f9; border-top: 1px solid #eee; text-align: center; font-size: 0.9em; color: #777; }
        .error-container { text-align: center; padding: 40px; }
        .error-icon { font-size: 4rem; color: #ef4444; margin-bottom: 1rem; }
        .error-message { font-size: 1.25rem; font-weight: 600; color: #374151; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error_message)): ?>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php else: ?>
            <!-- Header Section -->
            <div class="header">
                <span class="font-bold text-lg text-gray-800"><?= safe_html($event_data['qr_brand_text_ar'], 'دعواتي') ?></span>
                <button id="language-toggle" class="language-toggle">English</button>
            </div>
            <!-- Invitation Card Section -->
            <div class="invitation-card">
                <div class="invitation-content text-center">
                    <p class="text-xl font-bold mb-4">بارك الله لهما وبارك عليهما وجمع بينهما بخير</p>
                    <p class="text-2xl font-semibold text-pink-600 my-2"><?= safe_html($event_data['bride_name_ar'], 'العروس') ?></p>
                    <p class="text-lg mt-4">و</p>
                    <p class="text-2xl font-semibold text-blue-600 my-2"><?= safe_html($event_data['groom_name_ar'], 'العريس') ?></p>
                    <p class="text-lg text-center my-6">
                        وذلك بمناسبة حفل زفافهما المبارك<br>
                        <?= nl2br(safe_html($event_data['event_date_ar'])) ?><br>
                        في <?= safe_html($event_data['venue_ar']) ?>
                    </p>
                    <hr class="my-6 border-gray-300">
                    <p class="text-lg font-semibold text-gray-800 mb-2">
                        السيد/ة <span id="guest-name"><?= safe_html($guest_data['name_ar'], 'الضيف الكريم') ?></span>،
                    </p>
                    <div id="guest-details-display" class="mt-4 text-sm text-gray-700">
                        <p>عدد الضيوف: <span><?= safe_html($guest_data['guests_count'], '1') ?></span></p>
                        <p>رقم الطاولة: <span><?= safe_html($guest_data['table_number'], 'غير محدد') ?></span></p>
                    </div>
                </div>
            </div>
            <!-- Location Section -->
            <div class="p-5 bg-white border-t border-b border-gray-200">
                <a href="<?= safe_html($event_data['google_maps_link'], '#') ?>" target="_blank" class="flex items-center justify-between text-gray-700 hover:text-blue-600">
                    <span class="text-md font-medium"><?= safe_html($event_data['venue_ar'], 'مكان الحفل') ?></span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                </a>
            </div>
            <!-- Buttons and QR Code -->
            <div id="action-buttons" class="button-section">
                <button id="confirm-button" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl">تأكيد الحضور</button>
                <button id="cancel-button" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl">إلغاء الحضور</button>
            </div>
            <div id="qr-code-section" class="qr-code-section">
                <p class="font-semibold mb-2"><?= safe_html($event_data['qr_card_title_ar']) ?></p>
                <p class="mb-4"><?= safe_html($event_data['qr_show_code_instruction_ar']) ?></p>
                <div id="qrcode" class="qr-code-container"></div>
            </div>
            <!-- Footer -->
            <div class="footer">
                <span><?= safe_html($event_data['qr_brand_text_ar'], 'دعواتي') ?></span>
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
            const actionButtonsSection = document.getElementById('action-buttons');

            function generateQRCode(data) {
                qrcodeContainer.innerHTML = '';
                new QRCode(qrcodeContainer, { text: data, width: 200, height: 200 });
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
                        actionButtonsSection.style.display = 'none';
                        if (status === 'confirmed') {
                            qrCodeSection.style.display = 'flex';
                            generateQRCode(guestData.guest_id);
                            alert("تم تأكيد حضورك بنجاح!");
                        } else {
                            alert("تم تسجيل إلغاء حضورك.");
                        }
                    } else { throw new Error(result.message); }
                } catch (error) {
                    alert('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.');
                    confirmButton.disabled = false;
                    cancelButton.disabled = false;
                }
            }

            if (guestData.status === 'confirmed' || guestData.status === 'canceled') {
                actionButtonsSection.style.display = 'none';
                if (guestData.status === 'confirmed') {
                    qrCodeSection.style.display = 'flex';
                    generateQRCode(guestData.guest_id);
                }
            }

            confirmButton.addEventListener('click', () => sendRsvpResponse('confirmed'));
            cancelButton.addEventListener('click', () => sendRsvpResponse('canceled'));
        });
    </script>
    <?php endif; ?>
</body>
</html>
