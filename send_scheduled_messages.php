<?php
// Database configuration
include 'includes/db_conn.php';


// SMS Gateway credentials
$sms_username = 'Balajimotor@999';
$sms_password = 'Balajimotor@999';
$sms_senderid = 'BMDSCH';


// Function to send birthday SMS
function sendBirthdaySMS($phone, $name) {
    global $sms_username, $sms_password, $sms_senderid;
    
    // Marathi birthday message
    $message = "आपणास वाढदिवसाच्या हार्दिक शुभेच्छा..! आपल्या उज्वल भविष्यासाठी आजच SIP ची सुरुवात करा, SIP सुरू करण्यासाठी संपर्क 9277656565 उज्ज्वल / भाऊराव पिंगळे बालाजी मोटर ड्रायव्हिंग स्कूल ,पिंपळगाव बसवंत. 9881063639 #MF investment Subject to Market Risk.";
    
    $encoded_message = urlencode($message);
    $url = "https://www.smsjust.com/sms/user/urlsms.php?username=$sms_username&pass=$sms_password&senderid=$sms_senderid&dest_mobileno=$phone&msgtype=UNI&message=$encoded_message&response=Y";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response !== false;
}

// Get today's month and day (ignore year)
$today = date('m-d');
$current_year = date('Y');

// Find clients with birthdays today who haven't received SMS this year
$sql = "SELECT * FROM demo_clients 
        WHERE DATE_FORMAT(birthdate, '%m-%d') = '$today'
        AND (last_birthday_sent IS NULL OR last_birthday_sent < '$current_year')";
$result = $conn->query($sql);

while ($client = $result->fetch_assoc()) {
    $success = sendBirthdaySMS($client['phone'], $client['name']);
    
    if ($success) {
        // Update last sent year
        $update = "UPDATE demo_clients SET last_birthday_sent = '$current_year' WHERE id = " . $client['id'];
        $conn->query($update);
        
        // Log successful send
        $log = "INSERT INTO sms_log (client_id, phone, message, status) 
                VALUES (" . $client['id'] . ", '" . $client['phone'] . "', 
                'Birthday wishes sent', 'sent')";
        $conn->query($log);
        
        echo "Sent birthday SMS to " . $client['name'] . " (" . $client['phone'] . ")\n";
    } else {
        // Log failure
        $log = "INSERT INTO sms_log (client_id, phone, message, status) 
                VALUES (" . $client['id'] . ", '" . $client['phone'] . "', 
                'Failed to send birthday SMS', 'failed')";
        $conn->query($log);
        
        echo "Failed to send SMS to " . $client['name'] . "\n";
    }
}

$conn->close();
?>