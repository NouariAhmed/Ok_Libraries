<?php
session_start();
include('secure.php');
include('../connect.php');
$table = "libraries";

$itemsPerPage = 10; // Number of items per page

$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;

// Get the total number of items in the database
$sql = "SELECT COUNT(*) AS total_items FROM $table";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$totalItems = $row['total_items'];

$totalPages = ceil($totalItems / $itemsPerPage);
$currentPage = max(1, min($currentPage, $totalPages));

$startIndex = ($currentPage - 1) * $itemsPerPage;

// Retrieve items for the current page
$result = mysqli_query($conn, "SELECT * FROM $table LIMIT $startIndex, $itemsPerPage");
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);
 
// Get the selected library type, states, provinces, and cities from the query parameters
$selectedLibraryType = isset($_GET['libraryType']) ? $_GET['libraryType'] : 'all';
$selectedLibraryPercentage = isset($_GET['libraryPercentage']) ? $_GET['libraryPercentage'] : 'all'; 
$selectedStates = isset($_GET['states']) ? $_GET['states'] : 'all';
$selectedProvinces = isset($_GET['provinces']) ? $_GET['provinces'] : 'all';
$selectedCities = isset($_GET['cities']) ? $_GET['cities'] : 'all';


// Construct the SQL query based on selected filters
$sql = "SELECT COUNT(*) AS total_filtered_items FROM $table AS l
        INNER JOIN locations AS loc ON l.location_id = loc.location_id
        LEFT JOIN library_types AS lt ON l.library_type_id = lt.id
        LEFT JOIN library_percentages AS lp ON l.library_percentage_id = lp.id
        WHERE 1 = 1"; // Initial SQL with a dummy condition


$bindTypes = ''; // String to store parameter types
$bindValues = []; // Array to store parameter values

if ($selectedLibraryType !== 'all') {
    $sql .= " AND lt.library_type = ?";
    $bindTypes .= 's'; // Assuming library_type is a string
    $bindValues[] = &$selectedLibraryType;
}

if ($selectedLibraryPercentage !== 'all') { // Add this condition
    $sql .= " AND lp.library_percentage = ?";
    $bindTypes .= 's'; // Assuming library_percentage is a string
    $bindValues[] = &$selectedLibraryPercentage;
}

if ($selectedStates !== 'all') {
    $sql .= " AND loc.states = ?";
    $bindTypes .= 's'; // Assuming states is a string
    $bindValues[] = &$selectedStates;
}

if ($selectedProvinces !== 'all') {
    $sql .= " AND loc.provinces = ?";
    $bindTypes .= 's'; // Assuming provinces is a string
    $bindValues[] = &$selectedProvinces;
}

if ($selectedCities !== 'all') {
    $sql .= " AND loc.cities = ?";
    $bindTypes .= 's'; // Assuming cities is a string
    $bindValues[] = &$selectedCities;
}



$countStmt = mysqli_prepare($conn, $sql);

// Bind parameters for the count query prepared statement
if (!empty($bindValues)) {
    $bindParams = array_merge([$bindTypes], $bindValues);
    $countStmt->bind_param(...$bindParams);
}

// Execute the count query
mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);
$countRow = mysqli_fetch_assoc($countResult);
$totalFilteredItems = $countRow['total_filtered_items'];

// Calculate Total Pages
$totalPages = ceil($totalFilteredItems / $itemsPerPage);

// Construct the main SQL query for pagination
$sql = "SELECT l.*, loc.states, loc.provinces, loc.cities, lt.library_type, lp.library_percentage
        FROM libraries AS l
        INNER JOIN locations AS loc ON l.location_id = loc.location_id
        LEFT JOIN library_types AS lt ON l.library_type_id = lt.id
        LEFT JOIN library_percentages AS lp ON l.library_percentage_id = lp.id
        WHERE 1 = 1";
        
if ($selectedLibraryType !== 'all') {
    $sql .= " AND lt.library_type = ?";
}

if ($selectedLibraryPercentage !== 'all') {
    $sql .= " AND lp.library_percentage = ?";
}

if ($selectedStates !== 'all') {
    $sql .= " AND loc.states = ?";
}

if ($selectedProvinces !== 'all') {
    $sql .= " AND loc.provinces = ?";
}

if ($selectedCities !== 'all') {
    $sql .= " AND loc.cities = ?";
}


$sql .= " ORDER BY l.id DESC";
$sql .= " LIMIT $startIndex, $itemsPerPage";

$stmt = mysqli_prepare($conn, $sql);

// Bind parameters for the query prepared statement
if (!empty($bindValues)) {
    $bindParams = array_merge([$bindTypes], $bindValues);
    $stmt->bind_param(...$bindParams);
}

// Execute the query
mysqli_stmt_execute($stmt);

// Get the result set
$result = mysqli_stmt_get_result($stmt);

// Fetch the items for the current page
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

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
       
       <h4 class="mb-3">فلترة المكتبات</h4>
        <div class="input-group input-group-outline my-3">
            <a href="add_library.php" class="btn btn-secondary">إضـافة</a>
        </div>

        <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <h5 class="mb-3">فلترة</h5>
           <div class="input-group input-group-outline my-3">
                <select class="form-control" id="libraryType" name="libraryType">
                    <option value="all" <?php echo $selectedLibraryType === 'all' ? 'selected' : ''; ?>>-- جميع أنواع المكتبات --</option>
                    <?php
                    $libraryTypesQuery = "SELECT * FROM library_types";
                    $libraryTypesResult = mysqli_query($conn, $libraryTypesQuery);

                    while ($libraryTypeRow = mysqli_fetch_assoc($libraryTypesResult)) {
                        $typeId = $libraryTypeRow['id'];
                        $typeName = $libraryTypeRow['library_type'];
                        $selected = $selectedLibraryType === $typeName ? 'selected' : '';

                        echo "<option value=\"$typeName\" $selected>$typeName</option>";
                    }
                    ?>
                </select>
           </div>
           <div class="input-group input-group-outline my-3">
                <select class="form-control" id="libraryPercentage" name="libraryPercentage">
                    <option value="all" <?php echo $selectedLibraryPercentage === 'all' ? 'selected' : ''; ?>>-- جميع أنواع العملاء --</option>
                    <?php
                    $libraryPercentagesQuery = "SELECT * FROM library_percentages";
                    $libraryPercentagesResult = mysqli_query($conn, $libraryPercentagesQuery);

                    while ($libraryPercentageRow = mysqli_fetch_assoc($libraryPercentagesResult)) {
                        $percentageId = $libraryPercentageRow['id'];
                        $percentageName = $libraryPercentageRow['library_percentage'];
                        $selected = $selectedLibraryPercentage === $percentageName ? 'selected' : '';

                        echo "<option value=\"$percentageName\" $selected>$percentageName</option>";
                    }
                    ?>
                </select>
         </div>

            <div class="input-group input-group-outline my-3">
                <select class="form-control" id="state" name="states">
                    <option value="all" <?php echo $selectedStates === 'all' ? 'selected' : ''; ?>>-- جميع الولايات --</option>
                    <!-- Fetch and display states dynamically from the database -->
                    <?php
                    $statesQuery = "SELECT DISTINCT states FROM locations";
                    $statesResult = mysqli_query($conn, $statesQuery);
                    while ($statesRow = mysqli_fetch_assoc($statesResult)) {
                        $isSelected = $selectedStates == $statesRow['states'] ? 'selected' : '';
                        echo '<option value="' . $statesRow['states'] . '" ' . $isSelected . '>' . $statesRow['states'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="input-group input-group-outline my-3">
    <select class="form-control" name="province" id="province" <?php echo $selectedStates === 'all' ? 'disabled' : ''; ?>>
        <option value="all">-- جميع الدوائر --</option>
    </select>
</div>

<div class="input-group input-group-outline my-3">
    <select class="form-control" name="city" id="city" <?php echo ($selectedStates === 'all' || $selectedProvinces === 'all') ? 'disabled' : ''; ?>>
        <option value="all">-- جميع البلديات --</option>
    </select>
</div>
          <button type="submit"  class="btn bg-gradient-primary" >فلترة</button> 
          <button type="button" class="btn btn-secondary" id="clearFilter">مسح الفلتر</button>

        </form>
    <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize pe-3">جدول المكتبات</h6>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0 table-hover">
                  <thead>
                    <tr>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 ">المكتبة</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">النوع</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الهاتف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">الموقع</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">العنوان</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">تفاصيل</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">من طرف</th>
                      <th class="text-secondary text-lg font-weight-bolder opacity-7 pe-2">ملاحظات</th>
                      <th class="text-center text-secondary text-lg font-weight-bolder opacity-7">الإجراءات</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                 
                    $usernames = [];
                    $sql_users = "SELECT id, username FROM users";
                    $result_users = mysqli_query($conn, $sql_users);
                    while ($user = mysqli_fetch_assoc($result_users)) {
                        $usernames[$user['id']] = $user['username'];
                    }

                foreach ($items as $item) {    
                ?>
                    <tr>
                    <td class="align-middle text-sm">
                            <h6 class="mb-0 text-sm pe-3"><?php echo htmlspecialchars($item["library_name"]);?></h6>
                            <p class="text-xs text-secondary text-bold mb-0 pe-3"><?php echo htmlspecialchars($item["library_last_name"]);?></p>
                            <p class="text-xs text-warning text-bold mb-0 pe-3"><?php echo htmlspecialchars($item["id"]);?>#</h6>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["library_type"]);?></h6>
                        <p class="text-xs text-secondary text-bold mb-0"><?php echo htmlspecialchars($item["library_percentage"]);?></p>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["phone"]);?></h6>
                        <p class="text-xs text-secondary text-bold mb-0"><?php echo htmlspecialchars($item["second_phone"]);?></p>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["states"]); ?></h6>
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["provinces"]); ?></p>
                        <p class="text-xs text-warning mb-0 text-bold"><?php echo htmlspecialchars($item["cities"]); ?></p>
                      </td>
                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php
                        $address = htmlspecialchars($item["address"]);
                        $words = explode(' ', $address);
                        
                        $wordGroups = array_chunk($words, 4);
                        foreach ($wordGroups as $group) {
                            echo implode(' ', $group) . "<br>";
                        }
                        ?></h6>
                      <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["email"]);?></p>
                      <!-- Social Media Icons -->
                      <div class="ms-auto">
                          <?php if (!empty($item["fbLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["fbLink"]); ?>" target="_blank">
                              <i class="fab fa-facebook"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["instaLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["instaLink"]); ?>" target="_blank">
                              <i class="fab fa-instagram"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["mapAddress"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["mapAddress"]); ?>" target="_blank">
                              <i class="fas fa-map-marker-alt"></i>
                            </a>
                          <?php } ?>
                          <?php if (!empty($item["websiteLink"])) { ?>
                            <a href="<?php echo htmlspecialchars($item["websiteLink"]); ?>" target="_blank">
                              <i class="fas fa-globe"></i>
                            </a>
                          <?php } ?>
                        </div>
                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-xs">- <?php echo htmlspecialchars($item["firstCheckbox"]); ?></h6>
                        <h6 class="mb-0 text-xs">- <?php echo htmlspecialchars($item["secondCheckbox"]); ?></h6>
                        <h6 class="mb-0 text-xs">- <?php echo htmlspecialchars($item["thirdCheckbox"]); ?></h6>
                        <h6 class="mb-0 text-xs">- <?php echo htmlspecialchars($item["fourthCheckbox"]); ?></h6>
                        <h6 class="mb-0 text-xs">- <?php echo htmlspecialchars($item["fifthCheckbox"]); ?></h6>

                      </td>
                      <td class="align-middle text-sm">
                      <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($usernames[$item["inserted_by"]]); ?></h6>
                      <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["created_at"]);?></p>
                      </td>
                      <td class="align-middle text-sm">
                    <h6 class="mb-0 text-sm">
                        <?php if (!empty($item['notes'])): ?>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $item['id']; ?>">
                                <i class="fas fa-comment-alt align-middle" style="font-size: 18px;"></i>
                            </button>
                        <?php endif; ?>
                    </h6>
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal_<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitle">ملاحظات خاصة بمكتبة: <?php echo $item['library_name']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="modalContent">
                                    <?php
                        $words = explode(' ', $item['notes']); // Split note content into words
                        $chunkedWords = array_chunk($words, 9); // Group words into sets of 9
                        
                        foreach ($chunkedWords as $wordSet) {
                            echo '<div class="note-line">' . implode(' ', $wordSet) . '</div>'; // Display each set of words
                        }
                        ?>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex justify-content-center">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">غلق</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>          
                      <td class="align-middle text-center">
                        <?php if (!empty($item["userfile"])): ?>
                                    <a href="<?php echo htmlspecialchars($item["userfile"]); ?>" class="btn badge-sm bg-gradient-secondary" target="_blank">
                                    <i class="fas fa-file-pdf align-middle" style="font-size: 18px;"></i></a>
                        <?php endif; ?>
                        <a href="update_library.php?id=<?php echo htmlspecialchars($item["id"]); ?>&states=<?php echo htmlspecialchars($item["states"]); ?>&province=<?php echo htmlspecialchars($item["provinces"]); ?>&city=<?php echo htmlspecialchars($item["cities"]); ?>" class="btn badge-sm bg-gradient-primary">
                        <i class="material-icons-round align-middle" style="font-size: 18px;">edit</i>
                        </a>
                        <a href="delete_library.php?id=<?php echo htmlspecialchars($item["id"]);?>" class="btn badge-sm bg-gradient-danger"> <i class="material-icons-round align-middle" style="font-size: 18px;">delete</i></a>
                      </td>
                    </tr>
                    <?php
                }
                ?>
                  </tbody>
                </table>
                <?php
                include('../pagination.php');
                  ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
document.addEventListener("DOMContentLoaded", function() {
    const libraryTypeDropdown = document.getElementById("libraryType");
    const libraryPercentageDropdown = document.getElementById("libraryPercentage");
    const stateDropdown = document.getElementById("state");
    const provinceDropdown = document.getElementById("province");
    const cityDropdown = document.getElementById("city");

    stateDropdown.addEventListener("change", function() {
        const selectedState = stateDropdown.value;

        // Enable both the Province and City dropdowns
        provinceDropdown.disabled = false;
        cityDropdown.disabled = false;

        // Fetch provinces using AJAX
        fetch(`get_provinces_for_state.php?state_id=${selectedState}`)
            .then(response => response.json())
            .then(data => {
                provinceDropdown.innerHTML = '<option value="all">-- جميع الدوائر --</option>';
                data.forEach(province => {
                    provinceDropdown.innerHTML += `<option value="${province.id}">${province.province_name}</option>`;
                });
            })
            .catch(error => console.error(error));

        // Reset the City dropdown
        cityDropdown.innerHTML = '<option value="all">-- جميع البلديات --</option>';
    });

    provinceDropdown.addEventListener("change", function() {
        const selectedProvinces = provinceDropdown.value;

        if (selectedProvinces !== "all") {
            // Fetch cities using AJAX
            fetch(`get_cities_for_province.php?province_id=${selectedProvinces}`)
                .then(response => response.json())
                .then(data => {
                    cityDropdown.innerHTML = '<option value="all">-- جميع البلديات --</option>';
                    data.forEach(city => {
                        cityDropdown.innerHTML += `<option value="${city.id}">${city.city_name}</option>`;
                    });
                })
                .catch(error => console.error(error));
        } else {
            // Reset the City dropdown
            cityDropdown.innerHTML = '<option value="all">-- جميع البلديات --</option>';
        }
    });

    // Add event listener to the Clear Filter button
    const clearFilterButton = document.getElementById("clearFilter");
    clearFilterButton.addEventListener("click", function() {
        // Clear selected values and disable dropdowns
        libraryTypeDropdown.value = "all";
        libraryPercentageDropdown.value = "all";
        stateDropdown.value = "all";
        provinceDropdown.value = "all";
        cityDropdown.value = "all";
        provinceDropdown.disabled = true;
        cityDropdown.disabled = true;
    });
});

</script>
<?php
 mysqli_close($conn);
include('footer.php');
?>

          