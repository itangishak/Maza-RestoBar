$(document).ready(function() {
    // Bind to the form's submit event
    $('#settings').on('submit', function(e) {
        // Prevent the form from submitting normally
        e.preventDefault();

        // Hide any previous error messages
        $('#error').hide();
        $('#success').hide();
        // Collect the form data
        var formData = {
            firstname: $('#firstname').val(),
            lastname: $('#lastname').val(),
            email: $('#email').val(),
        
        };

        // Send the form data using an AJAX request
        $.ajax({
            type: 'POST',
            url: './profil/settingsSubmit.php',
            data: formData,
            dataType: 'json', // Expect a JSON response
            beforeSend: function() {
                // Disable the login button to prevent multiple submissions
                $('#submit').prop('disabled', true);
            },
            success: function(response) {
                // If the update is successful
                if (response.success) {
                    $("#success").show(); // Show the success message
                    setTimeout(function() {
                        $('#success').hide();
                    }, 3000); 
                    $("#error").hide(); // Hide the error message
                       // Clear the form fields after a successful update
                   
                } else {
                    $("#error").show(); // Show the error message
                    $("#error").html("<p>" + response.message + "</p>"); // Show specific error message
                    $("#success1").hide(); // Hide success message
                    setTimeout(function() {
                        $('#error').hide();
                    }, 10000);  
                }
            },
            error: function(xhr, status, error) {
                 // Handle any AJAX errors
                 console.error("Error: " + error);
                 $("#error1").show();
                 $("#error1").html("<p>Une erreur s'est produite lors de la modification du mot de passe.</p>");
                 $("#success1").hide();
            },
            complete: function() {
                // Re-enable the login button and hide the spinner
                $('#submit').prop('disabled', false);
               
            }
        });
    });
});
