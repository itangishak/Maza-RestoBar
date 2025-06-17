<!DOCTYPE html>
<html lang="fr">

<head>
  <?php
    session_start();
    require_once './connection.php';
    include_once 'header.php';
  ?>

</head>

<body>

  <!-- ======= Header ======= -->
  <?php include_once './navbar.php'; ?>
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <?php
   
   if($_SESSION['privilege']=='Administrateur'){
    include_once './sidebar.php';
}
else if($_SESSION['privilege']=='Boss'){
    include_once './sidebarboss.php';
}
else{
    include_once './sidebaruser.php';
}
  ?>
  <!-- End Sidebar-->

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Gestion des Utilisateurs</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Tableau de Bord</a></li>
          <li class="breadcrumb-item active">Gestion des Utilisateurs</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Ajouter un Utilisateur</h5>
              <form id="addUserForm">
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Prénom</label>
                  <div class="col-sm-10">
                    <input type="text" name="firstname" class="form-control" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Nom</label>
                  <div class="col-sm-10">
                    <input type="text" name="lastname" class="form-control" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Email</label>
                  <div class="col-sm-10">
                    <input type="email" name="email" class="form-control" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Mot de Passe</label>
                  <div class="col-sm-10">
                    <input type="password" name="password" class="form-control" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Privilège</label>
                  <div class="col-sm-10">
                    <select name="privilege" class="form-control" required>
                      <option value="Utilisateur">Utilisateur</option>
                      <option value="Administrateur">Administrateur</option>
                      <option value="Boss">Boss</option>
                    </select>
                  </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
              </form>
            </div>
          </div>
        </div>
        <?php
        if($_SESSION['privilege']=='Boss'){
        ?>
        <!-- User List -->
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Liste des Utilisateurs</h5>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Privilège</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="userList">
                  <!-- User list will be populated by AJAX -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
             };
        ?>
      </div>
    </section>
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <?php include_once './footer.php'; ?>
  <!-- End Footer -->

  <!-- User Management Scripts -->
  <script>
    // Fetch and display users
    function fetchUsers() {
      $.ajax({
        url: 'fetch_users.php',
        type: 'GET',
        success: function (data) {
          $('#userList').html(data); // Populate user list
        }
      });
    }

    // Load users when the page loads
    $(document).ready(function () {
      fetchUsers();
    });

    // Handle Add User form submission
    $('#addUserForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'add_user.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',  // Expect JSON response
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Succès!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            fetchUsers();  // Reload user list or reset form
                           
                        }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload(); // Reload the page to reflect the deletion
                                    }
                                });
                    } else {
                        Swal.fire({
                            title: 'Erreur!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Erreur!',
                        text: "Une erreur est survenue lors de l'envoi du formulaire.",
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

    // Handle delete user
    function deleteUser(UserId) {
      Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer!',
        cancelButtonText: 'Annuler'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'delete_user.php',
            type: 'POST',
            data: { id: UserId },
            success: function (response) {
              Swal.fire('Supprimé!', 'Utilisateur supprimé avec succès.', 'success');
              fetchUsers(); // Refresh user list
            },
            error: function () {
              Swal.fire('Erreur!', 'Impossible de supprimer l\'utilisateur.', 'error');
            }
          });
        }
      });
    }

    // Handle edit user (edit form logic should be similar to adding)
  </script>

</body>

</html>
