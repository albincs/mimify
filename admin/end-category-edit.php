<?php require_once('header.php'); ?>

<?php
// Function to generate a unique slug
function generate_unique_slug($pdo, $name, $current_id = 0) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $new_slug = $slug;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_end_category WHERE e_category_slug=? AND ecat_id!=?");
        $stmt->execute(array($new_slug, $current_id));
        $count = $stmt->fetchColumn();
        if($count == 0) break;
        $new_slug = $slug . '-' . $i;
        $i++;
    }
    return $new_slug;
}

if(isset($_POST['form1'])) {
    $valid = 1;
    $error_message = '';
    $success_message = '';

    if(empty($_POST['tcat_id'])) {
        $valid = 0;
        $error_message .= "You must have to select a top level category<br>";
    }

    if(empty($_POST['mcat_id'])) {
        $valid = 0;
        $error_message .= "You must have to select a mid level category<br>";
    }

    if(empty($_POST['ecat_name'])) {
        $valid = 0;
        $error_message .= "End level category name cannot be empty<br>";
    }

    // Check for duplicate category name
    if($valid == 1) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_end_category WHERE ecat_name=? AND ecat_id!=?");
        $stmt->execute(array($_POST['ecat_name'], $_REQUEST['id']));
        $count = $stmt->fetchColumn();
        if($count > 0) {
            $valid = 0;
            $error_message .= "End level category name already exists. Please choose another name.<br>";
        }
    }

    if($valid == 1) {
        // Generate unique slug
        $e_category_slug = generate_unique_slug($pdo, $_POST['ecat_name'], $_REQUEST['id']);

        // Update database
        $stmt = $pdo->prepare("UPDATE tbl_end_category SET ecat_name=?, mcat_id=?, e_category_slug=? WHERE ecat_id=?");
        $stmt->execute(array($_POST['ecat_name'], $_POST['mcat_id'], $e_category_slug, $_REQUEST['id']));

        $success_message = 'End Level Category is updated successfully.';
    }
}

// Validate the ecat_id
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} 

$statement = $pdo->prepare("
    SELECT t1.*, t2.mcat_name, t2.tcat_id AS tcat_id, t3.tcat_name AS tcat_name
    FROM tbl_end_category t1
    JOIN tbl_mid_category t2 ON t1.mcat_id = t2.mcat_id
    JOIN tbl_top_category t3 ON t2.tcat_id = t3.tcat_id
    WHERE t1.ecat_id=?
");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if($total == 0){
    header('location: logout.php');
    exit;
}

foreach ($result as $row) {
    $ecat_name = $row['ecat_name'];
    $mcat_id = $row['mcat_id'];
    $tcat_id = $row['tcat_id'];
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit End Level Category</h1>
    </div>
    <div class="content-header-right">
        <a href="end-category.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">

<div class="row">
<div class="col-md-12">

    <?php if(!empty($error_message)): ?>
    <div class="callout callout-danger">
        <p><?php echo $error_message; ?></p>
    </div>
    <?php endif; ?>

    <?php if(!empty($success_message)): ?>
    <div class="callout callout-success">
        <p><?php echo $success_message; ?></p>
    </div>
    <?php endif; ?>

    <form class="form-horizontal" action="" method="post">

    <div class="box box-info">

        <div class="box-body">
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Top Level Category Name <span>*</span></label>
                <div class="col-sm-4">
                    <select name="tcat_id" class="form-control select2 top-cat">
                        <option value="">Select Top Level Category</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
                        $stmt->execute();
                        $top_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($top_categories as $row) {
                            $selected = ($row['tcat_id'] == $tcat_id) ? 'selected' : '';
                            echo "<option value='{$row['tcat_id']}' {$selected}>{$row['tcat_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Mid Level Category Name <span>*</span></label>
                <div class="col-sm-4">
                    <select name="mcat_id" class="form-control select2 mid-cat">
                        <option value="">Select Mid Level Category</option>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id=? ORDER BY mcat_name ASC");
                        $stmt->execute(array($tcat_id));
                        $mid_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($mid_categories as $row) {
                            $selected = ($row['mcat_id'] == $mcat_id) ? 'selected' : '';
                            echo "<option value='{$row['mcat_id']}' {$selected}>{$row['mcat_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="" class="col-sm-3 control-label">End Level Category Name <span>*</span></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="ecat_name" value="<?php echo $ecat_name; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="" class="col-sm-3 control-label"></label>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-success pull-left" name="form1">Update</button>
                </div>
            </div>

        </div>
    </div>

    </form>

</div>
</div>

</section>

<?php require_once('footer.php'); ?>
