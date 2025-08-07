<?php
include 'session_check.php';
include 'includes/db_conn.php';

// WhatsApp template function
function sendWhatsAppTemplate($contact, $client_name) {
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d';
    $phoneNumberId = '919422246469';

    $data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "name" => "test",
            "language" => ["code" => "en"]
        ]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
            'wanumber: ' . $phoneNumberId
        ]
    ]);
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ['error' => $error];
    }
    
    $decoded = json_decode($response, true);
    if (isset($decoded['error'])) {
        return ['error' => $decoded['error']['message']];
    }
    
    return $decoded;
}

// SMS function
function sendTestMsg($contact, $client_name) {
    $username = 'Balajimotor@999';
    $password = 'Balajimotor@999';
    $senderId = 'BMDSCH';

    $message = "May God bless you with Health, Wealth, Prosperity in your life HAPPY BIRTHDAY TO YOU! From Balaji Motor Driving School M: 9881712967 / 9881063639";
    $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$username&pass=$password&senderid=$senderId&dest_mobileno=$contact&msgtype=TXT&message=" . urlencode($message) . "&response=Y";

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
        return ['error' => $error];
    }
    curl_close($ch);
    return ['response' => $response];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedClients = $_POST['selected_clients'] ?? [];
    $action = $_POST['action'] ?? 'whatsapp';

    if (empty($selectedClients)) {
        echo "No clients selected.";
        exit;
    }

    $responses = [];
    
    foreach ($selectedClients as $client) {
        list($contact, $client_name) = explode('|', $client);
        

        if ($action === 'whatsapp') {
            $res = sendWhatsAppTemplate($contact, $client_name);
            if (isset($res['messages'])) {
                echo "WhatsApp message sent successfully to $client_name ($contact)";
            } else {
                echo "Failed to send WhatsApp: " . ($res['error'] ?? 'Unknown error');
            }
        } 
        elseif ($action === 'sms') {
            $res = sendTestMsg($contact, $client_name);
            if (isset($res['response'])) {
                echo "SMS sent successfully to $client_name ($contact)";
            } else {
                echo "Failed to send SMS: " . ($res['error'] ?? 'Unknown error');
            }
        }
    }
}
?>