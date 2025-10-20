<?php require_once('header.php'); ?>

<?php
// Prevent direct access
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} 

// Validate ecat_id
$ecat_id = $_REQUEST['id'];
$statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE ecat_id=?");
$statement->execute(array($ecat_id));
$end_category = $statement->fetch(PDO::FETCH_ASSOC);

if(!$end_category) {
    header('location: logout.php');
    exit;
}

// Get all products under this end-level category
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE ecat_id=?");
$statement->execute(array($ecat_id));
$products = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach($products as $product) {
    $p_id = $product['p_id'];

    // Delete featured photo
    if(!empty($product['p_featured_photo']) && file_exists('../assets/uploads/'.$product['p_featured_photo'])) {
        unlink('../assets/uploads/'.$product['p_featured_photo']);
    }

    // Delete product photos
    $statement2 = $pdo->prepare("SELECT photo FROM tbl_product_photo WHERE p_id=?");
    $statement2->execute(array($p_id));
    $photos = $statement2->fetchAll(PDO::FETCH_ASSOC);
    foreach($photos as $photo) {
        if(!empty($photo['photo']) && file_exists('../assets/uploads/product_photos/'.$photo['photo'])) {
            unlink('../assets/uploads/product_photos/'.$photo['photo']);
        }
    }

    // Delete related records
    $tables_to_delete = ['tbl_product', 'tbl_product_photo', 'tbl_product_size', 'tbl_product_color', 'tbl_rating'];
    foreach($tables_to_delete as $table) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE p_id=?");
        $stmt->execute(array($p_id));
    }

    // Delete payments and orders
    $stmt_order = $pdo->prepare("SELECT payment_id FROM tbl_order WHERE product_id=?");
    $stmt_order->execute(array($p_id));
    $orders = $stmt_order->fetchAll(PDO::FETCH_ASSOC);
    foreach($orders as $order) {
        $stmt_payment = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
        $stmt_payment->execute(array($order['payment_id']));
    }

    // Delete orders
    $stmt_delete_order = $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?");
    $stmt_delete_order->execute(array($p_id));
}

// Finally, delete the end-level category
$statement = $pdo->prepare("DELETE FROM tbl_end_category WHERE ecat_id=?");
$statement->execute(array($ecat_id));

// Redirect back
header('location: end-category.php');
exit;
?>
