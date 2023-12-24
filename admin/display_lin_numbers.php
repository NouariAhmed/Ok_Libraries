<?php
session_start();
include('secure.php');
include('../connect.php');
$table = "libraries";

$usernames = [];
$sql_users = "SELECT id, username FROM users";
$result_users = mysqli_query($conn, $sql_users);
while ($user = mysqli_fetch_assoc($result_users)) {
    $usernames[$user['id']] = $user['username'];
}

$selectedLibraryType = isset($_GET['libraryType']) ? $_GET['libraryType'] : 'all';
$selectedLibraryPercentage = isset($_GET['libraryPercentage']) ? $_GET['libraryPercentage'] : 'all'; 
$selectedStates = isset($_GET['states']) ? $_GET['states'] : 'all';
$selectedProvinces = isset($_GET['province']) ? $_GET['province'] : 'all';
$selectedCities = isset($_GET['city']) ? $_GET['city'] : 'all';
$selectedHasNotes = isset($_GET['hasNotes']) ? $_GET['hasNotes'] : 'all';
$selectedSocialMedia = isset($_GET['socialMedia']) ? $_GET['socialMedia'] : 'all';
$selectedLibraryDetails = isset($_GET['libraryDetails']) ? $_GET['libraryDetails'] : 'all';

$sessionUserId = $_SESSION['id']; 
$userRole = $_SESSION['role']; 

$sql = "";
if ($userRole === 'admin' || $userRole === 'manager') {
  // For admin users, display all libraries
  $sql = "SELECT l.*, loc.states, loc.provinces, loc.cities, lt.library_type, lp.library_percentage
          FROM $table AS l
          INNER JOIN locations AS loc ON l.location_id = loc.location_id
          LEFT JOIN library_types AS lt ON l.library_type_id = lt.id
          LEFT JOIN library_percentages AS lp ON l.library_percentage_id = lp.id WHERE 1 = 1";
} elseif ($userRole === 'member') {
  // For member users, display only their own libraries
  $sql = "SELECT l.*, loc.states, loc.provinces, loc.cities, lt.library_type, lp.library_percentage
          FROM $table AS l
          INNER JOIN locations AS loc ON l.location_id = loc.location_id
          LEFT JOIN library_types AS lt ON l.library_type_id = lt.id
          LEFT JOIN library_percentages AS lp ON l.library_percentage_id = lp.id
          WHERE l.inserted_by = $sessionUserId";
}

$bindTypes = '';
$bindValues = [];

if ($selectedLibraryType !== 'all') {
  $sql .= " AND lt.library_type = ?";
  $bindTypes .= 's';
  $bindValues[] = &$selectedLibraryType;
}

if ($selectedLibraryPercentage !== 'all') {
  $sql .= " AND lp.library_percentage = ?";
  $bindTypes .= 's';
  $bindValues[] = &$selectedLibraryPercentage;
}

if ($selectedStates !== 'all') {
  $sql .= " AND loc.states = ?";
  $bindTypes .= 's';
  $bindValues[] = &$selectedStates;
}

if ($selectedProvinces !== 'all') {
  $sql .= " AND loc.provinces = ?";
  $bindTypes .= 's';
  $bindValues[] = &$selectedProvinces;
}

if ($selectedCities !== 'all') {
  $sql .= " AND loc.cities = ?";
  $bindTypes .= 's';
  $bindValues[] = &$selectedCities;
}

// Notes filter
if ($selectedHasNotes !== 'all') {
  if ($selectedHasNotes === 'yes') {
      $sql .= " AND l.notes IS NOT NULL AND l.notes <> ''";
  } else {
      $sql .= " AND (l.notes IS NULL OR l.notes = '')";
  }
}

// Social media filter
if ($selectedSocialMedia !== 'all') {
  if ($selectedSocialMedia === 'fb') {
      $sql .= " AND l.fbLink IS NOT NULL AND l.fbLink <> ''";
  } elseif ($selectedSocialMedia === 'insta') {
      $sql .= " AND l.instaLink IS NOT NULL AND l.instaLink <> ''";
  } elseif ($selectedSocialMedia === 'map') {
      $sql .= " AND l.mapAddress IS NOT NULL AND l.mapAddress <> ''";
  } elseif ($selectedSocialMedia === 'website') {
      $sql .= " AND l.websiteLink IS NOT NULL AND l.websiteLink <> ''";
  }
}

// Details filter
if ($selectedLibraryDetails !== 'all') {
  if ($selectedLibraryDetails === 'libraryPoster') {
      $sql .= " AND l.firstCheckbox = 'مكتبة ووراقة'";
  } elseif ($selectedLibraryDetails === 'online') {
      $sql .= " AND l.secondCheckbox = 'يعمل أونلاين'";
  } elseif ($selectedLibraryDetails === 'publishingHouse') {
      $sql .= " AND l.thirdCheckbox = 'دار نشر'";
  } elseif ($selectedLibraryDetails === 'infoMateriel') {
      $sql .= " AND l.fourthCheckbox = 'لديه عتاد اعلام آلي'";
  } elseif ($selectedLibraryDetails === 'printService') {
      $sql .= " AND l.fifthCheckbox = 'طباعة'";
  }  
}

$sql .= " ORDER BY l.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($bindValues)) {
  $bindParams = array_merge([$bindTypes], $bindValues);
  $stmt->bind_param(...$bindParams);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

include('header.php');
?>

    <div class="container-fluid py-4">
       
        <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <h5 class="mb-3">فلترة المكتبات</h5>

            <div class="row">
              <div class="col-md-4">
                  <div class="input-group input-group-outline m-2">
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
              </div>
              <div class="col-md-4">
                  <div class="input-group input-group-outline m-2">
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
              </div>
              <div class="col-md-4">
                <div class="input-group input-group-outline m-2">
                        <select class="form-control" id="libraryDetails" name="libraryDetails">
                            <option value="all" <?php echo $selectedLibraryDetails === 'all' ? 'selected' : ''; ?>>-- جميع التفاصيل --</option>
                            <option value="libraryPoster" <?php echo $selectedLibraryDetails === 'libraryPoster' ? 'selected' : ''; ?>>مكتبة ووراقة</option>
                            <option value="printService" <?php echo $selectedLibraryDetails === 'printService' ? 'selected' : ''; ?>>خدمة الطباعة</option>
                            <option value="online" <?php echo $selectedLibraryDetails === 'online' ? 'selected' : ''; ?>>يعمل أونلاين</option>
                            <option value="publishingHouse" <?php echo $selectedLibraryDetails === 'publishingHouse' ? 'selected' : ''; ?>>دار نشر</option>
                            <option value="infoMateriel" <?php echo $selectedLibraryDetails === 'infoMateriel' ? 'selected' : ''; ?>>يملك عتاد الإعلام آلي</option>
                        </select>
                </div>
              </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                  <div class="input-group input-group-outline m-2">
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
                </div>
               <div class="col-md-4">
                    <div class="input-group input-group-outline m-2">
                      <select class="form-control" name="province" id="province" <?php echo $selectedStates === 'all' ? 'disabled' : ''; ?>>
                          <option value="all">-- جميع الدوائر --</option>
                      </select>
                    </div>
               </div>
               <div class="col-md-4">
                  <div class="input-group input-group-outline m-2">
                      <select class="form-control" name="city" id="city" <?php echo ($selectedStates === 'all' || $selectedProvinces === 'all') ? 'disabled' : ''; ?>>
                          <option value="all">-- جميع البلديات --</option>
                      </select>
                  </div>
               </div>
             </div>
             <div class="row">
               <div class="col-md-4">
                  <div class="input-group input-group-outline m-2">
                    <select class="form-control" id="hasNotes" name="hasNotes">
                        <option value="all" <?php echo $selectedHasNotes === 'all' ? 'selected' : ''; ?>>-- فلترة الملاحظات --</option>
                        <option value="yes" <?php echo $selectedHasNotes === 'yes' ? 'selected' : ''; ?>>يملك ملاحظات</option>
                        <option value="no" <?php echo $selectedHasNotes === 'no' ? 'selected' : ''; ?>>لا يملك ملاحظات</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-outline m-2">
                      <select class="form-control" id="socialMedia" name="socialMedia">
                          <option value="all" <?php echo $selectedSocialMedia === 'all' ? 'selected' : ''; ?>>-- جميع وسائل التواصل --</option>
                          <option value="fb" <?php echo $selectedSocialMedia === 'fb' ? 'selected' : ''; ?>>فيسبوك</option>
                          <option value="insta" <?php echo $selectedSocialMedia === 'insta' ? 'selected' : ''; ?>>إنستغرام</option>
                          <option value="website" <?php echo $selectedSocialMedia === 'website' ? 'selected' : ''; ?>>موقع</option>
                          <option value="map" <?php echo $selectedSocialMedia === 'map' ? 'selected' : ''; ?>>خرائط قوقل</option>
                      </select>
                  </div>
                </div>
            </div>
          <button type="submit"  class="btn bg-gradient-primary" >فلترة</button> 
          <button type="button" class="btn btn-secondary" id="clearFilter">مسح الفلتر</button>
          <button id="copyButton" class="btn btn-warning">نسخ معلومات المكتبة</button>

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
                <table class="table align-items-center mb-0 table-hover" id="libraryTable">
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
                     
                    </tr>
                  </thead>
                  <tbody>
                  <?php

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
                        <p class="text-xs text-warning text-bold mb-0" id="studentPhone"><?php echo htmlspecialchars($item["student_phone"]);?></p>

                      </td>
                      <td class="align-middle text-sm">
                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item["states"]); ?></h6>
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($item["provinces"]); ?></p>
                        <p class="text-xs text-warning mb-0 text-bold" id="cities"><?php echo htmlspecialchars($item["cities"]); ?></p>
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
                    </tr>
                    <?php
                }
                ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
document.addEventListener("DOMContentLoaded", function() {
  const copyButton = document.getElementById("copyButton");

  copyButton.addEventListener("click", function(event) {
    event.preventDefault(); // Prevent the default form submission behavior

    let selectedState = document.getElementById("state").value;

    let textToCopy = `قائمة مكتبات ولاية ${selectedState}\n`; // Add the header with the selected state

    // Get all the table row elements from the body of the table
    let rows = document.querySelectorAll("#libraryTable tbody tr");

    // Loop over each table row
    rows.forEach(function(row) {
      // Get the library name and phone number from the relevant cells
      // Assume they are always in the first and third cells of each row
      let libraryName = row.cells[0].querySelector('h6').textContent.trim(); // Get only the library name, ignore last name and ID
      let studentPhone = row.cells[2].querySelector('#studentPhone').textContent.trim(); // Get only student phones , ignore second and main phone
      let cities = row.cells[3].querySelector('#cities').textContent.trim(); // 

      // Append the information to the text to copy, format "Library Name - Phone Number - Student Phone"
      textToCopy += `${libraryName} : ${studentPhone} - ${cities}\n`;
    });

    // Create a temporary textarea element to assist in copying the text
    let textarea = document.createElement('textarea');
    textarea.value = textToCopy;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);

    // Create a success message element
    let successMessage = document.createElement('div');
    successMessage.textContent = "تم نسخ المعلومات بنجاح!";
    successMessage.style.color = "#4BB543";
    successMessage.style.fontWeight = "bold";
    successMessage.style.marginRight = "6px"; // Add right margin to separate from the copy button
    successMessage.style.marginBottom = "10px"; // Add right margin to separate from the copy button

    // Insert the success message next to the copy button
    copyButton.insertAdjacentElement('afterend', successMessage);

    // Remove the success message after a short delay (e.g., 2 seconds)
    setTimeout(function() {
      successMessage.remove();
    }, 2000);
  });
});


document.addEventListener("DOMContentLoaded", function() {
    const libraryTypeDropdown = document.getElementById("libraryType");
    const libraryPercentageDropdown = document.getElementById("libraryPercentage");
    const stateDropdown = document.getElementById("state");
    const provinceDropdown = document.getElementById("province");
    const cityDropdown = document.getElementById("city");
    const notesDropdown = document.getElementById("hasNotes");
    const socialMediaDropdown = document.getElementById("socialMedia");
    const libraryDetailsDropdown = document.getElementById("libraryDetails");

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
        notesDropdown.value = "all";
        socialMediaDropdown.value = "all";
        libraryDetailsDropdown.value = "all";
        provinceDropdown.disabled = true;
        cityDropdown.disabled = true;
    });
});

</script>
<?php
 mysqli_close($conn);
include('footer.php');
?>         