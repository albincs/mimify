<?php require_once('header.php'); ?>

<?php
// Function to generate unique slug for top category
function generate_unique_slug($pdo, $name, $current_id = 0) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $new_slug = $slug;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_top_category WHERE t_category_slug = ? AND tcat_id != ?");
        $stmt->execute(array($new_slug, $current_id));
        $count = $stmt->fetchColumn();
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
        // Current Top Category name from DB
        $statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_id=?");
        $statement->execute(array($_REQUEST['id']));
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $current_tcat_name = $result['tcat_name'];

        // Duplicate checking excluding current category
        $statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_name=? AND tcat_name!=?");
        $statement->execute(array($_POST['tcat_name'], $current_tcat_name));
        if($statement->rowCount()) {
            $valid = 0;
            $error_message .= 'Top Category name already exists<br>';
        }
    }

    if($valid == 1) {
        // Generate unique slug for the updated name
        $t_category_slug = generate_unique_slug($pdo, $_POST['tcat_name'], $_REQUEST['id']);

        // Update the top category
        $statement = $pdo->prepare("UPDATE tbl_top_category SET tcat_name=?, show_on_menu=?, t_category_slug=? WHERE tcat_id=?");
        $statement->execute(array($_POST['tcat_name'], $_POST['show_on_menu'], $t_category_slug, $_REQUEST['id']));

        $success_message = 'Top Category is updated successfully.';
    }
}

// Fetch the current category
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}
$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_id=?");
$statement->execute(array($_REQUEST['id']));
if($statement->rowCount() == 0) {
    header('location: logout.php');
    exit;
}
$result = $statement->fetch(PDO::FETCH_ASSOC);
$tcat_name = $result['tcat_name'];
$show_on_menu = $result['show_on_menu'];
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Top Level Category</h1>
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
                        <input type="text" class="form-control" name="tcat_name" value="<?php echo $tcat_name; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Show on Menu? <span>*</span></label>
                    <div class="col-sm-4">
                        <select name="show_on_menu" class="form-control" style="width:auto;">
                            <option value="0" <?php if($show_on_menu == 0) {echo 'selected';} ?>>No</option>
                            <option value="1" <?php if($show_on_menu == 1) {echo 'selected';} ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label"></label>
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
