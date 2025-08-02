<?php
if (isset($_FILES['pdf']) && isset($_POST['contact'])) {
    $targetDir = 'pdfs/';
    $fileName = basename($_FILES['pdf']['name']);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetFile)) {
        $publicUrl = 'https://balaji.demovoting.com/pdfs/' . $fileName;
        $contact = $_POST['contact']; // use from JS/PHP bridge
        
        sendWhatsAppPDF($contact, $publicUrl, $fileName);
    } else {
        echo "File upload failed.";
    }
}

function sendWhatsAppPDF($contact, $publicUrl, $fileName) {
    $apiUrl = 'https://partners.pinbot.ai/v1/messages';
    $apiKey = 'fbae4610-2023-11f0-ad4f-92672d2d0c2d'; // your API key
    $phoneNumberId = '919422246469'; // your phone number ID

    $data = [
        "messaging_product" => "whatsapp",
        "to" => $contact,
        "type" => "template",
        "template" => [
            "name" => "expiry_letter", 
            "language" => [
                "code" => "en"
            ],
            "components" => [
                [
                    "type" => "header",
                    "parameters" => [
                        [
                            "type" => "document",
                            "document" => [
                                "link" => $publicUrl,
                                "filename" => $fileName
                            ]
                        ]
                    ]
                ],
            ]
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
            'apikey:' . $apiKey,
            'wanumber:' . $phoneNumberId
        ]
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo "cURL Error: " . curl_error($curl);
    } else {
        echo "Response: " . $response;
    }
    curl_close($curl);
}
?>
