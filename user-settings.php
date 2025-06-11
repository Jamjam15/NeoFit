<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

$user_email = $_SESSION['email'];
$user_name = '';  // Fetch this from your DB or session as required
$address = '';
$contact = '';

// Always load values for DISPLAY
include 'user_settings_backend.php'; // This should set: $user_name, $address, $contact

// If the user has just saved the profile, clear the form fields for new input
if (isset($_GET['saved'])) {
    $address_input = '';
    $contact_input = '';
} else {
    $address_input = $address;
    $contact_input = $contact;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - User Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            text-decoration: none;
            letter-spacing: 1px;
        }

        /* Main Content Styles */
        .main-content {
            max-width: 800px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        .settings-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: block;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .welcome-text {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .user-email {
            color: #666;
            font-size: 16px;
        }

        .settings-section {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input[type="text"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 12px 35px 12px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus {
            border-color: #4a90e2;
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 45px;
            transform: none;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            padding: 5px;
            z-index: 2;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            display: flex;
            align-items: center;
            height: 20px;
        }

        .password-toggle:hover {
            color: #333;
        }

        .password-toggle i {
            font-size: 14px;
            font-style: normal;
        }

        .btn {
            background-color: #4a90e2;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #357abd;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .current-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .current-info p {
            margin: 5px 0;
            color: #666;
        }

        .current-info strong {
            color: #333;
        }

        .logout-section {
            text-align: center;
            margin-top: 40px;
        }

        /* Success Message Styles */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        /* Error Message Styles */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        /* Add password requirements styles */
        .password-requirements {
            display: none;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding: 10px;
            border: 1px solid #eaeaea;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .requirement {
            margin: 3px 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .requirement.valid {
            color: #28a745;
        }

        .requirement.invalid {
            color: #dc3545;
        }

        .requirement::before {
            content: '✕';
            color: #dc3545;
        }

        .requirement.valid::before {
            content: '✓';
            color: #28a745;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-top: 80px;
            }

            .settings-section {
                padding: 20px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Add NeoCreds Wallet styles */
        .wallet-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .balance-display {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .balance-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .balance-amount {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        /* Add Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .close-modal {
            font-size: 24px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        .preset-amounts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .amount-btn {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #fff;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.1s ease;
        }

        .amount-btn:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
            color: #007bff;
        }

        .amount-btn:active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
            transform: scale(0.98);
        }

        .custom-amount {
            margin-bottom: 20px;
        }

        .custom-amount label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .custom-amount input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            text-align: right;
        }

        .custom-amount input::-webkit-inner-spin-button,
        .custom-amount input::-webkit-outer-spin-button {
            opacity: 1;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
        }

        .btn-secondary {
            background-color: #4a90e2;
            color: #fff;
            min-width: 200px;
        }

        .btn-secondary:hover {
            background-color: #357abd;
        }

        /* Add pending requests styles */
        .pending-requests {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .pending-amount {
            font-size: 18px;
            color: #ffa500;
            font-weight: bold;
            display: block;
        }

        .transaction-history {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .transaction-history h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
        }

        .transaction-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .transaction-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-details {
            flex-grow: 1;
        }

        .transaction-amount {
            font-weight: bold;
        }

        .transaction-date {
            font-size: 12px;
            color: #666;
        }

        .transaction-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }

        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-denied {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Add styles for transaction details */
        .transaction-processor,
        .transaction-notes {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }

        .transaction-notes {
            font-style: italic;
        }

        .pending-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #eee;
        }

        .transaction-list::-webkit-scrollbar {
            width: 8px;
        }

        .transaction-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .transaction-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .transaction-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .wallet-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .close-modal {
            font-size: 24px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }

        /* Transaction List Styles */
        .transaction-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .transaction-item:hover {
            transform: translateX(5px);
        }

        .transaction-details {
            flex-grow: 1;
        }

        .transaction-amount {
            font-weight: bold;
            font-size: 16px;
            color: #2ecc71;
        }

        .transaction-date {
            font-size: 12px;
            color: #666;
        }

        .transaction-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-denied {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-history {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-history i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .wallet-actions {
                flex-direction: column;
            }

            .wallet-actions button {
                width: 100%;
            }
        }

        /* Address Form Styles */
        .address-form {
            display: grid;
            gap: 20px;
        }

        .address-group {
            display: grid;
            gap: 10px;
        }

        .address-group select,
        .address-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
            background-color: white;
        }

        .address-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }

        .address-group select:focus,
        .address-group input:focus {
            border-color: #4a90e2;
            outline: none;
        }

        .street-address {
            display: grid;
            gap: 15px;
        }

        .contact-group {
            margin-top: 10px;
        }

        .address-help {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="landing_page.php" class="logo">NEOFIT</a>
        </div>
    </header>

    <main class="main-content">
        <div class="settings-header">
            <img src="profile.jpg" alt="Profile Picture" class="user-avatar">
            <h1 class="welcome-text">Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
            <p class="user-email"><?php echo htmlspecialchars($user_email); ?></p>
        </div>

        <!-- Success Message -->
        <div class="success-message" id="successMessage">
            Changes saved successfully!
        </div>

        <!-- Error Message -->
        <div class="error-message" id="errorMessage">
            An error occurred. Please try again.
        </div>

        <!-- Profile Information Section -->
        <div class="settings-section">
            <h2 class="section-title">Profile Information</h2>
            <form action="save_profile.php" method="POST" autocomplete="off" id="profileForm">
                <div class="address-form">
                    <div class="address-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" <?php echo empty($region) ? 'required' : ''; ?>>
                            <option value="" disabled selected>Select Region</option>
                            <option value="National Capital Region">National Capital Region (NCR)</option>
                            <option value="Cordillera Administrative Region">Cordillera Administrative Region (CAR)</option>
                            <option value="Ilocos Region">Ilocos Region (Region I)</option>
                            <option value="Cagayan Valley">Cagayan Valley (Region II)</option>
                            <option value="Central Luzon">Central Luzon (Region III)</option>
                            <option value="CALABARZON">CALABARZON (Region IV-A)</option>
                            <option value="MIMAROPA">MIMAROPA (Region IV-B)</option>
                            <option value="Bicol Region">Bicol Region (Region V)</option>
                            <option value="Western Visayas">Western Visayas (Region VI)</option>
                            <option value="Central Visayas">Central Visayas (Region VII)</option>
                            <option value="Eastern Visayas">Eastern Visayas (Region VIII)</option>
                            <option value="Zamboanga Peninsula">Zamboanga Peninsula (Region IX)</option>
                            <option value="Northern Mindanao">Northern Mindanao (Region X)</option>
                            <option value="Davao Region">Davao Region (Region XI)</option>
                            <option value="SOCCSKSARGEN">SOCCSKSARGEN (Region XII)</option>
                            <option value="Caraga">Caraga (Region XIII)</option>
                            <option value="Bangsamoro">Bangsamoro (BARMM)</option>
                        </select>
                    </div>

                    <div class="address-group">
                        <label for="city">City/Municipality</label>
                        <input type="text" id="city" name="city" 
                               placeholder="e.g., Makati City, Quezon City" 
                               <?php echo empty($region) ? 'required' : ''; ?>>
                    </div>

                    <div class="address-group">
                        <label for="barangay">Barangay</label>
                        <input type="text" id="barangay" name="barangay" 
                               placeholder="e.g., San Lorenzo" 
                               <?php echo empty($region) ? 'required' : ''; ?>>
                    </div>

                    <div class="address-group">
                        <label for="house_details">House Details</label>
                        <div class="street-address">
                            <input type="text" id="house_number" name="house_number" 
                                   placeholder="House/Unit Number" 
                                   <?php echo empty($region) ? 'required' : ''; ?>>
                            <input type="text" id="street_name" name="street_name" 
                                   placeholder="Street Name" 
                                   <?php echo empty($region) ? 'required' : ''; ?>>
                            <input type="text" id="subdivision" name="subdivision" 
                                   placeholder="Subdivision/Village/Building (Optional)">
                        </div>
                    </div>

                    <input type="hidden" id="house_details" name="house_details">

                    <div class="address-group contact-group">
                        <label for="contact">Contact Number</label>
                        <input type="tel" id="contact" name="contact" 
                               placeholder="Enter your contact number (e.g., 09123456789)" 
                               pattern="[0-9]{10,11}" 
                               title="Please enter a valid contact number (For phone numbers start from '09' only)"
                               <?php echo empty($contact) ? 'required' : ''; ?>>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn">Save Changes</button>
                    </div>

                    <div class="current-info" style="margin-top: 20px;">
                        <?php if (!empty($house_details) || !empty($barangay) || !empty($city) || !empty($region)): ?>
                            <p><strong>Current Address:</strong></p>
                            <p><?php 
                                $full_address = '';
                                if (!empty($house_details) && !empty($barangay) && !empty($city) && !empty($region)) {
                                    $full_address = $house_details . ', ' . $barangay . ', ' . $city . ', ' . $region;
                                }
                                echo htmlspecialchars($full_address);
                            ?></p>
                        <?php else: ?>
                            <p><strong>Current Address:</strong> Not set</p>
                        <?php endif; ?>
                        <p><strong>Current Contact:</strong> <?php echo htmlspecialchars($contact) ?: 'Not set'; ?></p>
                    </div>
                </div>
            </form>
        </div>

        <!-- NeoCreds Wallet Section -->
        <div class="settings-section">
            <h2 class="section-title">NeoCreds Wallet</h2>
            <div class="wallet-container">
                <div class="balance-display">
                    <span class="balance-label">Current Balance</span>
                    <span class="balance-amount">₱0.00</span>
                </div>
                <div class="wallet-actions">
                    <button type="button" class="btn btn-primary" id="addCreditsBtn">Add Credits</button>
                    <button type="button" class="btn btn-secondary" id="viewHistoryBtn">View History</button>
                </div>
            </div>
        </div>

        <!-- Add Credits Modal -->
        <div class="modal" id="addCreditsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add NeoCreds</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="preset-amounts">
                        <button class="amount-btn" data-amount="50">₱50</button>
                        <button class="amount-btn" data-amount="100">₱100</button>
                        <button class="amount-btn" data-amount="200">₱200</button>
                        <button class="amount-btn" data-amount="500">₱500</button>
                        <button class="amount-btn" data-amount="1000">₱1,000</button>
                        <button class="amount-btn" data-amount="1500">₱1,500</button>
                    </div>
                    <div class="custom-amount">
                        <label for="customAmount">Amount (₱)</label>
                        <input type="text" id="customAmount" placeholder="0.00" onkeypress="return isNumberOrPeriod(event)">
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" id="requestCredits">Request Credits</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History Modal -->
        <div class="modal" id="historyModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Transaction History</h2>
                    <span class="close-modal" data-modal="historyModal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="transaction-list" id="transactionList">
                        <!-- Transactions will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Section -->
        <div class="settings-section">
            <h2 class="section-title">Security Settings</h2>
            <form action="change_password.php" method="POST" id="passwordForm">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                    <span class="password-toggle" onmousedown="showPassword('current_password')" onmouseup="hidePassword('current_password')" onmouseleave="hidePassword('current_password')"><i class="fa-solid fa-eye-slash"></i></span>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <span class="password-toggle" onmousedown="showPassword('new_password')" onmouseup="hidePassword('new_password')" onmouseleave="hidePassword('new_password')"><i class="fa-solid fa-eye-slash"></i></span>
                    <div class="password-requirements" id="password-requirements">
                        <div class="requirement" id="length">At least 8 characters long</div>
                        <div class="requirement" id="letter">Contains at least one letter</div>
                        <div class="requirement" id="number">Contains at least one number</div>
                        <div class="requirement" id="special">Contains at least one special character</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="password-toggle" onmousedown="showPassword('confirm_password')" onmouseup="hidePassword('confirm_password')" onmouseleave="hidePassword('confirm_password')"><i class="fa-solid fa-eye-slash"></i></span>
                </div>

                <button type="submit" class="btn">Update Password</button>
            </form>
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
            <form action="logout.php" method="POST">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // URL parameters for success/error messages
            const urlParams = new URLSearchParams(window.location.search);
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');

            if (urlParams.has('saved')) {
                successMessage.style.display = 'block';
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            }

            if (urlParams.has('error')) {
                errorMessage.style.display = 'block';
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            }

            // Contact number validation
            const contactInput = document.querySelector('input[name="contact"]');
            if (contactInput) {
                contactInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 11) {
                        this.value = this.value.slice(0, 11);
                    }
                });
            }

            // Password confirmation validation
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;

                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New password and confirmation password do not match!');
                    }
                });
            }

            // Add Credits Modal Functionality
            const modal = document.getElementById('addCreditsModal');
            const addCreditsBtn = document.getElementById('addCreditsBtn');
            const closeModal = document.querySelector('.close-modal');
            const amountBtns = document.querySelectorAll('.amount-btn');
            const customAmountInput = document.getElementById('customAmount');
            const requestCreditsBtn = document.getElementById('requestCredits');

            // Open modal
            if (addCreditsBtn) {
                addCreditsBtn.onclick = function() {
                    modal.style.display = 'block';
                };
            }

            // Close modal
            if (closeModal) {
                closeModal.onclick = function() {
                    modal.style.display = 'none';
                };
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };

            // Handle preset amount buttons
            amountBtns.forEach(btn => {
                btn.onclick = function() {
                    const amount = parseFloat(btn.dataset.amount);
                    const currentAmount = parseFloat(customAmountInput.value) || 0;
                    const newAmount = (currentAmount + amount).toFixed(2);
                    customAmountInput.value = newAmount;
                };
            });

            // Function to allow only numbers and period
            function isNumberOrPeriod(evt) {
                const charCode = (evt.which) ? evt.which : evt.keyCode;
                const inputValue = evt.target.value;

                // Allow only one period
                if (charCode === 46) {
                    if (inputValue.indexOf('.') === -1) {
                        return true;
                    }
                    return false;
                }
                
                // Allow only numbers
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                }
                
                return true;
            }

            // Format custom input to always show 2 decimal places
            if (customAmountInput) {
                customAmountInput.addEventListener('input', function(e) {
                    // Remove any characters that aren't numbers or period
                    this.value = this.value.replace(/[^\d.]/g, '');
                    
                    // Ensure only one decimal point
                    const parts = this.value.split('.');
                    if (parts.length > 2) {
                        this.value = parts[0] + '.' + parts.slice(1).join('');
                    }

                    // Limit to 2 decimal places if there's a decimal point
                    if (parts[1] && parts[1].length > 2) {
                        this.value = parts[0] + '.' + parts[1].substring(0, 2);
                    }
                });

                customAmountInput.addEventListener('blur', function() {
                    if (this.value) {
                        // Ensure proper decimal format when leaving the field
                        const num = parseFloat(this.value);
                        if (!isNaN(num)) {
                            this.value = num.toFixed(2);
                        } else {
                            this.value = '';
                        }
                    }
                });

                // Prevent paste of invalid characters
                customAmountInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                    if (/^[\d.]*$/.test(text)) {
                        const parts = text.split('.');
                        if (parts.length <= 2) {
                            this.value = text;
                            // Trigger input event to format properly
                            this.dispatchEvent(new Event('input'));
                        }
                    }
                });
            }

            // Handle Request Credits button click
            if (requestCreditsBtn) {
                requestCreditsBtn.onclick = function() {
                    const amount = parseFloat(customAmountInput.value);
                    if (!amount || amount <= 0) {
                        alert('Please enter a valid amount');
                        return;
                    }

                    // Submit request to backend
                    const formData = new FormData();
                    formData.append('amount', amount);

                    fetch('request_neocreds.php', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Your NeoCreds request has been submitted for approval');
                            modal.style.display = 'none';
                            customAmountInput.value = '';
                            loadBalance();
                            loadTransactionHistory();
                        } else {
                            throw new Error(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message || 'An error occurred while submitting your request');
                    });
                };
            }

            // Load current balance and transaction history
            function loadBalance() {
                fetch('process_neocreds.php?action=balance')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelector('.balance-amount').textContent = 
                                '₱' + parseFloat(data.balance).toFixed(2);
                            
                            // Show pending total if there are pending transactions
                            if (data.pending_total) {
                                const pendingDisplay = document.createElement('div');
                                pendingDisplay.className = 'pending-total';
                                pendingDisplay.innerHTML = `
                                    <span class="balance-label">Pending Requests Total</span>
                                    <span class="pending-amount">₱${parseFloat(data.pending_total).toFixed(2)}</span>
                                `;
                                
                                // Remove existing pending total if any
                                const existingPendingTotal = document.querySelector('.pending-total');
                                if (existingPendingTotal) {
                                    existingPendingTotal.remove();
                                }
                                
                                document.querySelector('.balance-display').appendChild(pendingDisplay);
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Load transaction history
            function loadTransactionHistory() {
                const transactionList = document.getElementById('transactionList');
                transactionList.innerHTML = '<div class="loading">Loading transactions...</div>';

                fetch('process_neocreds.php?action=history')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.transactions) {
                            if (data.transactions.length === 0) {
                                transactionList.innerHTML = `
                                    <div class="empty-history">
                                        <i class="fas fa-history"></i>
                                        <p>No transaction history yet</p>
                                    </div>
                                `;
                                return;
                            }

                            transactionList.innerHTML = data.transactions.map(transaction => `
                                <div class="transaction-item">
                                    <div class="transaction-details">
                                        <div class="transaction-amount">₱${parseFloat(transaction.amount).toFixed(2)}</div>
                                        <div class="transaction-date">${new Date(transaction.request_date).toLocaleString()}</div>
                                    </div>
                                    <span class="transaction-status status-${transaction.status.toLowerCase()}">
                                        ${transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1)}
                                    </span>
                                </div>
                            `).join('');
                        } else {
                            throw new Error(data.message || 'Failed to load transactions');
                        }
                    })
                    .catch(error => {
                        transactionList.innerHTML = `
                            <div class="empty-history">
                                <i class="fas fa-exclamation-circle"></i>
                                <p>Error loading transactions</p>
                            </div>
                        `;
                        console.error('Error:', error);
                    });
            }

            // Initial load
            loadBalance();
            loadTransactionHistory();
            
            // Refresh data every 30 seconds
            setInterval(() => {
                loadBalance();
                loadTransactionHistory();
            }, 30000);

            // Add new history modal functionality
            const historyModal = document.getElementById('historyModal');
            const viewHistoryBtn = document.getElementById('viewHistoryBtn');
            const closeHistoryBtn = document.querySelector('[data-modal="historyModal"]');

            if (viewHistoryBtn) {
                viewHistoryBtn.onclick = function() {
                    historyModal.style.display = 'block';
                    loadTransactionHistory(); // Load history when modal opens
                };
            }

            if (closeHistoryBtn) {
                closeHistoryBtn.onclick = function() {
                    historyModal.style.display = 'none';
                };
            }

            // Update window click handler to handle both modals
            window.onclick = function(event) {
                if (event.target == addCreditsModal) {
                    addCreditsModal.style.display = 'none';
                } else if (event.target == historyModal) {
                    historyModal.style.display = 'none';
                }
            };

            // Add this to your existing script section
            document.addEventListener('DOMContentLoaded', function() {
                // Capitalize first letter of each word
                function capitalizeWords(str) {
                    return str.split(' ')
                             .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                             .join(' ');
                }

                // Format city name (add City suffix if not present)
                function formatCityName(city) {
                    city = capitalizeWords(city.trim());
                    if (!city.toLowerCase().endsWith('city')) {
                        city += ' City';
                    }
                    return city;
                }

                // Format barangay name (add Brgy. prefix if not present)
                function formatBarangayName(barangay) {
                    barangay = capitalizeWords(barangay.trim());
                    if (!barangay.toLowerCase().startsWith('brgy.') && 
                        !barangay.toLowerCase().startsWith('barangay')) {
                        barangay = 'Brgy. ' + barangay;
                    }
                    return barangay;
                }

                // Format house details
                function formatHouseDetails(houseNum, streetName, subdivision) {
                    let details = capitalizeWords(houseNum.trim()) + ' ' + 
                                capitalizeWords(streetName.trim());
                    if (subdivision && subdivision.trim()) {
                        details += ', ' + capitalizeWords(subdivision.trim());
                    }
                    return details;
                }

                // Form submission handler
                document.getElementById('profileForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get all address components
                    const region = document.getElementById('region').value;
                    const city = document.getElementById('city').value.trim();
                    const barangay = document.getElementById('barangay').value.trim();
                    const houseNumber = document.getElementById('house_number').value.trim();
                    const streetName = document.getElementById('street_name').value.trim();
                    const subdivision = document.getElementById('subdivision').value.trim();

                    // Only proceed if we have the required fields
                    if (!region || !city || !barangay || !houseNumber || !streetName) {
                        alert('Please fill in all required fields');
                        return;
                    }

                    // Build house details
                    let houseDetails = [];
                    if (houseNumber) houseDetails.push(houseNumber);
                    if (streetName) houseDetails.push(streetName);
                    if (subdivision) houseDetails.push(subdivision);
                    
                    // Set the concatenated house details to the hidden input
                    document.getElementById('house_details').value = houseDetails.join(', ');

                    // Submit the form
                    this.submit();
                });

                // Real-time formatting for city input
                document.getElementById('city').addEventListener('blur', function() {
                    if (this.value.trim()) {
                        this.value = formatCityName(this.value);
                    }
                });

                // Real-time formatting for barangay input
                document.getElementById('barangay').addEventListener('blur', function() {
                    if (this.value.trim()) {
                        this.value = formatBarangayName(this.value);
                    }
                });

                // Contact number validation
                const contactInput = document.getElementById('contact');
                contactInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 11) {
                        this.value = this.value.slice(0, 11);
                    }
                });
            });
        });

        // Password toggle functions
        function showPassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            input.type = 'text';
            toggle.innerHTML = '<i class="fa-solid fa-eye"></i>';
        }

        function hidePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            input.type = 'password';
            toggle.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
        }

        // Make these functions globally available
        window.showPassword = showPassword;
        window.hidePassword = hidePassword;

        // City data by region
        const citiesByRegion = {
            "National Capital Region": [
                "Manila", "Quezon City", "Makati", "Pasig", "Taguig", "Parañaque",
                "Las Piñas", "Mandaluyong", "Marikina", "Pasay", "Caloocan",
                "Muntinlupa", "San Juan", "Valenzuela", "Navotas", "Malabon", "Pateros"
            ],
            "Cordillera Administrative Region": [
                "Baguio City", "Tabuk City", "Bangued", "La Trinidad", "Lagawe",
                "Bontoc", "Kabugao"
            ],
            // Add more regions and their cities as needed
        };

        // Function to update city dropdown
        function updateCityDropdown(region) {
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';
            
            if (region && citiesByRegion[region]) {
                citiesByRegion[region].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        }

        // Listen for region changes
        document.getElementById('region').addEventListener('change', function() {
            updateCityDropdown(this.value);
        });
    </script>
</body>
</html>
