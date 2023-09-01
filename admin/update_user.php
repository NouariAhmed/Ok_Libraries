<?php
session_start();
ob_start(); // Start output buffering
include('secure.php');
include('header.php');
$username = $email = $role ='';
?>
    <div class="container-fluid py-4">
      <?php
      if (isset($_SESSION['create_update_success']) && $_SESSION['create_update_success'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['create_update_success']);
        // Redirect to the display_users page with a success message
        header("Location: display_users.php?create_update_success=1");
        exit;
    }
    // Check if the item_not_found session variable is set
    if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['item_not_found']);
        // Redirect to the display_users page with a success message
        header("Location: display_users.php?item_not_found=1");
        exit;
    }
     // Database connection configuration
     include('../connect.php');
     $id = isset($_GET['id']) ? $_GET['id'] : '';


     if (!empty($id)) {
         $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
         mysqli_stmt_bind_param($stmt, "i", $id);
         mysqli_stmt_execute($stmt);
         $result = mysqli_stmt_get_result($stmt);
     
         if (mysqli_num_rows($result) > 0) {
             $item = mysqli_fetch_assoc($result);
             $username = htmlspecialchars($item['username']);
             $email = htmlspecialchars($item['email']);
             $role = htmlspecialchars($item['role']);
         } else {
             $_SESSION['item_not_found'] = true;
             // Close the statement result
             mysqli_stmt_close($stmt);
             // Redirect to the display_users page after item not found
             header("Location: display_users.php");
             exit;
         }
         // Close the statement result
         mysqli_stmt_close($stmt);
     }
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['updateData'])) {
            // Validate user input
            $updatedUname = htmlspecialchars($_POST["txt_uname"]);
            $updatedEmail = htmlspecialchars($_POST["txt_email"]);
            $updatedUserType = htmlspecialchars($_POST["user_type"]);
            $password = htmlspecialchars($_POST["txt_password"]);
            $confirmPassword = htmlspecialchars($_POST["txt_confirm_password"]);
    
            // Validate updated username
            if (empty($updatedUname)) {
                echo "<div class='alert alert-danger text-right text-white'>يرجى إدخال اسم المستخدم.</div>";
            } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $updatedUname)) {
                echo "<div class='alert alert-danger text-right text-white'>إسم المستخدم يجب أن يحتوى فقط على حروف،أرقام،مطات.</div>";
            }
    
            // Validate updated email
            if (empty($updatedEmail)) {
                echo "<div class='alert alert-danger text-right text-white'>يرجى إدخال الإيميل.</div>";
            } elseif (!filter_var($updatedEmail, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='alert alert-danger text-right text-white'>نوع الإيميل غير صالح.</div>";
            }
    
            // Validate updated user type
            if (empty($updatedUserType)) {
                echo "<div class='alert alert-danger text-right text-white'>يرجى إختيار نوع المستخدم.</div>";
            }
    
            // Validate passwords
            if (!empty($password) || !empty($confirmPassword)) {
                if (empty($password) || empty($confirmPassword)) {
                    echo "<div class='alert alert-danger text-right text-white'>يرجى إدخال كلا الحقلين (كلمة المرور، تأكيد كلمة المرور)</div>";
                } elseif ($password !== $confirmPassword) {
                    echo "<div class='alert alert-danger text-right text-white'>كلمتا المرور غير متطابقتين.</div>";
                } else {
                    // Create a database connection
                    include('../connect.php');
    
                    // Hash the new password
                    $hashedPassword = password_hash($confirmPassword, PASSWORD_DEFAULT);
    
                    // Update the user record including the password
                    $sql_update_user = "UPDATE users SET username = ?, email = ?, pass = ?, role = ? WHERE id = ?";
                    $stmt_update_user = mysqli_prepare($conn, $sql_update_user);
                    mysqli_stmt_bind_param($stmt_update_user, "ssssi", $updatedUname, $updatedEmail, $hashedPassword, $updatedUserType, $id);
    
                    if (mysqli_stmt_execute($stmt_update_user)) {
                        $_SESSION['create_update_success'] = true;
                        header("Location: display_users.php");
                        exit();
                    } else {
                        echo "<div class='alert alert-danger text-right text-white'>حدث خطأ أثناء تحديث المعلومات</div>";
                    }
                    
                    // Close the statement
                    mysqli_stmt_close($stmt_update_user);
                }
            } else {
                // Create a database connection
                include('../connect.php');
                
                // Check if the email or username already exists in the database (excluding the current user)
                $sql_check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
                $stmt_check_email = mysqli_prepare($conn, $sql_check_email);
                mysqli_stmt_bind_param($stmt_check_email, "si", $updatedEmail, $id);
                mysqli_stmt_execute($stmt_check_email);
                mysqli_stmt_store_result($stmt_check_email);
    
                $sql_check_username = "SELECT id FROM users WHERE username = ? AND id != ?";
                $stmt_check_username = mysqli_prepare($conn, $sql_check_username);
                mysqli_stmt_bind_param($stmt_check_username, "si", $updatedUname, $id);
                mysqli_stmt_execute($stmt_check_username);
                mysqli_stmt_store_result($stmt_check_username);
    
                if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
                    // Email already exists, show error message
                    echo "<div class='alert alert-danger text-right text-white'>الإيميل موجود بالفعل، يرجى إدخال إيميل آخر.</div>";
                    $email = $updatedEmail; // Update the displayed email
                } elseif (mysqli_stmt_num_rows($stmt_check_username) > 0) {
                    // Username already exists, show error message
                    echo "<div class='alert alert-danger text-right text-white'>إسم المستخدم موجود بالفعل، يرجى إدخال إسم مستخدم آخر.</div>";
                    $username = $updatedUname; // Update the displayed username
                } else {
                    // Update the user record without changing the password
                    $sql_update_user = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
                    $stmt_update_user = mysqli_prepare($conn, $sql_update_user);
                    mysqli_stmt_bind_param($stmt_update_user, "sssi", $updatedUname, $updatedEmail, $updatedUserType, $id);
                    
                    if (mysqli_stmt_execute($stmt_update_user)) {
                        $_SESSION['create_update_success'] = true;
                        header("Location: display_users.php");
                        exit();
                    } else {
                        echo "<div class='alert alert-danger text-right text-white'>حدث خطأ أثناء تحديث المعلومات .</div>";
                    }
                    
                    // Close the statement
                    mysqli_stmt_close($stmt_update_user);
                }
    
                // Close statements
                mysqli_stmt_close($stmt_check_email);
                mysqli_stmt_close($stmt_check_username);
            }
        }
    }
        ?>
                <form role="form" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
                        <h4 class="mb-3">إضافة أدمن</h4>
                  
                    <div class="form-group">
                      <label class="form-label">إسم المستخدم</label>
                      <input type="text" name="txt_uname" class="form-control border pe-2" value="<?php echo htmlspecialchars($username); ?>">
                    </div>

                    <div class="form-group">
                      <label class="form-label">الإيميل</label>
                      <input type="email" name="txt_email" class="form-control border pe-2" value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="form-group">
                    <label class="form-label">كلمة السر</label>
                    <input type="password" name="txt_password" class="form-control border pe-2">
                   </div>
                    <div class="form-group">
                        <label class="form-label">تأكيد كلة السر</label>
                        <input type="password" name="txt_confirm_password" class="form-control border pe-2">
                    </div>
                    <div class="form-group">
                    <label class="form-label">دور المستخدم</label>
                    <select name="user_type" class="form-control border pe-2" required>
                        <option value="" disabled> -- إختر دور المستخدم -- </option>
                        <option value="admin" <?php if ($role === 'admin') echo 'selected'; ?>>أدمن</option>
                        <option value="member" <?php if ($role === 'member') echo 'selected'; ?>>مشرف</option>
                    </select>
                    </div>

                    <div class="form-group mt-3">
                  <button type="submit" name="updateData" class="btn btn-primary">تحديث</button>
              </div>

                  </form>
                  <hr>
          <a href="display_users.php" class="btn btn-secondary">العودة إلى قائمة العناصر</a>
<?php
     // Close the database connection
     mysqli_close($conn);
include('footer.php');
ob_end_flush();
?>