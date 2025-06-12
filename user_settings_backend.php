<?php
// database connection
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$house_details = '';
$barangay = '';
$city = '';
$region = '';
$contact = 'No contact found';

if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name']; // Set user_name from session
} else {
    $user_name = 'Guest'; // Fallback if no user is logged in
}

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $stmt = $conn->prepare("SELECT house_details, barangay, city, region, contact FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($house_details, $barangay, $city, $region, $contact);
    $stmt->fetch();
    $stmt->close();

    // Create complete address for backward compatibility
    if (!empty($house_details) && !empty($barangay) && !empty($city) && !empty($region)) {
        $address = $house_details . ', ' . $barangay . ', ' . $city . ', ' . $region;
    } else {
        $address = 'No address found';
    }
}

$conn->close();
?>