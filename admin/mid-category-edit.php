<?php require_once('header.php'); ?>

<?php
// Basic slug function
function generate_slug($name) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    return trim($slug, '-');
}

if(isset($_POST['form1'])) {
    $valid = 1;
    $error_message = '';
    $success_message = '';

    if(empty($_POST['tcat_id'])) {
        $valid = 0;
        $error_message .= "You must select a top level category.<br>";
    }

    if(empty($_POST['mcat_name'])) {
        $valid = 0;
        $error_message .= "Mid Level Category Name cannot be empty.<br>";
    }

    if($valid == 1) {
        // Generate slug
        $m_category_slug = generate_slug($_POST['mcat_name']);

        // Optional: Check if slug already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_mid_category WHERE m_category_slug=? AND mcat_id!=?");
        $stmt->execute(array($m_category_slug, $_REQUEST['id']));
        $count = $stmt->fetchColumn();
        if($count > 0) {
            $m_category_slug .= '-' . time(); // make it unique
        }

        // Update database
        $stmt = $pdo->prepare("UPDATE tbl_mid_category SET mcat_name=?, tcat_id=?, m_category_slug=? WHERE mcat_id=?");
        $stmt->execute(array($_POST['mcat_name'], $_POST['tcat_id'], $m_category_slug, $_REQUEST['id']));

        $success_message = 'Mid Level Category updated successfully.';
    }
}

// Fetch current mid-category info
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE mcat_id=?");
$stmt->execute(array($_REQUEST['id']));
if($stmt->rowCount() == 0) {
    header('location: logout.php');
    exit;
}
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$mcat_name = $row['mcat_name'];
$tcat_id = $row['tcat_id'];
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Mid Level Category</h1>
    </div>
    <div class="content-header-right">
        <a href="mid-category.php" class="btn btn-primary btn-sm">View All</a>
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

        <form class="form-horizontal" method="post">

            <div class="box box-info">
                <div class="box-body">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Top Level Category <span>*</span></label>
                        <div class="col-sm-4">
                            <select name="tcat_id" class="form-control select2">
                                <option value="">Select Top Level Category</option>
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
                                $stmt->execute();
                                $top_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach($top_categories as $cat) {
                                    $selected = ($cat['tcat_id'] == $tcat_id) ? 'selected' : '';
                                    echo "<option value='{$cat['tcat_id']}' $selected>".htmlspecialchars($cat['tcat_name'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Mid Level Category Name <span>*</span></label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="mcat_name" value="<?php echo htmlspecialchars($mcat_name); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"></label>
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
