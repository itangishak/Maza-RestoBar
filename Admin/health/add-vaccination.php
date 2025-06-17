<?php
require_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tag_number = $_POST['tag_number'];
    $vaccine_name = $_POST['vaccine_name'];
    $date_given = $_POST['date_given_vaccine'];
    $next_due_date = $_POST['next_due_date'];
    $notes = $_POST['vaccine_notes'];

    // Step 1: Validate tag_number and check if pig's health_status is not 'mort'
    $validation_sql = "SELECT * FROM pigs WHERE tag_number = ? AND health_status != 'mort'";
    $validation_stmt = $conn->prepare($validation_sql);
    $validation_stmt->bind_param('s', $tag_number);
    $validation_stmt->execute();
    $validation_result = $validation_stmt->get_result();

    if ($validation_result->num_rows > 0) {
        // Step 2: If validation passes, insert vaccination record into pig_health_vaccinations
        $sql = "INSERT INTO pig_health_vaccinations (tag_number, vaccine_name, date_given, next_due_date, notes) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssss', $tag_number, $vaccine_name, $date_given, $next_due_date, $notes);

        if ($stmt->execute()) {
            // Return success response
            echo json_encode([
                "success" => true,
                "message" => "Vaccination ajoutée avec succès."
            ]);
        } else {
            // Return database error
            echo json_encode([
                "success" => false,
                "message" => "Erreur lors de l'ajout de la vaccination."
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
