<?php require_once('header.php'); ?>

<?php
// Function to generate a basic slug
function generate_slug($name) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = trim($slug, '-');
    return $slug;
}

$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
    $valid = 1;

    if(empty($_POST['tcat_id'])) {
        $valid = 0;
        $error_message .= "You must select a top level category.<br>";
    }

    if(empty($_POST['mcat_name'])) {
        $valid = 0;
        $error_message .= "Mid Level Category Name cannot be empty.<br>";
    }

    // Check for duplicate mid-category name
    if($valid == 1) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_mid_category WHERE mcat_name=?");
        $stmt->execute(array($_POST['mcat_name']));
        if($stmt->fetchColumn() > 0) {
            $valid = 0;
            $error_message .= "Mid Level Category Name already exists. Please choose another name.<br>";
        }
    }

    if($valid == 1) {
        // Generate slug
        $m_category_slug = generate_slug($_POST['mcat_name']);

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO tbl_mid_category (mcat_name, tcat_id, m_category_slug) VALUES (?, ?, ?)");
        $stmt->execute(array($_POST['mcat_name'], $_POST['tcat_id'], $m_category_slug));

        $success_message = "Mid Level Category added successfully.";
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Mid Level Category</h1>
    </div>
    <div class="content-header-right">
        <a href="mid-category.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <?php if($error_message): ?>
            <div class="callout callout-danger">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">

                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Top Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="tcat_id" class="form-control select2">
                                    <option value="">Select Top Level Category</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
                                    $stmt->execute();
                                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);    
                                    foreach ($result as $row) {
                                        echo "<option value='{$row['tcat_id']}'>{$row['tcat_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Mid Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="mcat_name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
