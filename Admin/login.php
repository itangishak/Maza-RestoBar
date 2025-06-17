<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    include_once 'header.php'
    ?>
   <style>
    /* Reset some basic elements */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  background-color: #f5f5f5;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.container {
  width: 70%;
  max-width: 1200px;
  display: flex;
  border: 1px solid #ccc;
  background-color: white;
}

.logo-container {
  width: 50%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 0;
}



.tagline {
  text-align: center;
  font-size: 14px;
  margin-top: 20px;
  color: #3c823a; /* Adjust color to match the design */
  font-weight: bold;
}

.login-form-container {
  width: 50%;
  padding: 50px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

h2 {
  font-size: 24px;
  color: #333;
  margin-bottom: 20px;
}

form {
  display: flex;
  flex-direction: column;
}

label {
  font-size: 16px;
  margin-bottom: 5px;
}

input[type="text"], input[type="password"] {
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-bottom: 20px;
  width: 100%;
}

.password-container {
    position: relative; 
    display: flex;
    align-items: center; 
}

.password-container .show-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%); 
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center; 
    justify-content: center;
    padding: 0;
}

.fa{
    padding-bottom: 20px;
    font-size: 18px;
}

.login-button {
  padding: 10px 20px;
  background-color: #3c823a; /* Match button color */
  border: none;
  color: white;
  font-size: 16px;
  border-radius: 5px;
  cursor: pointer;
  width: 100%;
}

.login-button:hover {
  background-color: #2e642c;
}
.img-side{
    width: 460px;
    height: 400px;
}
.banner-top{
    position: fixed;
    top: 0;
    left: 0;
    background-color: #1E311E;
    width: 100%;
    height: 50px;
}
.banner-bottom{
    position: fixed;
    top: 50px;
    left: 0;
    background-color: #E9CD3B;
    width: 100%;
    height: 10px;
}
.logo{
    width: 60px;
    height: 40px;
    margin:10px 0 0 20px;
}
#usrError,#passError{
  display: none; /* Initially hidden */
  background-color: #f8d7da; /* Light red background */
  border: 1px solid #f5c6cb; /* Red border */
  color: #721c24; /* Dark red text color */
  padding: 10px; /* Padding around the text */
  border-radius: 5px; /* Rounded corners */
  font-size: 14px; /* Font size */
  margin-top: 10px; /* Margin above the div */
  font-family: Arial, sans-serif; /* Font family */
}
.spinner-border{
          border: 4px solid #f3f3f3; /* Light grey background */
          border-top: 4px solid blue; /* Blue color */
          border-radius: 50%;
          width: 20px;
          height: 20px;
          animation: spin 1s linear infinite;
          display:none;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Media Queries for Mobile Responsiveness */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
        width: 90%;
        border: none;
    }

    .logo-container, .login-form-container {
        width: 100%;
    }

    .login-form-container {
        padding: 20px;
    }

    h2 {
        font-size: 22px;
    }

    .tagline {
        font-size: 12px;
        margin-top: 10px;
    }

    .login-button {
        font-size: 14px;
        padding: 10px;
    }

    input[type="text"], input[type="password"] {
        font-size: 14px;
        padding: 8px;
    }

    label {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .logo {
        width: 40px;
    }
    .img-side{
        padding-top: 30px;
        width: 320px;
        height: auto;
    }
    h2 {
        font-size: 20px;
    }

    .tagline {
        font-size: 11px;
    }

    input[type="text"], input[type="password"] {
        font-size: 12px;
        padding: 6px;
    }

    .login-button {
        font-size: 12px;
        padding: 8px;
    }}
   </style>
</head>
<body>

    <!-- black line below all -->
    <div class="banner-top">
    <img src="../Client/img/LogoMaza.png" alt="pig" class="logo">
    </div>
    <!-- other one -->
    <div class="banner-bottom">
    </div>

    <div class="container">
        <!-- Left Section with Logo -->
        
        <div class="logo-container">
            <img src="../Client/img/main-back.jpg" alt="pig" class="img-side">
           
        </div>

        <!-- Right Section with Login Form -->
        <div class="login-form-container">
            <?php
            if (isset($error)) {
                echo "<p>$error</p>";
            }
            if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
                echo "<p>Your session has expired due to inactivity or screen off. Please log in again.</p>";
            }
            ?>
            <h2>Identifiez-vous</h2>
            <div class="input-group" id="passError" style="display: none;">
              <p>Le mot de passe n'est pas correct</p>
            </div>
            <div class="input-group" id="usrError" style="display: none;">
              <p>Le nom d'utilisateur n'est pas correct</p>
            </div>
            <form id="loginForms">
                <label for="username">Nom d'utilisateur:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">Mot de passe:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="show-password" onclick="togglePassword()">
                    <i id="open-eye" class="fa fa-eye"></i>  <!-- Open eye icon -->
                    <i id="closed-eye" class="fa fa-eye-slash" style="display:none;"></i>  <!-- Closed eye icon -->
                    </button>
                </div>
                
                <button type="submit" class="login-button">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Se connecter
                </button>
            </form>
        </div>
    </div>
    <script src="./assets/js/login.js"></script>
</body>

</html>
