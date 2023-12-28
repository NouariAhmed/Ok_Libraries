<?php
session_start(); 
if (isset($_COOKIE['remember_username']) && $_COOKIE['remember_password']) {
  // Use the values from cookies to pre-fill the form fields
  $cookUname = $_COOKIE['remember_username'];
  $cookPwd = $_COOKIE['remember_password'];
}else {
  $cookUname="";
  $cookPwd="";
}
// Check if the registration success message exists in the session
if (isset($_SESSION['register_success_msg'])) {
    $register_success_msg = $_SESSION['register_success_msg'];
    unset($_SESSION['register_success_msg']); // Remove the message from the session
}

// Initialize variables
$uname = "";
$uname_err = "";
$pwd_err = "";
$login_err = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $uname = trim($_POST["txt_uname"]);
    $pwd = trim($_POST["txt_pwd"]);
    

    // Validate username
    if (empty($uname)) {
        $uname_err = "يرجى إدخال إسم المستخدم أو كلمة المرور";
    }

    // Validate password
    if (empty($pwd)) {
        $pwd_err = "يرجى إدخال كلمة المرور.";
    }

// If there are no errors, proceed with login
if (empty($uname_err) && empty($pwd_err)) {
    // Create a database connection
    include('connect.php');
    // Check if the input matches either the email or username in the database
    $sql_check_user = "SELECT id, username, email, pass, role FROM users WHERE email = ? OR username = ?";
    $stmt_check_user = mysqli_prepare($conn, $sql_check_user);
    mysqli_stmt_bind_param($stmt_check_user, "ss", $uname, $uname);
    mysqli_stmt_execute($stmt_check_user);
    mysqli_stmt_store_result($stmt_check_user);

    if (mysqli_stmt_num_rows($stmt_check_user) == 1) {
        // Bind the result to variables
        mysqli_stmt_bind_result($stmt_check_user, $id, $username, $email, $hashed_password, $role);
        mysqli_stmt_fetch($stmt_check_user);

        // Verify the password
        if (password_verify($pwd, $hashed_password)) {
            // Password is correct, create a session and redirect to the appropriate page
            session_start();
            $_SESSION["id"] = $id;
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;
            $_SESSION['showWelcomeMessage'] = true;    
                            // Check if "Remember Me" is checked
                if (isset($_POST['rememberMe'])) {
                    // Set cookies for username and password
                    setcookie('remember_username', $uname, time() + (10 * 365 * 24 * 60 * 60), '/');
                    setcookie('remember_password', $pwd, time() + (10 * 365 * 24 * 60 * 60), '/');
                }else {
                    setcookie('remember_username', '', time() - (10 * 365 * 24 * 60 * 60), '/');
                    setcookie('remember_password', '', time() - (10 * 365 * 24 * 60 * 60), '/');
                }  
                // Admin user, redirect to dashboard
                header("Location: admin/index.php");

            exit();
        } else {
            // Password is incorrect
            $login_err = "إسم المستخدم /الإيميل أو كلمة المرور غير صحيحة.";
        }
    } else {
        // User does not exist
        $login_err = "إسم المستخدم /الإيميل أو كلمة المرور غير صحيحة";
    }

    // Close the statement
    mysqli_stmt_close($stmt_check_user);

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
   Ok login
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/droid-arabic-kufi" type="text/css"/>

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
<body class="bg-gray-200">
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-secondary shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">تسجيل الدخول</h4>
                  <h6 class="text-white text-center mt-2 mb-0">منصة المكتبات</h6>
                </div>
              </div>
              <div class="card-body">
                <form role="form" method="post" action="">
                  <div class="input-group input-group-outline my-3" dir="rtl">
                    <label class="form-label">إسم المستخدم/الإيميل</label>
                    <input type="text" class="form-control <?php echo (!empty($uname_err)) ? 'is-invalid' : ''; ?>" id="username" name="txt_uname" value="<?php echo $cookUname; ?>" />
                    <span class="invalid-feedback"><?php echo $uname_err; ?></span>
                  </div>
                  <div class="input-group input-group-outline mb-3" dir="rtl">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" class="form-control <?php echo (!empty($pwd_err)) ? 'is-invalid' : ''; ?>" id="password" name="txt_pwd" value="<?php echo $cookPwd; ?>"/>
                    <span class="invalid-feedback"><?php echo $pwd_err; ?></span>
                  </div>
                  <div class="form-check form-switch d-flex align-items-center mb-3" dir="rtl">
                  <label class="form-check-label mb-0 ms-2" for="rememberMe">تذكرني</label>
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-info w-100 my-4 mb-2" name="but_submit">تسجيل الدخول</button>
                  </div>
                  <p class="mt-4 text-sm text-center">
                   لا تملك حسابا بعد؟
                    <a href="register.php" class="text-primary text-gradient font-weight-bold">إنشاء حساب</a>
                  </p>
                  <?php if (!empty($login_err)) { ?>
                <div class="alert alert-danger mt-3 text-right text-white" role="alert" dir="rtl">
                  <?php echo $login_err; ?>
                </div>
              <?php } ?>
              <?php if (isset($register_success_msg)) { ?>
                <div class="alert alert-success mt-3 text-right text-white" role="alert" dir="rtl">
                  <?php echo $register_success_msg; ?>
                </div>
              <?php } ?>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="assets/js/material-dashboard.min.js?v=3.0.0"></script>
</body>
</html>

