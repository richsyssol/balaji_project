<?php
include 'session_check.php';
include 'includes/db_conn.php';

function sendWhatsAppTemplate($contact, $client_name, $templateName) {
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
    $phoneNumberId = '919422246469';

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "name" => $templateName,
            "language" => ["code" => "en"]
        ]
    ];

    return sendCurlRequest($apiUrl, $data, $apiKey, $phoneNumberId);
}

function sendWhatsAppMedia($contact, $mediaUrl, $templateName) {
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
    $phoneNumberId = '919422246469';

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "name" => $templateName,
            "language" => ["code" => "en"],
            "components" => [
                [
                    "type" => "header",
                    "parameters" => [
                        [
                            "type" => "image",
                            "image" => [
                                "link" => $mediaUrl
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
        ]
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    return $error ? ["error" => $error] : json_decode($response, true);
}

// Process Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageType = $_POST['message_type'] ?? '';
    $templateName = $_POST['template_name'] ?? '';
    $mediaUrl = $_POST['media_url'] ?? '';
    $selectedClients = $_POST['selected_clients'] ?? [];

    if (empty($selectedClients)) {
        echo "No clients selected.";
        exit;
    }

    $success = 0;
    $fail = 0;

    foreach ($selectedClients as $client) {
        list($contact, $client_name) = explode('|', $client);

        if ($messageType === 'template') {
            $res = sendWhatsAppTemplate($contact, $client_name, $templateName);
        } elseif ($messageType === 'media') {
            $res = sendWhatsAppMedia($contact, $mediaUrl, $templateName);
        }

        if (isset($res['messages'])) {
            $success++;
        } else {
            $fail++;
        }
    }

    echo "Message sent to $success client(s). Failed for $fail.";
}
?>
