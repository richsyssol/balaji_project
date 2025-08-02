<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactsRaw = $_POST['contacts'] ?? '';
    $template_name = $_POST['template_name'] ?? '';
    $sms_message = $_POST['sms_message'] ?? '';
    $method = $_POST['method'] ?? 'whatsapp';
    $message_type = $_POST['message_type'] ?? 'template';
    $media_url = $_POST['media_url'] ?? '';
    $language = $_POST['language'] ?? 'en'; // Default to English

    // WhatsApp sending functions
    function sendWhatsAppTemplate($contact, $template_name, $language) {
        $apiUrl = 'https://partners.pinbot.ai/v1/messages';
        $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
        $phoneNumberId = '919422246469';

        // Validate template name
        $template_name = trim($template_name);
        if (empty($template_name)) {
            return ["error" => "Template name is required"];
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $contact,
            "type" => "template",
            "template" => [
                "name" => $template_name,
                "language" => ["code" => $language] // Dynamic language code
            ]
        ];

        error_log("WhatsApp API Request: " . json_encode($data));
        return sendCurlRequest($apiUrl, $data, $apiKey, $phoneNumberId);
    }

    function sendWhatsAppMedia($contact, $media_url, $template_name, $language) {
        $apiUrl = 'https://partners.pinbot.ai/v1/messages';
        $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
        $phoneNumberId = '919422246469';
        
        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $contact,
            "type" => "template",
            "template" => [
                "language" => ["code" => $language], // Dynamic language code
                "name" => $template_name,
                "components" => [
                    [
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "image",
                                "image" => [
                                    "link" => $media_url
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return sendCurlRequest($apiUrl, $data, $apiKey, $phoneNumberId);
    }


    function sendCurlRequest($url, $data, $apiKey, $wanumber) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey:' . $apiKey,
                'wanumber:' . $wanumber
            ],
            CURLOPT_VERBOSE => true // Enable verbose logging
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        // return $error ? ["error" => $error] : json_decode($response, true);
        return $response;
    }

    // SMS sending function with language support
    function sendTestMsg($contact, $message, $language) {
        $username = 'Balajimotor@999';
        $password = 'Balajimotor@999';
        $senderId = 'BMDSCH';

        // URL encode the message (important for Marathi messages)
        $encoded_message = urlencode($message);
        
        if ($language == 'mr') {
            $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=UNI&message=$encoded_message&response=Y";
        }
        else{
            $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=TXT&message=$encoded_message&response=Y";
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ["error" => $error];
        }
        curl_close($ch);
        return ["response" => $response];
    }

    

    function sendMessagesToMultipleContacts($contactsRaw, $method, $message_type, $template_name, $media_url, $sms_message, $language) {
        $contacts = preg_split('/[\s,]+/', $contactsRaw, -1, PREG_SPLIT_NO_EMPTY);
        $results = [];

        foreach ($contacts as $contact) {
            $contact = trim($contact);
            if (!preg_match('/^\d{10,15}$/', $contact)) {
                $results[$contact] = ['error' => 'Invalid phone number format'];
                continue;
            }

            if ($method === 'whatsapp') {
                if ($message_type === 'media') {
                    $results[$contact] = sendWhatsAppMedia($contact, $media_url, $template_name, $language);
                } else {
                   
                    $results[$contact] = sendWhatsAppTemplate($contact, $template_name, $language);
                    

                }
            } elseif ($method === 'sms') {
                $results[$contact] = sendTestMsg($contact, $sms_message, $language);
            } else {
                $results[$contact] = ['error' => 'Invalid method'];
            }
        }

        return $results;
    }

    $results = sendMessagesToMultipleContacts(
        $contactsRaw, 
        $method, 
        $message_type, 
        $template_name, 
        $media_url, 
        $sms_message, 
        $language
    );
}
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    
    <div class="container data-table p-5">
        <div class="ps-5">
            <h1>Send Messages </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Send Messages</li>
                </ol>
            </nav>
        </div>

        <div class="bg-white con-tbl p-5">
            <?php if (!empty($results)): ?>
                <div class="mt-4">
                    <?php foreach ($results as $contact => $result): ?>
                        <?php if (isset($result['error'])): ?>
                            <div class="alert alert-danger">
                                <strong><?= htmlspecialchars($contact) ?>:</strong> <?= htmlspecialchars($result['error']) ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <strong><?= htmlspecialchars($contact) ?>:</strong> Message sent successfully.
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php print_r($results) ?>
            <h2>Send WhatsApp or SMS Message</h2>
            <form method="POST">
                <div class="row">
                    <div class="col-md-12 mb-3 field">
                        <label for="contacts" class="form-label">Paste Contact Numbers (comma, space or newline separated):</label>
                        <textarea class="form-control" name="contacts" id="contacts" rows="5" required><?= htmlspecialchars($_POST['contacts'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6 mb-4 field">
                        <label for="method" class="form-label">Choose Method:</label>
                        <select class="form-select" name="method" id="method" required>
                            <option value="whatsapp" <?= ($_POST['method'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="sms" <?= ($_POST['method'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4 field">
                        <label for="language" class="form-label">Language:</label>
                        <select class="form-select" name="language" id="language" required>
                            <option value="en" <?= ($_POST['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="mr" <?= ($_POST['language'] ?? '') === 'mr' ? 'selected' : '' ?>>Marathi</option>
                        </select>
                    </div>

                    <!-- WhatsApp specific fields (shown when method is whatsapp) -->
                    <div id="whatsapp_fields" style="display: <?= ($_POST['method'] ?? 'whatsapp') === 'whatsapp' ? 'block' : 'none' ?>;">
                        <div class="col-md-12 mb-3 field">
                            <label for="message_type">Message Type:</label>
                            <select name="message_type" id="message_type" class="form-control" required>
                                <option value="template" <?= ($_POST['message_type'] ?? '') === 'template' ? 'selected' : '' ?>>Text Template</option>
                                <option value="media" <?= ($_POST['message_type'] ?? '') === 'media' ? 'selected' : '' ?>>Media Template</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3 field" id="template_name_group">
                            <label for="template_name" class="form-label">WhatsApp Template Name:</label>
                            <input type="text" class="form-control" name="template_name" id="template_name" value="<?= htmlspecialchars($_POST['template_name'] ?? '') ?>" placeholder="e.g., anniversary_msg">
                        </div>

                        <div class="col-md-12 mb-3 field" id="media_url_group" style="display: none;">
                            <label for="media_url" class="form-label">Media URL:</label>
                            <input type="url" class="form-control" name="media_url" id="media_url" value="<?= htmlspecialchars($_POST['media_url'] ?? '') ?>" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>

                    <!-- SMS specific fields (shown when method is sms) -->
                    <div id="sms_fields" style="display: <?= ($_POST['method'] ?? 'whatsapp') === 'sms' ? 'block' : 'none' ?>;">
                        <div class="col-md-12 mb-3 field">
                            <label for="sms_message" class="form-label">SMS Message:</label>
                            <textarea class="form-control" name="sms_message" id="sms_message" rows="4" placeholder="Type your SMS message here"><?= htmlspecialchars($_POST['sms_message'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                        <a href="messages" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between WhatsApp and SMS fields
    const methodSelect = document.getElementById('method');
    const whatsappFields = document.getElementById('whatsapp_fields');
    const smsFields = document.getElementById('sms_fields');
    
    methodSelect.addEventListener('change', function() {
        if (this.value === 'whatsapp') {
            whatsappFields.style.display = 'block';
            smsFields.style.display = 'none';
        } else {
            whatsappFields.style.display = 'none';
            smsFields.style.display = 'block';
        }
    });

    // Toggle between template types for WhatsApp
    const messageTypeSelect = document.getElementById('message_type');
    const templateNameGroup = document.getElementById('template_name_group');
    const mediaUrlGroup = document.getElementById('media_url_group');
    
    messageTypeSelect.addEventListener('change', function() {
        if (this.value === 'media') {
            templateNameGroup.style.display = 'block';
            mediaUrlGroup.style.display = 'block';
        } else {
            templateNameGroup.style.display = 'block';
            mediaUrlGroup.style.display = 'none';
        }
    });

    // Initialize visibility based on current selections
    if (messageTypeSelect.value === 'media') {
        templateNameGroup.style.display = 'block';
        mediaUrlGroup.style.display = 'block';
    } else {
        templateNameGroup.style.display = 'block';
        mediaUrlGroup.style.display = 'none';
    }
});
</script>