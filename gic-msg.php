<?php
include 'session_check.php';
include 'includes/db_conn.php';



// ✅ 1. Define this first
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

// ✅ 2. WhatsApp template function
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

    return sendCurlRequest($apiUrl, $data, $apiKey, $phoneNumberId);
}

// ✅ 3. SMS function
function sendTestMsg($contact, $client_name)
{
    $username = 'Balajimotor@999';
    $password = 'Balajimotor@999';
    $senderId = 'BMDSCH';

    $message = "Dear Sir/Ma'am, Wishing you a very Happy Anniversary! May your day be filled with joy and cherished moments. Best regards, Balaji Motor Driving School M: 9881712967 / 9881063639";
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
        return ["error" => $error];
    }
    curl_close($ch);
    return ["response" => $response];
}

// ✅ 4. Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedClients = $_POST['selected_clients'] ?? [];
    $action = $_POST['action'] ?? 'whatsapp';

    if (empty($selectedClients)) {
        echo "No clients selected.";
        exit;
    }

    $success = 0;
    $fail = 0;
    $already = 0;

    foreach ($selectedClients as $client) {
        list($contact, $client_name) = explode('|', $client);

        // ✅ Check form_status and message_sent in DB
        $stmt = $conn->prepare("SELECT form_status, message_sent FROM gic_entries WHERE contact = ?");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $clientRow = $result->fetch_assoc();

        if (!$clientRow) continue;

        if ($clientRow['message_sent'] == 1) {
            $already++;
            continue;
        }

        if (strtoupper($clientRow['form_status']) !== "COMPLETE") {
            $fail++;
            continue;
        }

        if ($action === 'whatsapp') {
            $res = sendWhatsAppTemplate($contact, $client_name);
            $status = isset($res['messages']) ? 1 : 0;

        } elseif ($action === 'sms') {
            $res = sendTestMsg($contact, $client_name);
            $status = isset($res['response']) ? 1 : 0;
        }

        if ($status) {
            // ✅ Mark as sent in DB
            $updateStmt = $conn->prepare("UPDATE gic_entries SET message_sent = 1 WHERE contact = ?");
            $updateStmt->bind_param("s", $contact);
            $updateStmt->execute();
            $success++;
        } else {
            $fail++;
        }
    }

    echo "✅ Sent to $success client(s). ❌ Failed for $fail. 🔁 Already sent: $already.";
}



?>





