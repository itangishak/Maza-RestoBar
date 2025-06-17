<?php
session_start();
require_once '../connection.php';

// Check if the user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['UserId']; // Get the logged-in user's ID

// Initialize error/success message variables
$successMessage = '';
$errorMessage = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the file was uploaded
    if (!isset($_FILES['profileImage']) || $_FILES['profileImage']['error'] != UPLOAD_ERR_OK) {
        $errorMessage = "Error uploading the image.";
    } else {
        // File properties
        $file = $_FILES['profileImage'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];

        // Validate the file type (allow only jpg, jpeg, png, gif)
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errorMessage = "Invalid file format. Only JPG, JPEG, PNG, and GIF images are allowed.";
        } elseif ($fileSize > 2000000) {
            $errorMessage = "The image is too large. The maximum size is 2MB.";
        } else {
            // Generate a new unique name for the image
            $newFileName = "profile_" . $userId . "_" . time() . "." . $fileExt;
            $uploadDir = "../assets/img/uploads/"; // Ensure this directory exists
            $uploadPath = $uploadDir . $fileName;

            // Check if the directory exists
            if (!is_dir($uploadDir)) {
                echo "The upload directory does not exist.";
                exit();
            }

            // Check if the directory is writable
            if (!is_writable($uploadDir)) {
                echo "The upload directory is not writable.";
                exit();
            }

            // Move the uploaded file to the destination folder
            if (!move_uploaded_file($fileTmpName, $uploadPath)) {
                $errorMessage = "Error uploading the image. Please try again. " . $uploadPath;
            } else {
                // Update the image path in the database
                $sql = "UPDATE user SET image = ? WHERE UserId = ?";
                $stmt = $conn->prepare($sql);
                $imagePath = "./assets/img/uploads/" . $fileName; // Path to the uploaded image

                if ($stmt) {
                    $stmt->bind_param('si', $imagePath, $userId);

                    if ($stmt->execute()) {
                        $successMessage = "Profile image successfully updated.";
                    } else {
                        $errorMessage = "Database update error.";
                    }
                    $stmt->close();
                } else {
                    $errorMessage = "Error preparing the query.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload a Profile Image</title>
    <link href="../../Client/img/LogoMaza.png" rel="icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
   
</head>
<body>
   
<div class="container mt-5">
    <h2>Upload a New Profile Image</h2>

    <!-- Success and Error Messages -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Form to upload profile image -->
    <form id="upload-image-form" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="profileImage" class="form-label">Choose a new profile image</label>
            <input class="form-control" type="file" id="profileImage" name="profileImage" required>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary">Upload Image</button>
            <a href="../accountSettings.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

</body>
</html>
