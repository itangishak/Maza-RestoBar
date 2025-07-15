// function to hide and to show password eye
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const openEyeIcon = document.getElementById('open-eye');
    const closedEyeIcon = document.getElementById('closed-eye');

    if (passwordInput.type === 'password') {
        // Show the password
        passwordInput.type = 'text';
        // Switch icons
        openEyeIcon.style.display = 'none';
        closedEyeIcon.style.display = 'inline';
    } else {
        // Hide the password
        passwordInput.type = 'password';
        // Switch icons
        openEyeIcon.style.display = 'inline';
        closedEyeIcon.style.display = 'none';
    }
}

// Wait for jQuery to be ready
function initializeLoginForm() {
    // Bind to the form's submit event
    $('#loginForms').on('submit', function(e) {
        // Prevent the form from submitting normally
        e.preventDefault();

        // Hide any previous error messages
        $('#passError').hide();
        $('#usrError').hide();
        
        // Collect the form data
        var formData = {
            username: $('#username').val(),
            password: $('#password').val()
        };

        // Display the loading spinner as inline-block
        $('.spinner-border').show().css('display', 'inline-block');

        // Send the form data using an AJAX request
        $.ajax({
            type: 'POST',
            url: 'processCredentials.php',
            data: formData,
            dataType: 'json', // Expect a JSON response
            beforeSend: function() {
                // Disable the login button to prevent multiple submissions
                $('#loginButton').prop('disabled', true);
            },
            success: function(response) {
                // Check the response from the server
                if(response.link) {
                    // Login was successful
                    window.location.href = response.link;  // Redirect to a new page
                } else {
                    // Login failed, show the appropriate error message
                    if(response.error === 'password') {
                        $('#passError').show();
                        setTimeout(function() {
                            $('#passError').hide();
                        }, 3000);
                    } else if(response.error === 'username') {
                        $('#usrError').show();
                        setTimeout(function() {
                            $('#usrError').hide();
                        }, 3000);
                    } 
                }
            },
            error: function(xhr, status, error) {
                // Handle any AJAX error
                console.error("Error: " + error);
                alert("An error occurred. Please try again later.");
            },
            complete: function() {
                // Re-enable the login button and hide the spinner
                $('#loginButton').prop('disabled', false);
                $('.spinner-border').hide();
            }
        });
    });
}

// Initialize after DOM is ready
$(document).ready(initializeLoginForm);
