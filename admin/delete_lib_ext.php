<?php
session_start();
ob_start(); // Start output buffering
include('secure.php');
include('header.php');
?>
    <div class="container-fluid py-4">
    <h4 class="mb-3">حذف مكتبة</h4>
      <?php
            // Check if the delete_success session variable is set
            if (isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true) {
                // Unset the session variable to avoid displaying the message on page refresh
                unset($_SESSION['delete_success']);
                // Redirect to the display_lib_ext page with a success message
                header("Location: display_lib_ext.php?delete_success=1");
                exit;
            }
            // Check if the item_not_found session variable is set
            if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
                // Unset the session variable to avoid displaying the message on page refresh
                unset($_SESSION['item_not_found']);
                // Redirect to the display_lib_ext page with a success message
                header("Location: display_lib_ext.php?item_not_found=1");
                exit;
            }

            include('../connect.php');

            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $id = intval($_GET['id']);   
                
                    if (isset($_POST['confirm'])) {
                        // Delete book type from users table
                        $sql = "DELETE FROM ext_libraries WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $id);
    
                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            mysqli_close($conn);
                            $_SESSION['delete_success'] = true;
                            header("Location: display_lib_ext.php?delete_success=1");
                            exit;
                        } else {
                            echo '<div class="alert alert-danger text-right">خطأ في عملية الحذف: ' . mysqli_error($conn) . '</div>';
                        }
    
                        mysqli_stmt_close($stmt);
                    } else {
                          // Get ext_libraries data to display	library name in the confirmation message
                            $sql = "SELECT library_name FROM ext_libraries WHERE id = ?";
                            $delete_stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($delete_stmt, "i", $id);
                            mysqli_stmt_execute($delete_stmt);
                            $result = mysqli_stmt_get_result($delete_stmt);
                            $libraryData = mysqli_fetch_assoc($result);
                            mysqli_stmt_close($delete_stmt);
                        echo '
                        <p class="text-right">هل أنت متأكد أنك تريد حذف مكتبة  ' . htmlspecialchars($libraryData['library_name']) .' ؟</p>
                        <form action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" method="post" class="d-inline">
                            <button type="submit" name="confirm" value="yes" class="btn btn-danger">نعم</button>
                        </form>
                        <a href="display_lib_ext.php" class="btn btn-secondary">لا</a>
                        ';
                    }
            } 
     // Close the database connection
     mysqli_close($conn);
include('footer.php');
ob_end_flush();
?>