<?php require_once('header.php'); ?>

<?php
// Function to generate unique slug for top category
function generate_unique_slug($pdo, $name) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $new_slug = $slug;
    $i = 1;
    while (true) {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_top_category WHERE t_category_slug = ?");
        $statement->execute(array($new_slug));
        $count = $statement->fetchColumn();
        if($count == 0) {
            break;
        }
        $new_slug = $slug . '-' . $i;
        $i++;
    }
    return $new_slug;
}

if(isset($_POST['form1'])) {
    $valid = 1;
    $error_message = '';
    $success_message = '';

    if(empty($_POST['tcat_name'])) {
        $valid = 0;
        $error_message .= "Top Category Name cannot be empty<br>";
    } else {
        // Duplicate Category checking
        $statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_name=?");
        $statement->execute(array($_POST['tcat_name']));
        $total = $statement->rowCount();
        if($total) {
            $valid = 0;
            $error_message .= "Top Category Name already exists<br>";
        }
    }

    if($valid == 1) {
        // Generate unique t_category_slug
        $t_category_slug = generate_unique_slug($pdo, $_POST['tcat_name']);

        // Insert into tbl_top_category
        $statement = $pdo->prepare("INSERT INTO tbl_top_category (tcat_name, show_on_menu, t_category_slug) VALUES (?, ?, ?)");
        $statement->execute(array($_POST['tcat_name'], $_POST['show_on_menu'], $t_category_slug));

        $success_message = 'Top Category is added successfully.';
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Top Level Category</h1>
    </div>
    <div class="content-header-right">
        <a href="top-category.php" class="btn btn-primary btn-sm">View All</a>
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
                            <label for="" class="col-sm-2 control-label">Top Category Name <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="tcat_name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Show on Menu? <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="show_on_menu" class="form-control" style="width:auto;">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
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
