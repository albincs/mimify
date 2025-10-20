<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
    // No ID provided, redirect safely
    header('location: mid-category.php');
    exit;
}

// Check the id is valid
$statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE mcat_id=?");
$statement->execute(array($_REQUEST['id']));
if($statement->rowCount() == 0) {
    header('location: mid-category.php');
    exit;
}

// Fetch all end category IDs under this mid-category
$statement = $pdo->prepare("SELECT ecat_id FROM tbl_end_category WHERE mcat_id=?");
$statement->execute(array($_REQUEST['id']));
$ecat_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

if(!empty($ecat_ids)) {
    foreach($ecat_ids as $ecat_id) {
        // Delete all products under this end category
        $statement = $pdo->prepare("SELECT p_id, p_featured_photo FROM tbl_product WHERE ecat_id=?");
        $statement->execute(array($ecat_id));
        $products = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach($products as $product) {
            $p_id = $product['p_id'];
            $p_featured_photo = $product['p_featured_photo'];

            // Delete photos
            if(file_exists('../assets/uploads/'.$p_featured_photo)) {
                unlink('../assets/uploads/'.$p_featured_photo);
            }

            $stmt_photos = $pdo->prepare("SELECT photo FROM tbl_product_photo WHERE p_id=?");
            $stmt_photos->execute(array($p_id));
            $photos = $stmt_photos->fetchAll(PDO::FETCH_COLUMN);
            foreach($photos as $photo) {
                if(file_exists('../assets/uploads/product_photos/'.$photo)) {
                    unlink('../assets/uploads/product_photos/'.$photo);
                }
            }

            // Delete product-related data
            $pdo->prepare("DELETE FROM tbl_product WHERE p_id=?")->execute(array($p_id));
            $pdo->prepare("DELETE FROM tbl_product_photo WHERE p_id=?")->execute(array($p_id));
            $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?")->execute(array($p_id));
            $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?")->execute(array($p_id));
            $pdo->prepare("DELETE FROM tbl_rating WHERE p_id=?")->execute(array($p_id));

            // Delete payments and orders
            $stmt_orders = $pdo->prepare("SELECT payment_id FROM tbl_order WHERE product_id=?");
            $stmt_orders->execute(array($p_id));
            $orders = $stmt_orders->fetchAll(PDO::FETCH_COLUMN);
            foreach($orders as $payment_id) {
                $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?")->execute(array($payment_id));
            }
            $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?")->execute(array($p_id));
        }

        // Delete end category
        $pdo->prepare("DELETE FROM tbl_end_category WHERE ecat_id=?")->execute(array($ecat_id));
    }
}

// Delete mid category
$pdo->prepare("DELETE FROM tbl_mid_category WHERE mcat_id=?")->execute(array($_REQUEST['id']));

header('location: mid-category.php');
exit;
?>
