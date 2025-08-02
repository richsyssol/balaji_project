<?php
include 'includes/db_conn.php';

$current_date = date('Y-m-d');

$reminder_periods = [
    "1 Month" => date('Y-m-d', strtotime('+1 month', strtotime($current_date))),
    "15 Days" => date('Y-m-d', strtotime('+15 days', strtotime($current_date))),
    "10 Days" => date('Y-m-d', strtotime('+10 days', strtotime($current_date))),
    "5 Days" => date('Y-m-d', strtotime('+5 days', strtotime($current_date))),
    "2 Days" => date('Y-m-d', strtotime('+2 days', strtotime($current_date))),
    "1 Day" => date('Y-m-d', strtotime('+1 day', strtotime($current_date))),
    "Today" => $current_date
];

$sql = "SELECT r.id, e.expense_type, e.amount, 
               IFNULL(r.snooze_until, r.reminder_date) as effective_date,
               r.reminder_status
        FROM expenses_reminders r
        JOIN expenses e ON r.expense_id = e.id
        WHERE (
            (r.reminder_status = 'pending' AND (";

$conditions = [];
$param_types = '';
$params = [];

foreach ($reminder_periods as $label => $date) {
    $conditions[] = "r.reminder_date = ?";
    $params[] = $date;
    $param_types .= 's';
}

$sql .= implode(" OR ", $conditions) . ")) 
         OR 
         (r.reminder_status = 'snoozed' AND r.snooze_until <= NOW())
        )";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => $conn->error]));
}

$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    $time_period = array_search($row['effective_date'], $reminder_periods);
    $row['time_period'] = $time_period ?: "Snoozed";
    $reminders[] = $row;
}

echo "<script>var reminders = " . json_encode($reminders) . ";</script>";
?>

<!-- JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (reminders.length > 0) {
        showReminderPopup(reminders);
    }
});

function showReminderPopup(reminders) {
    if (reminders.length === 0) return;

    let existingPopup = document.querySelector(".reminder-popup");
    if (existingPopup) existingPopup.remove();

    let popup = document.createElement("div");
    popup.classList.add("reminder-popup");
    popup.style = `
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 55px;
    border: 1px solid #ccc;
    z-index: 9999;
    max-width: 600px;
    height: 42vh;
    overflow-y: auto;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
    border-radius: 10px;
    `;

    let html = `<h4>⏰ Work Reminders</h4><ul style="list-style:none; padding:0;">`;

    reminders.forEach(reminder => {
        let reminderDate = new Date(reminder.effective_date);
        let formattedDate = reminderDate.toLocaleDateString("en-GB");

        html += `
            <li id="reminder-${reminder.id}" style="margin-bottom:20px; border-bottom:1px solid #ddd; padding-bottom:10px;">
                <strong>${reminder.expense_type}</strong><br>
                Amount: ₹${parseFloat(reminder.amount).toFixed(2)}<br>
                Due Date: ${formattedDate}<br>
                Time Period: <b>${reminder.time_period}</b><br><br>

                <label>Select Snooze Time:</label><br>
                <input type="datetime-local" id="snooze-datetime-${reminder.id}" style="width:100%;"><br><br>
                <button onclick="snoozeReminderWithDate(${reminder.id})">Snooze</button>
                <button onclick="dismissReminder(${reminder.id})">Dismiss</button>
            </li>
        `;
    });

    html += `</ul>`;
    popup.innerHTML = html; 
    document.body.appendChild(popup);

    setTimeout(() => { popup.style.display = "block"; }, 2000);
}

function snoozeReminderWithDate(id) {
    const datetimeInput = document.getElementById(`snooze-datetime-${id}`);
    const datetimeValue = datetimeInput.value;

    if (!datetimeValue) {
        alert("Please select a date and time to snooze.");
        return;
    }

    fetch(`update_reminder.php?action=snooze&id=${id}&datetime=${encodeURIComponent(datetimeValue)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`reminder-${id}`).remove();
                removePopupIfEmpty();
            } else {
                alert("Failed to snooze reminder.");
            }
        });
}

function dismissReminder(id) {
    fetch(`update_reminder.php?action=dismiss&id=${id}`)
        .then(response => response.json())
        .then(() => {
            document.getElementById(`reminder-${id}`).remove();
            removePopupIfEmpty();
        });
}

function removePopupIfEmpty() {
    let popup = document.querySelector(".reminder-popup ul");
    if (!popup || popup.children.length === 0) {
        document.querySelector(".reminder-popup").remove();
    }
}
</script>
