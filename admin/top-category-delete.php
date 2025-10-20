<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

// Check if the top category exists
$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_id=?");
$statement->execute(array($_REQUEST['id']));
if($statement->rowCount() == 0) {
    header('location: logout.php');
    exit;
}

// Fetch all end-category IDs for this top category
$statement = $pdo->prepare("
    SELECT t3.ecat_id 
    FROM tbl_top_category t1
    JOIN tbl_mid_category t2 ON t1.tcat_id = t2.tcat_id
    JOIN tbl_end_category t3 ON t2.mcat_id = t3.mcat_id
    WHERE t1.tcat_id=?
");
$statement->execute(array($_REQUEST['id']));
$ecat_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

// If there are end categories, fetch products
if(!empty($ecat_ids)) {
    $p_ids = [];
    foreach($ecat_ids as $ecat_id) {
        $stmt = $pdo->prepare("SELECT p_id, p_featured_photo FROM tbl_product WHERE ecat_id=?");
        $stmt->execute(array($ecat_id));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($products as $prod) {
            $p_ids[$prod['p_id']] = $prod['p_featured_photo'];
        }
    }

    // Delete products and related data
    foreach($p_ids as $p_id => $featured_photo) {
        // Delete featured photo
        if(!empty($featured_photo) && file_exists('../assets/uploads/'.$featured_photo)) {
            unlink('../assets/uploads/'.$featured_photo);
        }

        // Delete additional product photos
        $stmt = $pdo->prepare("SELECT photo FROM tbl_product_photo WHERE p_id=?");
        $stmt->execute(array($p_id));
        $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach($photos as $photo) {
            if(file_exists('../assets/uploads/product_photos/'.$photo)) {
                unlink('../assets/uploads/product_photos/'.$photo);
            }
        }

        // Delete product-related data
        $tables = ['tbl_product', 'tbl_product_photo', 'tbl_product_size', 'tbl_product_color', 'tbl_rating'];
        foreach($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM $table WHERE p_id=?");
            $stmt->execute(array($p_id));
        }

        // Delete related orders and payments
        $stmt = $pdo->prepare("SELECT payment_id FROM tbl_order WHERE product_id=?");
        $stmt->execute(array($p_id));
        $payment_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach($payment_ids as $payment_id) {
            $stmt1 = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
            $stmt1->execute(array($payment_id));
        }

        $stmt = $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?");
        $stmt->execute(array($p_id));
    }

    // Delete end categories
    $in  = str_repeat('?,', count($ecat_ids) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM tbl_end_category WHERE ecat_id IN ($in)");
    $stmt->execute($ecat_ids);
}

// Delete mid categories
$statement = $pdo->prepare("DELETE FROM tbl_mid_category WHERE tcat_id=?");
$statement->execute(array($_REQUEST['id']));

// Delete top category
$statement = $pdo->prepare("DELETE FROM tbl_top_category WHERE tcat_id=?");
$statement->execute(array($_REQUEST['id']));

header('location: top-category.php');
exit;
?>
