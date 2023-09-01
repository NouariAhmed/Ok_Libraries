<?php
// Initialize variables
$uname = $email = $pwd = $confirm_pwd = "";
$uname_err = $email_err = $pwd_err = $confirm_pwd_err = $register_err = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the form data
  $uname = trim($_POST["txt_uname"]);
  $email = trim($_POST["txt_email"]);
  $pwd = trim($_POST["txt_pwd"]);
  $confirm_pwd = trim($_POST["txt_confirm_pwd"]);

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
    $pwd_err = "يرجى إدخال كلمة المرور.";
  } elseif (strlen($pwd) < 6) {
    $pwd_err = "كلمة المرور يجب أن تحتوي على الأقل 6 أحرف";
  }

  // Validate confirm password
  if (empty($confirm_pwd)) {
    $confirm_pwd_err = "يرجى تأكيد كلمة المرور.";
  } elseif ($pwd !== $confirm_pwd) {
    $confirm_pwd_err = "كلمتا المرور غير متطابقتين.";
  }

// If there are no errors, proceed with registration
if (empty($uname_err) && empty($email_err) && empty($pwd_err)&& empty($confirm_pwd_err)) {
    // Create a database connection
    include('connect.php');

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
        $sql_insert_user = "INSERT INTO users (username, email, pass) VALUES (?, ?, ?)";
        $stmt_insert_user = mysqli_prepare($conn, $sql_insert_user);
        // Hash the password before storing it in the database
        $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt_insert_user, "sss", $uname, $email, $hashed_pwd);
        mysqli_stmt_execute($stmt_insert_user);
        // Registration successful, show success message
        $register_success_msg = "تم التسجيل بنجاح، يمكنك تسجيل الدخول الآن";

        // Store the success message in a session variable
        session_start();
        $_SESSION['register_success_msg'] = $register_success_msg;
        // Registration successful, redirect to login page or dashboard
        header("Location: login.php");
        exit();
    }

    // Close the connection
    mysqli_close($conn);
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>
    register Ok
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
</head>

<body>
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image: url('assets/img/illustrations/sing-up.jpg'); background-size: cover;">
              </div>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
              <div class="card card-plain" dir="rtl">
                <div class="card-header">
                  <h4 class="font-weight-bolder">إنشاء حساب</h4>
                  <p class="mb-0">أدخل اسم المستخدم/الإيميل وكلمة المرور لإنشاء حساب</p>
                </div>
                <div class="card-body">
                  <form role="form" method="post" action="">
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">إسم المستخدم</label>
                      <input type="text" class="form-control <?php echo (!empty($uname_err)) ? 'is-invalid' : ''; ?>"
                        id="username" name="txt_uname" value="<?php echo $uname; ?>" />
                      <span class="invalid-feedback"><?php echo $uname_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">الإيميل</label>
                      <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                        id="email" name="txt_email" value="<?php echo $email; ?>" />
                      <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">كلمة المرور</label>
                      <input type="password" class="form-control <?php echo (!empty($pwd_err)) ? 'is-invalid' : ''; ?>"
                        id="password" name="txt_pwd" />
                      <span class="invalid-feedback"><?php echo $pwd_err; ?></span>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">تأكيد كلمة المرور</label>
                      <input type="password" class="form-control <?php echo (!empty($confirm_pwd_err)) ? 'is-invalid' : ''; ?>" id="confirm_pwd"
                      name="txt_confirm_pwd" />
                    <span class="invalid-feedback"><?php echo $confirm_pwd_err; ?></span>
                    </div>
                    <div class="form-check form-check-info text-start ps-0">
                      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
                      <label class="form-check-label" for="flexCheckDefault">
                        I agree the <a href="javascript:;" class="text-dark font-weight-bolder">Terms and Conditions</a>
                      </label>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn btn-lg bg-gradient-primary btn-lg w-100 mt-4 mb-0" name="but_submit">إنشـاء</button>
                    </div>
                    <?php if (!empty($register_err)) { ?>
                <div class="alert alert-danger mt-3" role="alert">
                  <?php echo $register_err; ?>
                </div>
              <?php } ?>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-2 text-sm mx-auto">
                   لديك حساب بالفعل ؟
                    <a href="login.php" class="text-primary text-gradient font-weight-bold">تسجيل الدخول</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!--   Core JS Files   -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script src="assets/js/material-dashboard.min.js?v=3.0.0"></script>
</body>

</html>







