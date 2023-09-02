<?php
session_start();
include('secure.php');
include('../connect.php');
$table = "library_percentages";

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
$result = mysqli_query($conn, "SELECT * FROM library_percentages LIMIT $startIndex, $itemsPerPage");
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        // Validate user input
        $library_percentage = htmlspecialchars($_POST['library_percentage']);
        if (empty($library_percentage)) {
            echo "<div class='alert alert-danger text-right text-white'>نوع العميل مطلوب</div>";
        } else {
            // Prepare and execute SQL query
            $stmt = mysqli_prepare($conn, "INSERT INTO library_percentages (library_percentage) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $library_percentage);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['create_update_success'] = true;
                header("Location: display_library_percentages.php");
                exit;
            } else {
                echo "<div class='alert alert-danger text-right text-white'>حدث خطأ</div>";
            }
            mysqli_stmt_close($stmt);
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
        <form role="form" action="" method="post">
        <h4 class="mb-3">إضـافة نوع عميل </h4>
          <div class="input-group input-group-outline my-3">
            <label class="form-label">نوع العميل :</label>
              <input type="text" name="library_percentage" class="form-control" value="<?php echo htmlspecialchars(isset($item['library_percentage']) ? $item['library_percentage'] : ''); ?>" required>
              </div>
                <button type="submit" name="submit" class="btn bg-gradient-primary" >إضـافة</button>
        </form>
    <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize pe-3">جدول أنواع العملاء</h6>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7" >المعرف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">العميل</th>
                      <th class="text-center text-secondary text-lg font-weight-bolder opacity-7">الإجراءات</th>
                    
                      <th class="text-secondary opacity-7"></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                foreach ($items as $item) {
                    $id = htmlspecialchars($item["id"]);
                    $library_percentage = htmlspecialchars($item["library_percentage"]);
?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm pe-3"><?php echo $id;?>#</h6>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php echo $library_percentage;?></h6>
                      </td>
                      <td class="align-middle text-center">
                            <a href="update_library_percentage.php?id=<?php echo $id;?>" class="btn badge-sm bg-gradient-primary"> <i class="material-icons-round align-middle" style="font-size: 18px;">edit</i></a>
                            <a href="delete_library_percentage.php?id=<?php echo $id;?>" class="btn badge-sm bg-gradient-danger"> <i class="material-icons-round align-middle" style="font-size: 18px;">delete</i></a>                          
                      </td>
                    </tr>
                    <?php
                }
                ?>
                  </tbody>
                </table>
                  <?php
                include('../pagination.php');
                // Close the database connection
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

          