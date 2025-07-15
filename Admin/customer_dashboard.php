<?php
session_start();
require_once 'connection.php'; // Définir la durée de la session
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gestion des clients</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->

    <!-- DataTables CSS -->

    <!-- SweetAlert2 CSS -->
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }

        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Ajuster le style si la barre latérale est masquée */
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }

        .main-container.full {
            margin-left: 0 !important;
        }
    </style>

</head>

<body>
    <!-- ======= Header ======= -->
    <?php
    include_once './navbar.php';
    ?>
    <!-- ======= Sidebar ======= -->
    <?php include_once 'sidebar.php'; ?>
    <!-- End Sidebar -->

    <main class="container main-container">
        <div class="card">
            <div class="card-header">
                <h4>Gestion des clients</h4>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">+ Créer un client</button>
                    <button class="btn btn-secondary btn-sm me-2" id="exportBtn">Exporter</button>
                    <button class="btn btn-info btn-sm" id="importBtn">Importer</button>
                </div>
            </div>
            <div class="card-body">
                <table id="customerTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>E-mail</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Date d'adhésion</th>
                            <th>Total des commandes</th>
                            <th>Revenu total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="customerForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCustomerModalLabel">Ajouter un nouveau client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom de famille *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Téléphone *</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Ajouter un client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCustomerForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCustomerModalLabel">Modifier le client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Champ caché pour stocker customer_id -->
                        <input type="hidden" id="edit_customer_id" name="customer_id">

                        <div class="mb-3">
                            <label for="edit_first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_last_name" class="form-label">Nom de famille *</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Téléphone *</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour le client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Customers Modal -->
    <div class="modal fade" id="importCustomersModal" tabindex="-1" aria-labelledby="importCustomersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="importCustomersForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importCustomersModalLabel">Importer des clients depuis un fichier CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Champ de fichier pour CSV -->
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Choisir un fichier CSV</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <small class="text-muted">Format de fichier : CSV avec les colonnes : first_name, last_name, email, phone, address</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Importer des clients</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inclure le pied de page -->
    <?php include_once './footer.php'; ?>

    <!-- JS Libraries -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/vendor/DataTables/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/DataTables/dataTables.buttons.min.js"></script>
    <script src="assets/vendor/DataTables/buttons.html5.min.js"></script>

    <!-- Pour Copier, Excel, PDF, et Imprimer -->
    <script src="assets/vendor/DataTables/buttons.print.min.js"></script>
    <script src="assets/vendor/DataTables/jszip.min.js"></script>
    <script src="assets/vendor/DataTables/pdfmake.min.js"></script>
    <script src="assets/vendor/DataTables/vfs_fonts.js"></script>
    <script>
        $(document).ready(function () {
            // Initialiser DataTable
            let table = $('#customerTable').DataTable({
                processing: true,
                serverSide: false, // Passer à true pour de gros ensembles de données
                ajax: {
                    url: 'fetch_customers.php',
                    type: 'GET',
                    dataType: 'json',
                    error: function(xhr, error, thrown) {
                        console.error("Erreur DataTables :", error, thrown);
                        Swal.fire("Erreur", "Échec du chargement des données du client.", "error");
                    }
                },
                columns: [
                    { data: "customer_name", title: "Nom" },
                    { data: "email", title: "E-mail" },
                    { data: "phone", title: "Téléphone" },
                    { data: "address", title: "Adresse" },
                    { data: "joined_date", title: "Date d'adhésion" },
                    { data: "total_orders", title: "Total des commandes" },
                    { data: "total_revenue", title: "Revenu total" },
                    { data: "actions", title: "Action", orderable: false, searchable: false }
                ],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Copier dans le presse-papiers',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'Exporter en PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Imprimer',
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ]
            });

            // Fonctionnalité d'export (exemple)
            $('#exportBtn').on('click', function () {
                // Rediriger vers l'endpoint d'export pour télécharger les données
                window.location.href = 'export_customers.php';
            });

            // Fonctionnalité d'import (exemple)
            $('#importBtn').on('click', function () {
                $('#importCustomersModal').modal('show');
            });

            // Soumettre le formulaire pour ajouter un nouveau client
            $('#customerForm').on('submit', function (e) {
                e.preventDefault(); // Empêche l'envoi du formulaire

                const formData = $(this).serialize(); // Sérialiser les données du formulaire

                $.ajax({
                    url: 'add_customers.php',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire('Succès', res.message, 'success');
                                $('#customerForm')[0].reset(); // Réinitialiser le formulaire
                                table.ajax.reload(); // Recharger DataTable
                            } else {
                                Swal.fire('Erreur', res.message, 'error');
                            }
                        } catch (e) {
                            console.error('Erreur JSON :', e);
                            Swal.fire('Erreur', 'Réponse invalide du serveur.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Erreur', 'Une erreur s\'est produite. Veuillez réessayer plus tard.', 'error');
                    }
                });
            });

            // Gérer le clic sur le bouton Supprimer
            $('#customerTable').on('click', '.delete-btn', function () {
                const customerId = $(this).data('id'); // Récupérer l'ID client

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Vous ne pourrez pas annuler cette action !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimez-le !'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Requête AJAX vers delete_customer.php
                        $.ajax({
                            url: 'delete_customer.php',
                            type: 'POST',
                            data: { customer_id: customerId },
                            success: function (response) {
                                try {
                                    const res = JSON.parse(response);
                                    if (res.status === 'success') {
                                        Swal.fire('Supprimé !', res.message, 'success');
                                        table.ajax.reload(); // Recharger DataTable
                                    } else {
                                        Swal.fire('Erreur', res.message, 'error');
                                    }
                                } catch (e) {
                                    console.error('Erreur JSON :', e);
                                    Swal.fire('Erreur', 'Réponse invalide du serveur.', 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Erreur', 'Une erreur s\'est produite. Veuillez réessayer plus tard.', 'error');
                            }
                        });
                    }
                });
            });

            // Logique pour masquer/afficher la barre latérale
            const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
            const sidebar = document.querySelector('.sidebar');
            const mainContainer = document.querySelector('.main-container');
            const card = document.querySelector('.card');

            // Ajuster la taille de la carte au chargement
            if (sidebar && card) {
                if (!sidebar.classList.contains('collapsed')) {
                    card.style.width = '87%';
                    card.style.marginLeft = '20%';
                } else {
                    card.style.width = '100%';
                    card.style.marginLeft = '5%';
                }
            }

            // Gérer le clic pour basculer la barre latérale
            if (toggleSidebarBtn && sidebar && mainContainer && card) {
                toggleSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('collapsed');
                    mainContainer.classList.toggle('full');

                    if (!sidebar.classList.contains('collapsed')) {
                        card.style.width = '87%';
                        card.style.marginLeft = '20%';
                    } else {
                        card.style.width = '100%';
                        card.style.marginLeft = '5%';
                    }
                });
            }

            // Gérer le clic sur le bouton Modifier
            $('#customerTable').on('click', '.edit-btn', function () {
                const customerId = $(this).data('id');
                // Récupérer les données du client
                $.ajax({
                    url: 'get_customer.php',
                    type: 'POST',
                    data: { customer_id: customerId },
                    success: function (response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                const customer = res.data;
                                // Remplir les champs du formulaire
                                $('#edit_customer_id').val(customer.customer_id);
                                $('#edit_first_name').val(customer.first_name);
                                $('#edit_last_name').val(customer.last_name);
                                $('#edit_email').val(customer.email);
                                $('#edit_phone').val(customer.phone);
                                $('#edit_address').val(customer.address);

                                // Afficher la fenêtre modale
                                $('#editCustomerModal').modal('show');
                            } else {
                                Swal.fire('Erreur', res.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Erreur', 'Réponse invalide du serveur.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Erreur', 'Une erreur s\'est produite. Veuillez réessayer plus tard.', 'error');
                    }
                });

            });

            // Soumettre le formulaire pour mettre à jour le client
            $('#editCustomerForm').on('submit', function (e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: 'update_customer.php',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire('Succès', res.message, 'success');
                                $('#editCustomerForm')[0].reset();
                                $('#editCustomerModal').modal('hide');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Erreur', res.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Erreur', 'Réponse invalide du serveur.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Erreur', 'Une erreur s\'est produite. Veuillez réessayer plus tard.', 'error');
                    }
                });
            });

            // Gérer le formulaire d'import
            $('#importCustomersForm').on('submit', function (e) {
                e.preventDefault();

                // Préparer les données du formulaire (y compris le fichier)
                const formData = new FormData(this);

                $.ajax({
                    url: 'import_customers.php',
                    type: 'POST',
                    data: formData,
                    processData: false, // Nécessaire pour l'upload
                    contentType: false, // Nécessaire pour l'upload
                    success: function (response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire('Succès', res.message, 'success');
                                $('#importCustomersForm')[0].reset();
                                $('#importCustomersModal').modal('hide');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Erreur', res.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Erreur', 'Réponse invalide du serveur.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Erreur', 'Une erreur s\'est produite. Veuillez réessayer plus tard.', 'error');
                    }
                });
            });

        });
    </script>

</body>
</html>
