$(document).ready(function() {
    // Handle form submission
    $("#change-password-form").on("submit", function(event) {
        event.preventDefault(); // Prevent default form submission

        // Get the user's language preference from localStorage
        const lang = localStorage.getItem('userLang') || 'en';

        // Prepare the form data for AJAX submission, including language
        var formData = $(this).serialize() + '&lang=' + encodeURIComponent(lang);

        // Send the AJAX request
        $.ajax({
            type: "POST",
            url: "./profil/password.php", // The server-side script
            data: formData,
            dataType: "json", // Expecting JSON response from server
            success: function(response) {
                // If the update is successful
                if (response.success) {
                    showMessage('success', response.message);
                    setTimeout(function() {
                        $('#success1').hide();
                    }, 3000);
                    $("#error1").hide(); // Hide the error message
                    // Clear the form fields after a successful update
                    $("#change-password-form")[0].reset();
                } else {
                    showMessage('error', response.message);
                    setTimeout(function() {
                        $('#error1').hide();
                    }, 10000);
                }
            },
            error: function(xhr, status, error) {
                // Handle any AJAX errors
                console.error("Error: " + error);
                showMessage('error', getTranslation('password.errorAjaxError', lang));
            }
        });
    });

    // Function to show messages with SweetAlert or DOM manipulation
    function showMessage(type, message) {
        if (type === 'success') {
            $("#success1").show();
            $("#success1 p").text(message);
            $("#error1").hide();
        } else {
            $("#error1").show();
            $("#error1 p").text(message);
            $("#success1").hide();
        }
    }

    // Function to get translation from localStorage or default
    function getTranslation(key, lang) {
        const translations = JSON.parse(localStorage.getItem('translations')) || {};
        return translations[key] || (lang === 'en' ? 'Default English Text' : 'Texte par défaut en français');
    }
});