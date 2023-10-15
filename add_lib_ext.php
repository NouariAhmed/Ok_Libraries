<?php
include('connect.php');
// Initialize variables
$library_name = $lib_owner_name = $library_last_name = $library_type_id = $address = $phone = $second_phone = $student_phone = $email = $fbLink = $instaLink = $mapAddress = $websiteLink = $notes = $state = $province = $city = "";
$library_name_err = $lib_owner_name_err = $address_err = $phone_err = $second_phone_err = $student_phone_err = $email_err = $state_err = $province_err = $city_err = $file_err = $register_err = "";

// Fetch states from the database
$sql_fetch_states = "SELECT DISTINCT states FROM locations";
$result_states = mysqli_query($conn, $sql_fetch_states);

// Fetch provinces based on selected state
if (isset($_POST['selected_state'])) {
    $selected_state = $_POST['selected_state'];
    $sql_fetch_provinces = "SELECT DISTINCT provinces FROM locations WHERE states = '$selected_state'";
    $result_provinces = mysqli_query($conn, $sql_fetch_provinces);
}

// Fetch cities based on selected province
if (isset($_POST['selected_province'])) {
    $selected_province = $_POST['selected_province'];
    $sql_fetch_cities = "SELECT DISTINCT cities FROM locations WHERE provinces = '$selected_province'";
    $result_cities = mysqli_query($conn, $sql_fetch_cities);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the form data
  $library_name = trim($_POST["library_name"]);
  $lib_owner_name = trim($_POST["lib_owner_name"]);
  $library_last_name = trim($_POST["library_last_name"]);
  $library_type_id = trim($_POST["library_type"]);
  $address = trim($_POST["address"]);
  $phone = trim($_POST["phone"]);
  $second_phone = trim($_POST["second_phone"]);
  $student_phone = trim($_POST["student_phone"]);
  $email = trim($_POST["email"]);
  $notes = trim($_POST["notes"]);

  $fbLink = trim($_POST["fbLink"]);
  $instaLink = trim($_POST["instaLink"]);
  $mapAddress = trim($_POST["mapAddress"]);
  $websiteLink = trim($_POST["websiteLink"]);

  $state = $_POST["state"];
  $province = $_POST["province"];
  $city = $_POST["city"];

  if (isset($_POST['firstCheckbox'])) {
    $firstCheckboxValue = $_POST['firstCheckbox'];
} else {
    $firstCheckboxValue = 'مكتبة فقط'; // Set a default value if not checked
}
if (isset($_POST['secondCheckbox'])) {
    $secondCheckboxValue = $_POST['secondCheckbox'];
} else {
    $secondCheckboxValue = 'لا يعمل أونلاين'; // Set a default value if not checked
}

if (isset($_POST['thirdCheckbox'])) {
    $thirdCheckboxValue = $_POST['thirdCheckbox'];
} else {
    $thirdCheckboxValue = 'ليس دار نشر'; // Set a default value if not checked
}

if (isset($_POST['fourthCheckbox'])) {
    $fourthCheckboxValue = $_POST['fourthCheckbox'];
} else {
    $fourthCheckboxValue = 'ليس لديه عتاد الإعلام آلي'; // Set a default value if not checked
}

if (isset($_POST['fifthCheckbox'])) {
    $fifthCheckboxValue = $_POST['fifthCheckbox'];
} else {
    $fifthCheckboxValue = 'لا يملك خدمة الطباعة'; // Set a default value if not checked
}

   // Validate library name
    if (empty($library_name)) {
        $library_name_err = "يرجى إدخال اسم المكتبة.";
    } elseif (!preg_match("/^[\p{L}\p{N}_\s]+$/u", $library_name)) {
        $library_name_err = "اسم المكتبة يجب أن يحتوي على حروف.";
    }
  // Validate address
  if (empty($address)) {
    $address_err = "يرجى إدخال عنوان المكتبة.";
  }

  $phonePattern = "/^\+?\d{1,4}?\s?\(?\d{1,4}?\)?[0-9\- ]+$/";

    // Validate primary phone
    if (!empty($phone) && (!preg_match($phonePattern, $phone) || strlen($phone) > 10)) {
        $phone_err = "رقم هاتف غير صالح.";
    } else {
        // Check if phone number already exists in the database (in phone, second_phone, or student_phone column)
        $existingPhoneQuery = "SELECT id, library_name FROM ext_libraries WHERE phone = ? OR second_phone = ? OR student_phone = ?";
        $stmt_existingPhone = mysqli_prepare($conn, $existingPhoneQuery);
        mysqli_stmt_bind_param($stmt_existingPhone, "sss", $phone, $phone, $phone);
        mysqli_stmt_execute($stmt_existingPhone);
        mysqli_stmt_store_result($stmt_existingPhone);
        if (mysqli_stmt_num_rows($stmt_existingPhone) > 0) {
            mysqli_stmt_bind_result($stmt_existingPhone, $existingLibaryId, $existingLibraryName);
            mysqli_stmt_fetch($stmt_existingPhone);
            $phone_err = "رقم الهاتف مستخدم بالفعل مع مكتبة: $existingLibraryName (رقم المكتبة: $existingLibaryId)";
        }
        mysqli_stmt_close($stmt_existingPhone);
    }

    // Validate secondary phone
    if (!empty($second_phone) && (!preg_match($phonePattern, $second_phone) || strlen($second_phone) > 10)) {
        $second_phone_err = "رقم هاتف ثانوي غير صالح.";
    } else {
        // Check if secondary phone number already exists in the database (in phone, second_phone, or student_phone column)
        if (!empty($second_phone)) {
            $existingSecondPhoneQuery = "SELECT id, library_name FROM ext_libraries WHERE phone = ? OR second_phone = ? OR student_phone = ?";
            $stmt_existingSecondPhone = mysqli_prepare($conn, $existingSecondPhoneQuery);
            mysqli_stmt_bind_param($stmt_existingSecondPhone, "sss", $second_phone, $second_phone, $second_phone);
            mysqli_stmt_execute($stmt_existingSecondPhone);
            mysqli_stmt_store_result($stmt_existingSecondPhone);
            if (mysqli_stmt_num_rows($stmt_existingSecondPhone) > 0) {
                mysqli_stmt_bind_result($stmt_existingSecondPhone, $existingLibaryId, $existingLibraryName);
                mysqli_stmt_fetch($stmt_existingSecondPhone);
                $second_phone_err = "رقم الهاتف الثانوي مستخدم بالفعل مع مكتبة: $existingLibraryName (رقم المكتبة: $existingLibaryId)";
            }
            mysqli_stmt_close($stmt_existingSecondPhone);
        }
    }

    // Validate student phone
    if (!empty($student_phone) && (!preg_match($phonePattern, $student_phone) || strlen($student_phone) > 10)) {
        $student_phone_err = "رقم الهاتف للتلاميذ غير صالح.";
    } else {
        // Check if student phone number already exists in the database (in phone, second_phone, or student_phone column)
        if (!empty($student_phone)) {
            $existingStudentPhoneQuery = "SELECT id, library_name FROM ext_libraries WHERE phone = ? OR second_phone = ? OR student_phone = ?";
            $stmt_existingStudentPhone = mysqli_prepare($conn, $existingStudentPhoneQuery);
            mysqli_stmt_bind_param($stmt_existingStudentPhone, "sss", $student_phone, $student_phone, $student_phone);
            mysqli_stmt_execute($stmt_existingStudentPhone);
            mysqli_stmt_store_result($stmt_existingStudentPhone);
            if (mysqli_stmt_num_rows($stmt_existingStudentPhone) > 0) {
                mysqli_stmt_bind_result($stmt_existingStudentPhone, $existingLibaryId, $existingLibraryName);
                mysqli_stmt_fetch($stmt_existingStudentPhone);
                $student_phone_err = "رقم هاتف التلاميذ مستخدم بالفعل مع مكتبة: $existingLibraryName (رقم المكتبة: $existingLibaryId)";
            }
            mysqli_stmt_close($stmt_existingStudentPhone);
        }
    }
  // Validate email
  if (!empty($email)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "يرجى إدخال عنوان إيميل صالح.";
    } 
    }

    // Validate state, province, and city selections
    if (empty($state)) {
      $state_err = "يرجى اختيار الولاية.";
  }
  if (empty($province)) {
      $province_err = "يرجى اختيار الدائرة.";
  }
  if (empty($city)) {
      $city_err = "يرجى اختيار البلدية.";
  }

 // Validate File
    // Check if a file is uploaded
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
          // Check if the file is an image or PDF
          $file = $_FILES['uploadedFile'];
          $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
          if (!in_array($file['type'], $allowedTypes)) {
          
              $file_err = "نوع غير صحيح، الأنواع المقبولة: JPEG, PNG, GIF, PDF.";
          }

          // Check file size (max 5MB)
          $maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes
          if ($file['size'] > $maxFileSize) {
              $file_err = "يجب أن لا يتجاوز حجم الملف (5 MB).";
          }
  }

  // If there are no errors, proceed with registration
  if (empty($library_name_err) && empty($lib_owner_name_err) && empty($address_err) && empty($phone_err) && empty($second_phone_err) && empty($student_phone_err) && empty($email_err) && empty($state_err) && empty($province_err) && empty($city_err)  && empty($file_err)) {
    include('connect.php');
    session_start();
        // Get location_id based on state, province, and city
        $location_query = "SELECT location_id FROM locations WHERE states = ? AND provinces = ? AND cities = ?";
        $stmt_location = mysqli_prepare($conn, $location_query);
        mysqli_stmt_bind_param($stmt_location, "sss", $state, $province, $city);
        mysqli_stmt_execute($stmt_location);
        mysqli_stmt_bind_result($stmt_location, $location_id);
        mysqli_stmt_fetch($stmt_location);
        mysqli_stmt_close($stmt_location);

        $uploadDirectory = "sila_commercial_photos/"; 
        // Create the directory if it does not exist
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        $uploadedFile = '';
        if (!empty($_FILES['uploadedFile']['name'])) {
        // Generate a unique filename
        $uniqueFileName = uniqid() . "_" . basename($_FILES['uploadedFile']['name']);
        $uploadedFile = $uploadDirectory . $uniqueFileName;
        // Get the file type from the uploaded file
        $fileType = $_FILES['uploadedFile']['type'];
        // Move the uploaded file to the destination directory
        move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $uploadedFile);
        }
        // Insert the library data into the database
        mysqli_query($conn, "SET time_zone = '+01:00'");

        $insert_query = "INSERT INTO ext_libraries (library_name, lib_owner_name, library_last_name, address, phone, second_phone, student_phone, email, fbLink, instaLink, mapAddress, websiteLink, created_at, notes,  userfile, filetype, firstCheckbox, secondCheckbox, thirdCheckbox, fourthCheckbox, fifthCheckbox, location_id, library_type_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssii", $library_name, $lib_owner_name, $library_last_name, $address, $phone, $second_phone, $student_phone, $email, $fbLink, $instaLink, $mapAddress, $websiteLink, $notes, $uploadedFile, $fileType, $firstCheckboxValue, $secondCheckboxValue, $thirdCheckboxValue, $fourthCheckboxValue, $fifthCheckboxValue, $location_id, $library_type_id);
        
        mysqli_stmt_execute($stmt);

    // Store the success message in a session variable
    $_SESSION['register_success_msg'] = "تم إضافة المكتبة بنجاح.";
    // Registration successful, redirect to login page or dashboard
    header("Location: add_lib_ext.php");
    exit();
    mysqli_stmt_close($stmt_insert_user);
    // Close the connection
    mysqli_close($conn);
  }
}
session_start();
$register_success_msg = isset($_SESSION['register_success_msg']) ? $_SESSION['register_success_msg'] : "";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel = "icon" href = "assets/img/ok-logo.png" type = "image/x-icon">
	<meta name="description" content="قائمة ومعلومات المكتبات - دار النشر مكتبة عكاشة">
  <meta name="keywords" content="authors okacha">
  <meta name="author" content="Okacha programming">
	<meta property="og:title" content="قائمة ومعلومات المكتبات - دار النشر مكتبة عكاشة">
  <meta property="og:description" content="إضافة ودراسة المكتبات لدار النشر مكتبة عكاشة">
  <title>
   Okacha Libraries
  </title>
  <!--     jquery     -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!--     Fonts and icons     -->
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
  <!-- Pure JavaScript Modal Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pure-js-modal@1.0.0/dist/modal.css">
<script src="https://cdn.jsdelivr.net/npm/pure-js-modal@1.0.0/dist/modal.js"></script>
<style>
        .progress-container {
            height: 30px;
            background-color: #f5f5f5;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            background-color: #4caf50;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            position: relative;
        }

        .progress-text {
            position: absolute;
        }
    </style>
</head>

<body class="g-sidenav-show rtl bg-gray-200">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-end me-3 rotate-caret  bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute start-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="admin/index.php">
        <img src="assets/img/ok-logo.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="me-1 font-weight-bold text-white">لوحة التحكم</span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse px-0 w-auto  max-height-vh-100" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="add_lib_ext.php">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">add</i>
            </div>
            <span class="nav-link-text me-1">إضافة مكتبة</span>
          </a>
        </li>
        <?php $currentDate = date("Y-m-d");?>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
      <div class="mx-3">
      <p class="text-white text-center mb-2 fs-4" id="currentDateTime"></p>
      </div>
     </div>
        
  </aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg overflow-x-hidden">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <div class="collapse navbar-collapse mt-sm-0 mt-2 px-0" id="navbar">
          <ul class="navbar-nav me-auto ms-0 justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="logout.php" class="nav-link text-body font-weight-bold px-0" data-bs-toggle="modal" data-bs-target="#logOutModal">
                <i class="fa fa-user me-sm-1"></i>
                <span class="d-sm-inline d-none">تسجيل الخروج</span>
              </a>
            </li>
            <li class="nav-item d-xl-none pe-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0">
                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
              </a>
            </li>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->

    <div class="container-fluid py-4">
          <!-- Display the flash message if it exists -->
<?php if (isset($_SESSION['register_success_msg'])) { ?>
    <div class="progress-container">
        <div class="progress-bar" id="myProgressBar">
            <div class="progress-text">يتم إضافة المكتبة</div>
        </div>
    </div>
    <div class="alert alert-success mt-3 text-white" role="alert" id="successMessage" style="display: none;">
        <?php echo $_SESSION['register_success_msg']; ?>
    </div>
<?php unset($_SESSION['register_success_msg']); }  ?>

        <form role="form" action="" method="post" enctype="multipart/form-data">
        <h4 class="mb-3">إضافة مكتبة</h4>

            <div class="border rounded p-4 shadow">
                <h6 class="border-bottom pb-2 mb-3">معلومات المكتبة</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group input-group-outline m-2">
                            <select class="form-control" id="library_type" name="library_type" required>
                                <option value="" disabled selected>-- اختر نوع المكتبة * --</option>
                                <?php
                                // Fetch library types from the database
                                $sql_fetch_library_types = "SELECT * FROM library_types";
                                $result_library_types = mysqli_query($conn, $sql_fetch_library_types);
                                while ($row = mysqli_fetch_assoc($result_library_types)) {
                                    echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['library_type']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-outline m-2">
                            <?php if (empty($lib_owner_name)): ?>
                                <label for="lib_owner_name" class="form-label"> إسم صاحب المكتبة * </label>
                            <?php endif; ?>
                            <input type="text" class="form-control <?php echo (!empty($lib_owner_name_err)) ? 'is-invalid' : ''; ?>"
                                id="lib_owner_name" name="lib_owner_name" value="<?php echo $lib_owner_name; ?>" required
                                <?php if (!empty($lib_owner_name)) echo 'placeholder="اسم المكتبة"'; ?> />
                            <span class="invalid-feedback"><?php echo $lib_owner_name_err; ?></span>
                        </div>
                    </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($library_name)): ?>
                            <label for="library_name" class="form-label">اسم المكتبة * </label>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo (!empty($library_name_err)) ? 'is-invalid' : ''; ?>"
                            id="library_name" name="library_name" value="<?php echo $library_name; ?>" required
                            <?php if (!empty($library_name)) echo 'placeholder="اسم المكتبة"'; ?> />
                        <span class="invalid-feedback"><?php echo $library_name_err; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($library_last_name)): ?>
                            <label for="library_last_name" class="form-label">اسم شهرة المكتبة</label>
                        <?php endif; ?>
                        <input type="text" class="form-control"
                            id="library_last_name" name="library_last_name" value="<?php echo $library_last_name; ?>"
                            <?php if (!empty($library_last_name)) echo 'placeholder="اسم شهرة المكتبة"'; ?> />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($phone)): ?>
                            <label for="phone" class="form-label">هاتف المكتبة * </label>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>"
                            id="phone" name="phone" value="<?php echo $phone; ?>" required
                            <?php if (!empty($phone)) echo 'placeholder="هاتف المكتبة"'; ?> />
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>
                </div>   
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($second_phone)): ?>
                            <label for="second_phone" class="form-label">الهاتف الثاني</label>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo (!empty($second_phone_err)) ? 'is-invalid' : ''; ?>"
                            id="second_phone" name="second_phone" value="<?php echo $second_phone; ?>"
                            <?php if (!empty($second_phone)) echo 'placeholder="الهاتف الثاني"'; ?> />
                        <span class="invalid-feedback"><?php echo $second_phone_err; ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($student_phone)): ?>
                            <label for="student_phone" class="form-label">الهاتف الخاص بالتلاميذ</label>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo (!empty($student_phone_err)) ? 'is-invalid' : ''; ?>"
                            id="student_phone" name="student_phone" value="<?php echo $student_phone; ?>"
                            <?php if (!empty($student_phone)) echo 'placeholder="الهاتف الخاص بالتلاميذ"'; ?> />
                        <span class="invalid-feedback"><?php echo $student_phone_err; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($email)): ?>
                            <label for="email" class="form-label">الإيميل</label>
                        <?php endif; ?>
                        <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>"
                            id="email" name="email" value="<?php echo $email; ?>"
                            <?php if (!empty($email)) echo 'placeholder="الإيميل"'; ?> />
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>
            </div>

            <div class="d-flex">
                <div class="form-check col-md-6 me-3 mt-3">
                    <input class="form-check-input" type="checkbox" value="مكتبة ووراقة" id="fcustomCheck1" name="firstCheckbox">
                    <label class="custom-control-label" for="customCheck1">مكتبة ووراقة</label>
                </div>

                <div class="form-check col-md-6 mt-3">
                    <input class="form-check-input" type="checkbox" value="يعمل أونلاين" id="fcustomCheck2" name="secondCheckbox">
                    <label class="custom-control-label" for="customCheck2">تعمل أونلاين</label>
                </div>
            </div>

            <div class="d-flex">
                <div class="form-check col-md-6 me-3 mt-3">
                    <input class="form-check-input" type="checkbox" value="دار نشر" id="fcustomCheck3" name="thirdCheckbox">
                    <label class="custom-control-label" for="customCheck3">دار نشر</label>
                </div>

                <div class="form-check col-md-6 mt-3">
                    <input class="form-check-input" type="checkbox" value="لديه عتاد اعلام آلي" id="fcustomCheck4" name="fourthCheckbox">
                    <label class="custom-control-label" for="customCheck4">لديك عتاد اعلام آلي</label>
                </div>
            </div>

            <div class="d-flex">
                <div class="form-check col-md-6 me-3 mt-3">
                    <input class="form-check-input" type="checkbox" value="طباعة" id="fcustomCheck5" name="fifthCheckbox">
                    <label class="custom-control-label" for="customCheck5">لديك خدمة الطباعة</label>
                </div>
            </div>
          </div>

        <!-- Location Info Section -->
        <div class="border rounded p-4 my-4 shadow">
            <h6 class="border-bottom pb-2 mb-3">معلومات موقع المكتبة</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <select class="form-control" id="state" name="state" required>
                            <option value="" disabled selected>-- الولاية  * --</option>
                            <?php while ($row = mysqli_fetch_assoc($result_states)) { ?>
                                <option value="<?php echo $row['states']; ?>"><?php echo $row['states']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                    <select class="form-control" id="province" name="province" required>
                                <option value="" disabled selected>-- الدائرة  * --</option>
                                <?php if (isset($result_provinces)) { ?>
                                    <?php while ($row = mysqli_fetch_assoc($result_provinces)) { ?>
                                        <option value="<?php echo $row['provinces']; ?>" data-state="<?php echo $row['states']; ?>"><?php echo $row['provinces']; ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                        <div class="input-group input-group-outline m-2">
                            <select class="form-control" id="city" name="city" required>
                            <option value="">-- البلدية  * --</option>
                            <?php if (isset($result_cities)) { ?>
                                <?php while ($row = mysqli_fetch_assoc($result_cities)) { ?>
                                    <option value="<?php echo $row['cities']; ?>" data-province="<?php echo $row['provinces']; ?>" data-state="<?php echo $row['states']; ?>"><?php echo $row['cities']; ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline m-2">
                        <?php if (empty($address)): ?>
                            <label for="address" class="form-label">عنوان المكتبة * </label>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"
                            id="address" name="address" value="<?php echo $address; ?>" required
                            <?php if (!empty($address)) echo 'placeholder="عنوان المكتبة"'; ?> />
                        <span class="invalid-feedback"><?php echo $address_err; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Section-->
          <div class="border rounded p-4 shadow">
                <h6 class="border-bottom pb-2 mb-3">معلومات وسائل التواصل</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group input-group-outline m-2">
                                <?php if (empty($fbLink)): ?>
                                <label for="fbLink" class="form-label">صفحة الفيسبوك</label>
                                <?php endif; ?>
                                <input type="text" class="form-control" id="fbLink" name="fbLink" value="<?php echo $fbLink; ?>"
                                <?php if (!empty($fbLink)) echo 'placeholder="صفحة الفيسبوك"'; ?> />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-outline m-2">
                                <?php if (empty($instaLink)): ?>
                                <label for="instaLink" class="form-label">صفحة الإنستغرام</label>
                                <?php endif; ?>
                                <input type="text" class="form-control" id="instaLink" name="instaLink" value="<?php echo $instaLink; ?>"
                                <?php if (!empty($instaLink)) echo 'placeholder="صفحة الإنستغرام"'; ?> />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group input-group-outline m-2">
                                <?php if (empty($mapAddress)): ?>
                                <label for="mapAddress" class="form-label">خرائط قوقل (Google Map)</label>
                                <?php endif; ?>
                                <input type="text" class="form-control" id="mapAddress" name="mapAddress" value="<?php echo $mapAddress; ?>"
                                <?php if (!empty($mapAddress)) echo 'placeholder="خرائط قوقل (Google Map)"'; ?> />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-outline m-2">
                                <?php if (empty($websiteLink)): ?>
                                <label for="websiteLink" class="form-label">رابط موقع الويب</label>
                                <?php endif; ?>
                                <input type="text" class="form-control" id="websiteLink" name="websiteLink" value="<?php echo $websiteLink; ?>"
                                <?php if (!empty($websiteLink)) echo 'placeholder="رابط موقع الويب"'; ?> />
                            </div>
                        </div>
                    </div> 

              </div>
            <!-- File Section-->
            <div class="border rounded p-4 shadow">
               <h6 class="border-bottom pb-2 mb-3">الملاحظات + السجل التجاري</h6>
               <div class="input-group input-group-outline m-3 ps-3">
                    <input type="file" class="form-control <?php echo (!empty($file_err)) ? 'is-invalid' : ''; ?>" id="file" name="uploadedFile" />
                    <span class="invalid-feedback"><?php echo $file_err; ?></span>
              </div>
              <div class="input-group input-group-outline m-3 ps-3">
                  <label for="notes" class="form-label">ملاحظات</label>
                  <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo $notes; ?></textarea>
              </div>

            </div>   
            <div class="form-group mt-3">
                <button type="submit" name="but_submit" class="btn bg-gradient-primary" >إضـافة</button>
                </div> 
                <?php if (!empty($register_err)) { ?>
                <div class="alert alert-danger mt-3" role="alert">
                  <?php echo $register_err; ?>
                </div>
              <?php } ?>
        </form>

</div>
  </main>
  <!--   Core JS Files   -->
  <script src="ext_lib.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="assets/js/plugins/chartjs.min.js"></script>
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