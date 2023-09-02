<?php
session_start();
ob_start(); // Start output buffering
include('secure.php');
include('header.php');
?>
    <div class="container-fluid py-4">
      <?php
      if (isset($_SESSION['create_update_success']) && $_SESSION['create_update_success'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['create_update_success']);
        // Redirect to the display_library_percentages page with a success message
        header("Location: display_library_percentages.php?create_update_success=1");
        exit;
    }
    // Check if the item_not_found session variable is set
    if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['item_not_found']);
        // Redirect to the display_library_percentages page with a success message
        header("Location: display_library_percentages.php?item_not_found=1");
        exit;
    }
     // Database connection configuration
     include('../connect.php');
     $id = isset($_GET['id']) ? $_GET['id'] : '';
     $library_percentage = '';

     if (!empty($id)) {
         $stmt = mysqli_prepare($conn, "SELECT * FROM library_percentages WHERE id = ?");
         mysqli_stmt_bind_param($stmt, "i", $id);
         mysqli_stmt_execute($stmt);
         $result = mysqli_stmt_get_result($stmt);
     
         if (mysqli_num_rows($result) > 0) {
             $item = mysqli_fetch_assoc($result);
             $library_percentage = htmlspecialchars($item['library_percentage']);
         } else {
             $_SESSION['item_not_found'] = true;
             // Close the statement result
             mysqli_stmt_close($stmt);
             // Redirect to the display_library_percentages page after item not found
             header("Location: display_library_percentages.php");
             exit;
         }
         // Close the statement result
         mysqli_stmt_close($stmt);
     }
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         if (isset($_POST['updateData'])) {
             // Validate user input
             $library_percentage = htmlspecialchars($_POST['library_percentage']);
             if (empty($library_percentage)) {
                echo "<div class='alert alert-danger text-right text-white'>نوع العميل مطلوب</div>";
            } else {
                 // Prepare and execute SQL query
                 $stmt = mysqli_prepare($conn, "UPDATE library_percentages SET library_percentage = ? WHERE id = ?");
                 mysqli_stmt_bind_param($stmt, "si", $library_percentage, $id);
                 if (mysqli_stmt_execute($stmt)) {
                     $_SESSION['create_update_success'] = true;
                     header("Location: display_library_percentages.php");
                     exit;
                 } else {
                   echo "<div class='alert alert-danger text-right'>حدث خطأ أثناء تحديث المعلومات</div>";
                 }
                 mysqli_stmt_close($stmt);
             }
         }
     }
        ?>
          <form role="form" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
              <h4 class="mb-3">تحديث نوع عميل</h4>
              <div class="form-group">
                  <label class="form-label">نوع العميل :</label>
                  <input type="text" name="library_percentage" class="form-control border pe-2" value="<?php echo htmlspecialchars($library_percentage); ?>" required>
              </div>
              <div class="form-group mt-3">
                  <button type="submit" name="updateData" class="btn btn-primary">تحديث</button>
              </div>
          </form>
          <hr>
          <a href="display_library_percentages.php" class="btn btn-secondary">العودة إلى قائمة أنواع العملاء</a>
<?php
     // Close the database connection
     mysqli_close($conn);
include('footer.php');
ob_end_flush();
?>