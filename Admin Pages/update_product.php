<?php
// update_product.php

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: all_product_page.php');
    exit;
}

function uploadPhoto($file, $oldPhoto = null) {
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return $oldPhoto; // Keep the old photo if no new one is uploaded
    }

    if ($file['error'] == UPLOAD_ERR_OK) {
        $tmpName = $file['tmp_name'];
        $fileName = time() . "_" . basename($file['name']);
        $uploadDir = "../uploads/";
        $targetFile = $uploadDir . $fileName;

        // Check if file is an image
        $fileType = mime_content_type($tmpName);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG, and GIF files are allowed.");
        }

        // Check file size (max 5MB)
        if ($file['size'] > 5000000) {
            throw new Exception("File is too large.");
        }

        // Move the uploaded file
        if (move_uploaded_file($tmpName, $targetFile)) {
            // Delete old photo if it exists
            if ($oldPhoto && file_exists("../uploads/" . basename($oldPhoto))) {
                @unlink("../uploads/" . basename($oldPhoto));
            }
            return $targetFile;
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    }

    throw new Exception("Error uploading file.");
}

try {
    // Get form data
    $product_id = intval($_POST['product_id']);
    $product_name = trim($_POST['product_name']);
    $product_category = $_POST['product_category'];
    $product_price = floatval($_POST['product_price']);
    $quantity_small = intval($_POST['quantity_small']);
    $quantity_medium = intval($_POST['quantity_medium']);
    $quantity_large = intval($_POST['quantity_large']);
    $product_status = $_POST['product_status'];

    // Validate required fields
    if (empty($product_name) || $product_price <= 0) {
        throw new Exception("Please fill in all required fields correctly.");
    }

    // Get current product data
    $stmt = $conn->prepare("SELECT photoFront, photo1, photo2, photo3, photo4 FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_product = $result->fetch_assoc();
    $stmt->close();

    if (!$current_product) {
        throw new Exception("Product not found.");
    }

    // Handle photo uploads
    $photoFront = isset($_FILES['photo_front']) ? uploadPhoto($_FILES['photo_front'], $current_product['photoFront']) : $current_product['photoFront'];
    $photo1 = isset($_FILES['photo_1']) ? uploadPhoto($_FILES['photo_1'], $current_product['photo1']) : $current_product['photo1'];
    $photo2 = isset($_FILES['photo_2']) ? uploadPhoto($_FILES['photo_2'], $current_product['photo2']) : $current_product['photo2'];
    $photo3 = isset($_FILES['photo_3']) ? uploadPhoto($_FILES['photo_3'], $current_product['photo3']) : $current_product['photo3'];
    $photo4 = isset($_FILES['photo_4']) ? uploadPhoto($_FILES['photo_4'], $current_product['photo4']) : $current_product['photo4'];

    // Update product in database
    $sql = "UPDATE products SET 
            product_name = ?, 
            product_category = ?,
            product_price = ?,
            quantity_small = ?,
            quantity_medium = ?,
            quantity_large = ?,
            product_status = ?,
            photoFront = ?,
            photo1 = ?,
            photo2 = ?,
            photo3 = ?,
            photo4 = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssdiiissssssi",
        $product_name,
        $product_category,
        $product_price,
        $quantity_small,
        $quantity_medium,
        $quantity_large,
        $product_status,
        $photoFront,
        $photo1,
        $photo2,
        $photo3,
        $photo4,
        $product_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Error updating product: " . $stmt->error);
    }

    header("Location: all_product_page.php?updated=1");
    exit;

} catch (Exception $e) {
    header("Location: edit_product.php?id=" . $product_id . "&error=" . urlencode($e->getMessage()));
    exit;
}
?>
