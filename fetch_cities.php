
<?php
ob_start();
session_start();
include("admin/inc/config.php");
include("admin/inc/functions.php");
include("admin/inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
// $statement = $pdo->prepare("SELECT * FROM tbl_language");

if(isset($_POST['state_id'])) {
    $state_id = $_POST['state_id'];

    $stmt = $pdo->prepare("SELECT * FROM tbl_districts WHERE state_id = ? ORDER BY district_name ASC");
    $stmt->execute([$state_id]);
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<option value="">Select City/District</option>';
    foreach($districts as $district) {
        echo "<option value='{$district['district_id']}'>{$district['district_name']}</option>";
    }
}
?>
