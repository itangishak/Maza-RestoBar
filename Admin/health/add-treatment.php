<?php
require_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tag_number = $_POST['tag_number'];
    $treatment_description = $_POST['treatment_description'];
    $treatment_date = $_POST['treatment_date'];
    $notes = $_POST['treatment_notes'];

    // Step 1: Validate tag_number and check if pig's health_status is not 'mort'
    $validation_sql = "SELECT * FROM pigs WHERE tag_number = ? AND health_status != 'mort'";
    $validation_stmt = $conn->prepare($validation_sql);
    $validation_stmt->bind_param('s', $tag_number);
    $validation_stmt->execute();
    $validation_result = $validation_stmt->get_result();

    if ($validation_result->num_rows > 0) {
        // Step 2: If validation passes, insert treatment record into pig_health_treatments
        $sql = "INSERT INTO pig_health_treatments (tag_number, treatment_description, treatment_date, notes) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $tag_number, $treatment_description, $treatment_date, $notes);

        if ($stmt->execute()) {
            // Step 4: Update the health_status of the pig to 'malade' in the pigs table
            $update_health_sql = "UPDATE pigs SET health_status = 'Malade' WHERE tag_number = ?";
            $update_stmt = $conn->prepare($update_health_sql);
            $update_stmt->bind_param('s', $tag_number);
            $update_stmt->execute();
            // Return success response
            echo json_encode([
                "success" => true,
                "message" => "Traitement ajouté avec succès."
            ]);
        } else {
            // Return database error
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de l'ajout du traitement."
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
