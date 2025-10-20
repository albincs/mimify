<?php require_once('header.php'); ?>

<?php
// Function to generate a unique slug
function generate_unique_slug($pdo, $name) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $new_slug = $slug;
    $i = 1;
    while (true) {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_end_category WHERE e_category_slug = ?");
        $statement->execute(array($new_slug));
        $count = $statement->fetchColumn();
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

    // Check for duplicate end-level category name
    if($valid == 1) {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_end_category WHERE ecat_name = ?");
        $statement->execute(array($_POST['ecat_name']));
        $count = $statement->fetchColumn();
        if($count > 0) {
            $valid = 0;
            $error_message .= "End level category name already exists. Please choose another name.<br>";
        }
    }

    if($valid == 1) {
        $e_category_slug = generate_unique_slug($pdo, $_POST['ecat_name']);

        $statement = $pdo->prepare("INSERT INTO tbl_end_category (ecat_name, mcat_id, e_category_slug) VALUES (?, ?, ?)");
        $statement->execute(array($_POST['ecat_name'], $_POST['mcat_id'], $e_category_slug));

        $success_message = 'End Level Category is added successfully.';
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add End Level Category</h1>
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
                                    $statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);  
                                    foreach ($result as $row) {
                                        ?>
                                        <option value="<?php echo $row['tcat_id']; ?>"><?php echo $row['tcat_name']; ?></option>
                                        <?php
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
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">End Level Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="ecat_name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label"></label>
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
