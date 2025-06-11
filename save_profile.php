<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $house_number = isset($_POST['house_number']) ? trim($_POST['house_number']) : '';
    $street_name = isset($_POST['street_name']) ? trim($_POST['street_name']) : '';
    $subdivision = isset($_POST['subdivision']) ? trim($_POST['subdivision']) : '';
    
    // Concatenate house details with commas
    $house_details_array = array();
    if (!empty($house_number)) $house_details_array[] = $house_number;
    if (!empty($street_name)) $house_details_array[] = $street_name;
    if (!empty($subdivision)) $house_details_array[] = $subdivision;
    $house_details = implode(', ', $house_details_array);

    $barangay = isset($_POST['barangay']) ? trim($_POST['barangay']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $region = isset($_POST['region']) ? trim($_POST['region']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';

    // Debug: Print received values
    error_log("Received house_number: " . $house_number);
    error_log("Received street_name: " . $street_name);
    error_log("Received subdivision: " . $subdivision);
    error_log("Concatenated house_details: " . $house_details);
    error_log("Received barangay: " . $barangay);
    error_log("Received city: " . $city);
    error_log("Received region: " . $region);
    error_log("Received contact: " . $contact);

    // Prepare update query
    $sql = "UPDATE users SET ";
    $params = array();
    $types = "";
    $updates = array();

    // Add address fields to update if provided
    if (!empty($house_details)) {
        $updates[] = "house_details = ?";
        $params[] = $house_details;
        $types .= "s";
    }

    if (!empty($barangay)) {
        $updates[] = "barangay = ?";
        $params[] = $barangay;
        $types .= "s";
    }

    if (!empty($city)) {
        $updates[] = "city = ?";
        $params[] = $city;
        $types .= "s";
    }

    if (!empty($region)) {
        $updates[] = "region = ?";
        $params[] = $region;
        $types .= "s";
    }

    // Add contact to update if provided
    if (!empty($contact)) {
        $updates[] = "contact = ?";
        $params[] = $contact;
        $types .= "s";
    }

    // Only proceed if we have updates
    if (!empty($updates)) {
        $sql .= implode(", ", $updates) . " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";

        // Debug: Print SQL and parameters
        error_log("SQL Query: " . $sql);
        error_log("Parameters: " . print_r($params, true));
        error_log("Types: " . $types);

        $stmt = $conn->prepare($sql);
        
        if ($stmt && $stmt->bind_param($types, ...$params) && $stmt->execute()) {
            // Update session variables
            if (!empty($house_details)) $_SESSION['house_details'] = $house_details;
            if (!empty($barangay)) $_SESSION['barangay'] = $barangay;
            if (!empty($city)) $_SESSION['city'] = $city;
            if (!empty($region)) $_SESSION['region'] = $region;
            if (!empty($contact)) $_SESSION['contact'] = $contact;
            
            header('Location: user-settings.php?saved=1');
            exit;
        } else {
            // Debug: Print any SQL errors
            error_log("SQL Error: " . $conn->error);
        }
    } else {
        error_log("No updates to perform");
    }
    
    // If we get here, either there were no updates or something went wrong
    header('Location: user-settings.php?error=1');
    exit;
}

// If not POST request, redirect to settings page
header('Location: user-settings.php');
exit;
