<?php
session_start();
include('secure.php');
include('../connect.php');
// Initialize variables
$uname = $email = $pwd = $confirm_pwd = $user_type= "";
$uname_err = $email_err = $pwd_err = $confirm_pwd_err = $user_type_err = "";

$table = "users";

$itemsPerPage = 10; // Number of items per page

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;

// Get the total number of items in the database
$sql = "SELECT COUNT(*) AS total_items FROM $table";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$totalItems = $row['total_items'];

$totalPages = ceil($totalItems / $itemsPerPage);
$currentPage = max(1, min($currentPage, $totalPages));

if (!is_numeric($_GET['page']) || $_GET['page'] <= 0) {
    header("Location: ?page=1");
    exit;
}

if ($currentPage > $totalPages) {    
    header("Location: ?page=$totalPages");
    exit;
}
$startIndex = ($currentPage - 1) * $itemsPerPage;
// Retrieve items for the current page
$result = mysqli_query($conn, "SELECT * FROM users LIMIT $startIndex, $itemsPerPage");
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);





// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
  // Get the form data
  $uname = trim($_POST["txt_uname"]);
  $email = trim($_POST["txt_email"]);
  $pwd = trim($_POST["txt_pwd"]);
  $confirm_pwd = trim($_POST["txt_confirm_pwd"]);
  $user_type = trim($_POST["user_type"]);

  // Validate username
  if (empty($uname)) {
    $uname_err = "يرجى إدخال اسم المستخدم.";
  }elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $uname)) {
    $uname_err = "إسم المستخدم يجب أن يحتوى فقط على حروف،أرقام،مطات.";
}

  // Validate email
  if (empty($email)) {
    $email_err = "يرجى إدخال الإيميل.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email_err = "نوع الإيميل غير صالح.";
  }

  // Validate password
  if (empty($pwd)) {
    $pwd_err = "يرجى إدخال كلمة السر.";
  } elseif (strlen($pwd) < 6) {
    $pwd_err = "كلمة السر يجب أن تحتوي على الأقل 6 أحرف.";
  }

  // Validate confirm password
  if (empty($confirm_pwd)) {
    $confirm_pwd_err = "يرجى تأكيد كلمة المرور.";
  } elseif ($pwd !== $confirm_pwd) {
    $confirm_pwd_err = "كلمتا المرور غير متطابقتين.";
  }
  // Validate User Type
  if (empty($user_type)) {
    $user_type_err = "يرجى إختيار نوع المستخدم.";
  }
  
// If there are no errors, proceed with registration
if (empty($uname_err) && empty($email_err) && empty($pwd_err)&& empty($confirm_pwd_err)&& empty($user_type_err)) {
    // Create a database connection
    include('../connect.php');

    // Check if the email already exists in the database
    $sql_check_email = "SELECT id FROM users WHERE email = ?";
    $stmt_check_email = mysqli_prepare($conn, $sql_check_email);
    mysqli_stmt_bind_param($stmt_check_email, "s", $email);
    mysqli_stmt_execute($stmt_check_email);
    mysqli_stmt_store_result($stmt_check_email);

    // Check if the username already exists in the database
    $sql_check_username = "SELECT id FROM users WHERE username = ?";
    $stmt_check_username = mysqli_prepare($conn, $sql_check_username);
    mysqli_stmt_bind_param($stmt_check_username, "s", $uname);
    mysqli_stmt_execute($stmt_check_username);
    mysqli_stmt_store_result($stmt_check_username);

    if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
        // Email already exists, show error message
        $email_err = "الإيميل موجود بالفعل، يرجى إدخال إيميل آخر.";
    } elseif (mysqli_stmt_num_rows($stmt_check_username) > 0) {
        // Username already exists, show error message
        $uname_err = "إسم المستخدم موجود بالفعل، يرجى إدخال إسم مستخدم آخر.";
    } else {
       // Insert the new user record into the database
        $sql_insert_user = "INSERT INTO users (username, email, pass, role) VALUES (?, ?, ?, ?)";
        $stmt_insert_user = mysqli_prepare($conn, $sql_insert_user);

        // Hash the password before storing it in the database
        $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);

        // Modify this line to include the user type parameter
        mysqli_stmt_bind_param($stmt_insert_user, "ssss", $uname, $email, $hashed_pwd, $user_type);

        mysqli_stmt_execute($stmt_insert_user);
      
        session_start();
        $_SESSION['create_update_success'] = true;
        header("Location: display_users.php");
        exit();
    }

    // Close the connection
    mysqli_close($conn);
}
}
}
include('header.php');
?>
            <div class="container-fluid py-4">
                <?php
                // Check if create_update_success session variable is set
                    if (isset($_SESSION['create_update_success']) && $_SESSION['create_update_success'] === true) {
                        echo '<div class="alert alert-success text-right text-white">تم إنشاء/تحديث العنصر بنجاح.</div>';
                        // Unset the session variable to avoid displaying the message on page refresh
                        unset($_SESSION['create_update_success']);
                    }
                    // Check if delete_success session variable is set
                    if (isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true) {
                        echo '<div class="alert alert-success text-right text-white">تم حذف العنصر بنجاح.</div>';
                        // Unset the session variable to avoid displaying the message on page refresh
                        unset($_SESSION['delete_success']);
                    }
                    // Check if item_not_found session variable is set
                    if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
                        echo '<div class="alert alert-danger text-right text-white">العنصر غير موجود.</div>';
                        // Unset the session variable to avoid displaying the message on page refresh
                        unset($_SESSION['item_not_found']);
                    }
                    ?>

                  <form role="form" method="post" action="">
                  <h4 class="mb-3">إضافة أدمن</h4>
                  
                    <div class="input-group input-group-outline my-3">
                      <label class="form-label">إسم المستخدم</label>
                      <input type="text" class="form-control <?php echo (!empty($uname_err)) ? 'is-invalid' : ''; ?>"
                        id="username" name="txt_uname" value="<?php echo $uname; ?>" />
                      <span class="invalid-feedback"><?php echo $uname_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline my-3">
                      <label class="form-label">الإيميل</label>
                      <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                        id="email" name="txt_email" value="<?php echo $email; ?>" />
                      <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline my-3">
                      <label class="form-label">كلمة السر</label>
                      <input type="password" class="form-control <?php echo (!empty($pwd_err)) ? 'is-invalid' : ''; ?>"
                        id="password" name="txt_pwd" />
                      <span class="invalid-feedback"><?php echo $pwd_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline my-3">
                      <label class="form-label">تأكيد كلمة السر</label>
                      <input type="password" class="form-control <?php echo (!empty($confirm_pwd_err)) ? 'is-invalid' : ''; ?>" id="confirm_pwd"
                      name="txt_confirm_pwd" />
                    <span class="invalid-feedback"><?php echo $confirm_pwd_err; ?></span>
                    </div>

                    <div class="input-group input-group-outline my-3">
                    <select name="user_type" class="form-control" required>
                    <option value="" disabled selected> -- اختر دور المستخدم -- </option>
                    <option value="Admin">أدمن</option>
                    <option value="member">عضو</option>
                   </select>
                    </div>
                                   
                    <button type="submit" name="submit" class="btn bg-gradient-primary" >إضـافة</button>
                  </form>

            <div class="row">
                <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize pe-3">جدول المستخدمين</h6>
                    </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                            <th class="text-secondary text-lg font-weight-bolder opacity-7" >المعرف</th>
                            <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الإسم</th>
                            <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الإيميل</th>
                            <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الدور</th>
                            <th class="text-center text-secondary text-lg font-weight-bolder opacity-7">الإجراءات</th>
                            
                            <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($items as $item) {
                            $id = htmlspecialchars($item["id"]);
                            $username = htmlspecialchars($item["username"]);
                            $email = htmlspecialchars($item["email"]);
                            $role = htmlspecialchars($item["role"]);
                            ?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                           <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm pe-3"><?php echo $id;?></h6>
                            </div>
                        </div>
                      </td>
                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php echo $username;?></h6>
                      </td>

                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php echo $email;?></h6>
                      </td>

                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php echo $role;?></h6>
                      </td>
                      <td class="align-middle text-center">
                         <a href="update_user.php?id=<?php echo $id;?>" class="btn badge-sm bg-gradient-primary"> <i class="material-icons-round align-middle" style="font-size: 18px;">edit</i></a>
                        <a href="delete_user.php?id=<?php echo $id;?>" class="btn badge-sm bg-gradient-danger"> <i class="material-icons-round align-middle" style="font-size: 18px;">delete</i></a>
                      </td>
                    </tr>
                    <?php
                }
                ?>
                  </tbody>
                </table>
                  <?php
                include('../pagination.php');
                 mysqli_close($conn);
                  ?>
              </div>
            </div>
          </div>
        </div>
      </div>
<?php
include('footer.php');
?>

          







