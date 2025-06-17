<?php
require_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tag_number = $_POST['tag_number'];
    $medicine_name = $_POST['medicine_name'];
    $medicine_cost = $_POST['medicine_cost'];
    $date_given = $_POST['date_given'];
    $notes = $_POST['medicine_notes'];

    // Step 1: Validate tag_number and check if pig's health_status is not 'mort'
    $validation_sql = "SELECT * FROM pigs WHERE tag_number = ? AND health_status != 'mort'";
    $validation_stmt = $conn->prepare($validation_sql);
    $validation_stmt->bind_param('s', $tag_number);
    $validation_stmt->execute();
    $validation_result = $validation_stmt->get_result();

    if ($validation_result->num_rows > 0) {
        // Step 2: If validation passes, insert medicine record into pig_health_medicines
        $sql = "INSERT INTO pig_health_medicines (tag_number, medicine_name, medicine_cost, date_given, notes) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdss', $tag_number, $medicine_name, $medicine_cost, $date_given, $notes);

        if ($stmt->execute()) {
            // Step 4: Update the health_status of the pig to 'malade' in the pigs table
            $update_health_sql = "UPDATE pigs SET health_status = 'Malade' WHERE tag_number = ?";
            $update_stmt = $conn->prepare($update_health_sql);
            $update_stmt->bind_param('s', $tag_number);
            $update_stmt->execute();
            // Return success response
            echo json_encode([
                "success" => true,
                "message" => "Médicament ajouté avec succès."
            ]);
        } else {
            // Return database error
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de l'ajout du médicament."
            ]);
        }

        $stmt->close();
    } else {
        // Step 3: If validation fails, return error message
        echo json_encode([
            "success" => false,
            "message" => "Le numéro de marque est invalide ou le porc est mort."
        ]);
    }

    $validation_stmt->close();
    $conn->close();
}
?>
