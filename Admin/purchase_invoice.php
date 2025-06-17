<?php
session_start();
require_once 'connection.php';

// 1) Vérification de session
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Accès non autorisé.']);
    exit;
}

// 2) Récupérer l'utilisateur
$user_id = $_SESSION['UserId'];
$stmt = $conn->prepare("SELECT firstname, lastname, email FROM user WHERE UserId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Session utilisateur invalide.']);
    exit;
}

$created_by = $user['firstname'] . ' ' . $user['lastname'];

// 3) Récupérer les données POST
$supplier_id    = $_POST['supplier_id']   ?? null;
$items          = $_POST['items']         ?? [];
$notes          = $_POST['notes']         ?? '';
$payment_method = $_POST['payment_method'] ?? 'Espèces';

if (!$supplier_id || empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Données fournisseur ou produit invalides.']);
    exit;
}

// 4) Récupérer le nom du fournisseur
$stmt = $conn->prepare("SELECT contact_person FROM suppliers WHERE supplier_id = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Échec de la préparation de la requête fournisseur.']);
    exit;
}
$stmt->bind_param("i", $supplier_id);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Échec de l\'exécution de la requête fournisseur.']);
    exit;
}
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

$supplier_name = $supplier['contact_person'] ?? "Fournisseur ID: $supplier_id";
if (!$supplier) {
    error_log("Fournisseur non trouvé pour l'ID : $supplier_id");
}

// 5) Traitement des articles
$total_amount = 0.0;
$product_details = [];

foreach ($items as $item) {
    $product_id = $item['product'] ?? null;
    $quantity   = (float)($item['quantity'] ?? 0);
    $unit_cost  = (float)($item['unit_cost'] ?? 0);

    if (!$product_id || $quantity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Données de produit invalides.']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT ii.item_name, u.unit_name 
        FROM inventory_items ii
        JOIN units u ON ii.unit = u.unit_id
        WHERE ii.inventory_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $prodRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $product_name = $prodRow['item_name']   ?? 'Produit inconnu';
    $measurement  = $prodRow['unit_name']   ?? 'N/D';
    $total_cost   = $quantity * $unit_cost;
    $total_amount += $total_cost;

    $product_details[] = [
        'product_id'   => $product_id,
        'product_name' => $product_name,
        'measurement'  => $measurement,
        'quantity'     => $quantity,
        'unit_cost'    => $unit_cost,
        'total_cost'   => $total_cost,
    ];
}

// 6) Générer le numéro de commande
$invoice_number = "PO-" . date("Ymd") . "-" . uniqid();
$order_date     = date('Y-m-d H:i:s');

// 7) Insérer dans purchase_records
$stmt = $conn->prepare("
    INSERT INTO purchase_records 
    (po_number, order_date, payment_method, created_by, total)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("ssssd", $invoice_number, $order_date, $payment_method, $created_by, $total_amount);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Échec de l\'insertion dans purchase_records.']);
    exit;
}
$purchase_id = $stmt->insert_id;
$stmt->close();

// 8) Insérer dans purchase_order_items
foreach ($product_details as $detail) {
    $stmt = $conn->prepare("
        INSERT INTO purchase_order_items
        (purchase_order_id, inventory_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iidd", $purchase_id, $detail['product_id'], $detail['quantity'], $detail['unit_cost']);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Échec de l\'insertion dans purchase_order_items.']);
        exit;
    }
}
$stmt->close();

// 9) Générer le reçu pour impression (retourné au client pour impression)
$receiptContent = '
    <img id="receiptLogo" alt="Logo" style="width:100px; margin-bottom:0px;" />
    <hr>
    <h2>Maza Resto-Bar</h2>
    <p>Address: Boulevard Melchior Ndadaye, Peace Corner, Bujumbura</p>
    <p>Phone: +257 69 80 58 98 | Email: barmazaresto@gmail.com</p>
    <hr>
    <p>Bon de commande #: ' . htmlspecialchars($invoice_number) . '</p>
    <p>Date: ' . date('Y-m-d') . '</p>
    <p>Fournisseur: ' . htmlspecialchars($supplier_name) . '</p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Mesure</th>
                <th>Qté</th>
                <th>Coût Un.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';

foreach ($product_details as $d) {
    $receiptContent .= '
        <tr>
            <td>' . htmlspecialchars($d['product_name']) . '</td>
            <td>' . htmlspecialchars($d['measurement']) . '</td>
            <td>' . $d['quantity'] . '</td>
            <td>' . number_format($d['unit_cost'], 2) . '</td>
            <td>' . number_format($d['total_cost'], 2) . '</td>
        </tr>';
}

$receiptContent .= '
        </tbody>
    </table>
    <p class="text-end">Total: ' . number_format($total_amount, 2) . ' BIF</p>
    <hr>
    <p>Notes: ' . htmlspecialchars($notes ?: 'Aucune') . '</p>
    <p>Créé par: ' . htmlspecialchars($created_by) . '</p>
    <hr>
    <p>Signatures:</p>
    <p>Magasinier: _______________</p>
    <p>Approuvé par: _______________</p>
    <p>Reçu par: _______________</p>
    <hr>
    <p>Merci pour votre collaboration!</p>';

echo json_encode([
    'status' => 'success',
    'message' => 'Bon de commande créé avec succès.',
    'receiptContent' => $receiptContent,
    'invoiceNumber' => $invoice_number
]);
?>