<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel = "icon" href = "../assets/img/ok-logo.png" type = "image/x-icon">
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
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
  <!-- Pure JavaScript Modal Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pure-js-modal@1.0.0/dist/modal.css">
<script src="https://cdn.jsdelivr.net/npm/pure-js-modal@1.0.0/dist/modal.js"></script>

</head>

<body class="g-sidenav-show rtl bg-gray-200">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-end me-3 rotate-caret  bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute start-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="index.php">
        <img src="../assets/img/ok-logo.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="me-1 font-weight-bold text-white">لوحة التحكم</span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse px-0 w-auto  max-height-vh-100" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="display_libraries.php?page=1">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">store</i>
            </div>
            <span class="nav-link-text me-1">المكتبات</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="add_library.php">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">add</i>
            </div>
            <span class="nav-link-text me-1">إضافة مكتبة</span>
          </a>
        </li>
        <?php
        $currentDate = date("Y-m-d");

        if ($_SESSION['role'] === "admin") { ?>
        <li class="nav-item">
          <a class="nav-link" href="display_library_types.php?page=1">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">segment</i>
            </div>
            <span class="nav-link-text me-1">أنواع المكتبات</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="display_library_percentages.php?page=1">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">badge</i>
            </div>
            <span class="nav-link-text me-1">أنواع العملاء</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="display_users.php">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">person_add</i>
            </div>
            <span class="nav-link-text me-1">المستخدمون</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="display_lib_ext.php">
            <div class="text-white text-center ms-2 d-flex align-items-center justify-content-center">
              <i class="material-icons-round opacity-10">store</i>
            </div>
            <span class="nav-link-text me-1">مكتبات للإنضمام</span>
          </a>
        </li>
        <?php } ?>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
      <div class="mx-3">
      <p class="text-white text-center mb-2 fs-4" id="currentDateTime"></p>
      <script>
// Function to update the current date and time
function updateDateTime() {
    const currentDateTimeElement = document.getElementById('currentDateTime');
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    currentDateTimeElement.textContent = `${hours}.${minutes}.${seconds} ${year}/${month}/${day}`;
  }

  // Update the date and time every second
  setInterval(updateDateTime, 1000);
</script>
           <button type="button" class="btn bg-gradient-primary mt-4 w-100" data-bs-toggle="modal" data-bs-target="#logOutModal">تسجيل الخروج</button>
      </div>
     </div>
        
  </aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg overflow-x-hidden">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <div class="collapse navbar-collapse mt-sm-0 mt-2 px-0" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
             
          <form role="form" method="post" action="search_result.php">
          <div style="display: flex; align-items: center;">
            <div class="input-group input-group-outline" style="flex: 1;">
              <label class="form-label">بحث ...</label>
              <input type="text" name="search_query" class="form-control" required>
            </div>
            <button type="submit" class="btn bg-gradient-primary mt-3 me-3"> بحث </button>
            </div>
            </form>
        
            </div>
          <ul class="navbar-nav me-auto ms-0 justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="../logout.php" class="nav-link text-body font-weight-bold px-0" data-bs-toggle="modal" data-bs-target="#logOutModal">
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
