<?php
session_start();
ob_start(); // Start output buffering
include('secure.php');
include('header.php');
include('../connect.php');
// Initialize variables
$library_name = $library_last_name = $library_type_id = $library_percentage_id = $address = $phone = $second_phone = $email = $fbLink = $instaLink = $mapAddress = $tiktokLink = $notes = $state = $province = $city = "";
$library_name_err = $library_last_name_err = $address_err = $phone_err = $second_phone_err = $email_err = $state_err = $province_err = $city_err = $register_err = $file_err= "";

// Fetch library types from the database
$sql_library_types = "SELECT id, library_type FROM library_types";
$result_library_types = mysqli_query($conn, $sql_library_types);
$libraryTypes = mysqli_fetch_all($result_library_types, MYSQLI_ASSOC);

// Fetch library percentages from the database
$sql_library_percentages = "SELECT id, library_percentage FROM library_percentages";
$result_library_percentages = mysqli_query($conn, $sql_library_percentages);
$libraryPercentages = mysqli_fetch_all($result_library_percentages, MYSQLI_ASSOC);

?>
<div class="container-fluid py-4">
    <?php
    if (isset($_SESSION['create_update_success']) && $_SESSION['create_update_success'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['create_update_success']);
        // Redirect to the display_libraries page with a success message
        header("Location: display_libraries.php?create_update_success=1");
        exit;
    }

    if (isset($_SESSION['item_not_found']) && $_SESSION['item_not_found'] === true) {
        // Unset the session variable to avoid displaying the message on page refresh
        unset($_SESSION['item_not_found']);
        // Redirect to the display_libraries page with a success message
        header("Location: display_libraries.php?item_not_found=1");
        exit;
    }

    // Database connection configuration
    include('../connect.php');

    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $states = isset($_GET['states']) ? $_GET['states'] : '';


    if (!empty($id)) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM libraries WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $item = mysqli_fetch_assoc($result);

            $library_name = htmlspecialchars($item["library_name"]);          
            $library_last_name = htmlspecialchars($item["library_last_name"]);
            $library_type_id = htmlspecialchars($item["library_type_id"]);
            $library_percentage_id = htmlspecialchars($item["library_percentage_id"]);
            $address = htmlspecialchars($item["address"]);
            $phone = htmlspecialchars($item["phone"]);
            $second_phone = htmlspecialchars($item["second_phone"]);
            $email = htmlspecialchars($item["email"]);
            $notes = htmlspecialchars($item["notes"]);
          
            $fbLink = htmlspecialchars($item["fbLink"]);
            $instaLink = htmlspecialchars($item["instaLink"]);
            $mapAddress = htmlspecialchars($item["mapAddress"]);
            $tiktokLink = htmlspecialchars($item["tiktokLink"]);

            $location_id =  htmlspecialchars($item["location_id"]);
           // Fetch the selected library's location details
            $locationQuery = "SELECT states, provinces, cities FROM locations WHERE location_id = ?";
            $stmt_location = mysqli_prepare($conn, $locationQuery);
            mysqli_stmt_bind_param($stmt_location, "i", $location_id);
            mysqli_stmt_execute($stmt_location);
            $locationResult = mysqli_stmt_get_result($stmt_location);
            $location = mysqli_fetch_assoc($locationResult);

            $selectedState = htmlspecialchars($location["states"]);
            $selectedProvince = htmlspecialchars($location["provinces"]);
            $selectedCity = htmlspecialchars($location["cities"]);


        } else {
            $_SESSION['item_not_found'] = true;
            // Close the statement result
            mysqli_stmt_close($stmt);
            // Redirect to the display_libraries page after item not found
            header("Location: display_libraries.php");
            exit;
        }

        // Close the statement result
        mysqli_stmt_close($stmt);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['updateData'])) {
           // Get the form data
           $library_name = trim($_POST["library_name"]);
           $library_last_name = trim($_POST["library_last_name"]);
           $library_type_id = trim($_POST["library_type_id"]);
           $library_percentage_id = trim($_POST["library_percentage_id"]);
           $address = trim($_POST["address"]);
           $phone = trim($_POST["phone"]);
           $second_phone = trim($_POST["second_phone"]);
           $email = trim($_POST["email"]);
           $notes = trim($_POST["notes"]);
         
           $fbLink = trim($_POST["fbLink"]);
           $instaLink = trim($_POST["instaLink"]);
           $mapAddress = trim($_POST["mapAddress"]);
           $tiktokLink = trim($_POST["tiktokLink"]);
          
           $state =  trim($_POST["state"]);
           $province = trim($_POST["province"]);
           $city = trim($_POST["city"]);
           

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
            if (!empty($phone) && !preg_match($phonePattern, $phone)) {
                $phone_err = "رقم هاتف غير صالح.";
            } else {
                // Check if phone number already exists in the database (in phone or second_phone column)
                $existingPhoneQuery = "SELECT id, library_name FROM libraries WHERE (phone = ? OR second_phone = ?) AND id != ?";
                $stmt_existingPhone = mysqli_prepare($conn, $existingPhoneQuery);
                mysqli_stmt_bind_param($stmt_existingPhone, "ssi", $phone, $phone, $id);
                mysqli_stmt_execute($stmt_existingPhone);
                mysqli_stmt_store_result($stmt_existingPhone);
                if (mysqli_stmt_num_rows($stmt_existingPhone) > 0) {
                    mysqli_stmt_bind_result($stmt_existingPhone, $existingAuthorId, $existingAuthorName);
                    mysqli_stmt_fetch($stmt_existingPhone);
                    $phone_err = "رقم الهاتف مستخدم بالفعل مع مكتبة: $existingAuthorName (معرف المكتبة: $existingAuthorId)";
                }
                mysqli_stmt_close($stmt_existingPhone);
            }
            
            // Validate secondary phone
            if (!empty($second_phone) && !preg_match($phonePattern, $second_phone)) {
                $second_phone_err = "رقم هاتف ثانوي غير صالح.";
            } else {
                // Check if secondary phone number already exists in the database (in phone or second_phone column)
                if (!empty($second_phone)) {
                    $existingSecondPhoneQuery = "SELECT id, library_name FROM libraries WHERE (phone = ? OR second_phone = ?) AND id != ?";
                    $stmt_existingSecondPhone = mysqli_prepare($conn, $existingSecondPhoneQuery);
                    mysqli_stmt_bind_param($stmt_existingSecondPhone, "ssi", $second_phone, $second_phone, $id);
                    mysqli_stmt_execute($stmt_existingSecondPhone);
                    mysqli_stmt_store_result($stmt_existingSecondPhone);
                    if (mysqli_stmt_num_rows($stmt_existingSecondPhone) > 0) {
                        mysqli_stmt_bind_result($stmt_existingSecondPhone, $existingAuthorId, $existingAuthorName);
                        mysqli_stmt_fetch($stmt_existingSecondPhone);
                        $second_phone_err = "رقم الهاتف الثانوي مستخدم بالفعل مع مكتبة: $existingAuthorName (معرف المكتبة: $existingAuthorId)";
                    }
                    mysqli_stmt_close($stmt_existingSecondPhone);
                }
            }

            // Validate email
            if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_err = "يرجى إدخال عنوان إيميل صالح.";
            } 
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
            if (empty($library_name_err) && empty($address_err) && empty($phone_err) && empty($second_phone_err) && empty($email_err) && empty($state_err) && empty($province_err) && empty($city_err) && empty($file_err)) {
             
            // Get location_id based on state, province, and city
            $location_query = "SELECT location_id FROM locations WHERE states = ? AND provinces = ? AND cities = ?";
            $stmt_location = mysqli_prepare($conn, $location_query);
            mysqli_stmt_bind_param($stmt_location, "sss", $state, $province, $city);
            mysqli_stmt_execute($stmt_location);
            mysqli_stmt_bind_result($stmt_location, $location_id);
            mysqli_stmt_fetch($stmt_location);
            mysqli_stmt_close($stmt_location);

            $uploadDirectory = "commercial_photos/"; 
            // Create the directory if it does not exist
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }
            $uploadedFile = '';
            $fileType = "";
            if (!empty($_FILES['uploadedFile']['name'])) {
            // Generate a unique filename
            $uniqueFileName = uniqid() . "_" . basename($_FILES['uploadedFile']['name']);
            $uploadedFile = $uploadDirectory . $uniqueFileName;
            // Get the file type from the uploaded file
            $fileType = $_FILES['uploadedFile']['type'];
            // Move the uploaded file to the destination directory
            move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $uploadedFile);
            }
     
            // Update the author data 
            if (!empty($uploadedFile)) {
                // If a new file is uploaded, update userfile and filetype
                $sql_update_library = "UPDATE libraries SET library_name = ?, library_last_name = ?, address = ?, phone = ?, second_phone = ?, email = ?, fbLink = ?, instaLink = ?, mapAddress = ?, tiktokLink = ?, notes = ?, userfile = ?, filetype = ?, location_id = ?, library_type_id = ?, library_percentage_id = ? WHERE id = ?";
                $stmt_update_library = mysqli_prepare($conn, $sql_update_library);
                mysqli_stmt_bind_param($stmt_update_library, "sssssssssssssiiii", $library_name, $library_last_name, $address, $phone, $second_phone, $email, $fbLink, $instaLink, $mapAddress, $tiktokLink, $notes, $uploadedFile, $fileType, $location_id, $library_type_id, $library_percentage_id, $id);
            } else {
                // If no new file is uploaded, don't update userfile and filetype
                $sql_update_library = "UPDATE libraries SET library_name = ?, library_last_name = ?, address = ?, phone = ?, second_phone = ?, email = ?, fbLink = ?, instaLink = ?, mapAddress = ?, tiktokLink = ?, notes = ?, location_id = ?, library_type_id = ?, library_percentage_id = ? WHERE id = ?";
                $stmt_update_library = mysqli_prepare($conn, $sql_update_library);
                mysqli_stmt_bind_param($stmt_update_library, "sssssssssssiiii", $library_name, $library_last_name, $address, $phone, $second_phone, $email, $fbLink, $instaLink, $mapAddress, $tiktokLink, $notes, $location_id, $library_type_id, $library_percentage_id, $id);
            }

                mysqli_stmt_execute($stmt_update_library);

                 // Redirect to the display_libraries page after successful update
                 $_SESSION['create_update_success'] = true;
                 header("Location: display_libraries.php");
                 exit;

        }

        }
            }
    ?>
             <form role="form" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
              <h4 class="mb-3">تحديث مكتبة</h4>
              <div class="border rounded p-4 shadow">
                 <h6 class="border-bottom pb-2 mb-3">تحديث معلومات المكتبة</h6>
                 <div class="row">
                    <div class="col-md-6 mt-4">
                        <div class="input-group input-group-outline mt-2">
                            <select name="library_type_id" id="library_type" class="form-control" required>
                                <option value="" disabled> -- اختر نوع المكتبة -- </option>
                                <?php
                                foreach ($libraryTypes as $type) {
                                    $selected = ($type['id'] == $library_type_id) ? 'selected' : ''; // Check if this option is selected
                                    echo '<option value="' . $type['id'] . '" ' . $selected . '>' . $type['library_type'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                    </div>
                    <div class="col-md-6 mt-4">
                        <div class="input-group input-group-outline mt-2">
                                <select name="library_percentage_id" id="library_percentage" class="form-control" required>
                                    <option value="" disabled> -- اختر نسبة المكتبة -- </option>
                                    <?php
                                    foreach ($libraryPercentages as $percentage) {
                                        $selected = ($percentage['id'] == $library_percentage_id) ? 'selected' : ''; // Check if this option is selected
                                        echo '<option value="' . $percentage['id'] . '" ' . $selected . '>' . $percentage['library_percentage'] . '</option>';
                                    }
                                    ?>
                                </select>
                        </div>
                    </div>

                 </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label">إسم المكتبة :</label>
                        <input type="text" name="library_name" class="form-control border pe-2 mb-3 <?php echo (!empty($library_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($library_name); ?>" required>
                        <span class="invalid-feedback"><?php echo $library_name_err; ?></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label">اسم شهرة المكتبة :</label>
                        <input type="text" name="library_last_name" class="form-control border pe-2 mb-3 <?php echo (!empty($library_last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($library_last_name); ?>">
                        <span class="invalid-feedback"><?php echo $library_last_name_err; ?></span>
                     </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label">الهاتف :</label>
                        <input type="text" name="phone" class="form-control border pe-2 mb-3 <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($phone); ?>" required>
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label">الهاتف الثاني :</label>
                        <input type="text" name="second_phone" class="form-control border pe-2 mb-3 <?php echo (!empty($second_phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($second_phone); ?>">
                        <span class="invalid-feedback"><?php echo $second_phone_err; ?></span>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                            <label class="form-label">العنوان :</label>
                            <input type="text" name="address" class="form-control border pe-2 mb-3 <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($address); ?>" required>
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label">الإيميل :</label>
                        <input type="email" name="email" class="form-control border pe-2 mb-3 <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>

        </div>
    
            <!-- Updete Book Section-->
            <div class="border rounded p-4 shadow mt-4">
                            <h6 class="border-bottom pb-2 mb-3">تحديث معلومات موقع المكتبة</h6>
                                <div class="d-flex">
                                    <div class="input-group input-group-outline my-3">
                                        <select name="state" id="state" class="form-control" required>
                                            <option value="" disabled>-- اختر الولاية --</option>
                                            <?php
                                            // Fetch states from the locations table
                                            $statesResult = mysqli_query($conn, "SELECT DISTINCT states FROM locations");
                                            while ($state = mysqli_fetch_assoc($statesResult)) {
                                                $state_name = htmlspecialchars($state["states"]);
                                                $selected = ($state_name == $selectedState) ? 'selected' : '';
                                                echo '<option value="' . $state_name . '" ' . $selected . '>' . $state_name . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                <div class="input-group input-group-outline my-3 me-3">
                                    <select name="province" id="province" class="form-control" required>
                                        <option value="" disabled selected>-- اختر الدائرة --</option>
                                        <!-- Options will be populated dynamically using JavaScript -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group input-group-outline my-3">
                                    <select name="city" id="city" class="form-control" required>
                                        <option value="" disabled selected>-- اختر المدينة --</option>
                                        <!-- Options will be populated dynamically using JavaScript -->
                                    </select>
                                </div>
                            </div>
        </div>
        
                <!-- Updete Social Section-->
                <div class="border rounded p-4 shadow mt-4">
                    <h6 class="border-bottom pb-2 mb-3">تحديث معلومات وسائل التواصل</h6>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط الفيسبوك :</label>
                                <input type="text" name="fbLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($fbLink); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط الإنستغرام :</label>
                                <input type="text" name="instaLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($instaLink); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط الموقع على خرائط قوقل :</label>
                                <input type="text" name="mapAddress" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($mapAddress); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">رابط التيكتوك :</label>
                                <input type="text" name="tiktokLink" class="form-control border pe-2 mb-3" value="<?php echo htmlspecialchars($tiktokLink); ?>">
                            </div>
                        </div>
                </div>

                        <!-- Updete Notes Section-->
                    <div class="border rounded p-4 shadow mt-4">
                        <h6 class="border-bottom pb-2 mb-3">تحديث الملاحظات + السجل التجاري</h6>
                                <div class="row">
                                    <div class="input-group input-group-outline col-md-6">    
                                        <input type="file" class="form-control <?php echo (!empty($file_err)) ? 'is-invalid' : ''; ?>" id="file" name="uploadedFile" />
                                        <span class="invalid-feedback"><?php echo $file_err; ?></span>
                                    </div>
                                    <div class="form-group col-md-12 my-3">
                                        <label for="notes" class="form-label">تحديث الملاحظات:</label>                   
                                        <textarea class="form-control border pe-2 mb-3" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($notes); ?></textarea>
                                    </div>
                                </div>
                    </div>

                           <div class="form-group mt-3">
                                <button type="submit" name="updateData" class="btn btn-primary">تحديث</button>
                            </div>
          </form>          
    <hr>
    <a href="display_libraries.php" class="btn btn-secondary">العودة إلى قائمة المؤلفين</a>
</div>
<script>
    const stateDropdown = document.getElementById('state');
    const provinceDropdown = document.getElementById('province');
    const cityDropdown = document.getElementById('city');

    // Get the state and province parameters from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const selectedState = urlParams.get('states');
    const selectedProvince = urlParams.get('province');
    const selectedCity = urlParams.get('city'); // Assuming you have this parameter in the URL

    // Pre-select the state dropdown based on the parameter
    if (selectedState) {
        const stateOption = stateDropdown.querySelector(`option[value="${selectedState}"]`);
        if (stateOption) {
            stateOption.selected = true;
        }
    }

    // Pre-select the province dropdown based on the parameter
    if (selectedProvince) {
        const provinceOption = provinceDropdown.querySelector(`option[value="${selectedProvince}"]`);
        if (provinceOption) {
            provinceOption.selected = true;
        }
    }

    // Function to fetch and populate cities
    function fetchCities(selectedProvince) {
        // Fetch cities based on the selected province using AJAX
        fetch(`get_cities_for_province.php?province_id=${selectedProvince}`)
            .then(response => response.json())
            .then(cities => {
                // Clear existing options
                cityDropdown.innerHTML = '<option value="" disabled selected>-- اختر المدينة --</option>';

                // Populate city options
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.city_name;
                    cityDropdown.appendChild(option);
                });

                // Pre-select the city dropdown based on the parameter
                if (selectedCity) {
                    const cityOption = cityDropdown.querySelector(`option[value="${selectedCity}"]`);
                    if (cityOption) {
                        cityOption.selected = true;
                    }
                }
            })
            .catch(error => console.error('Error fetching cities:', error));
    }

    // Function to fetch and populate provinces
    function fetchProvinces(selectedState) {
        // Fetch provinces based on the selected state using AJAX
        fetch(`get_provinces_for_state.php?state_id=${selectedState}`)
            .then(response => response.json())
            .then(provinces => {
                // Clear existing options
                provinceDropdown.innerHTML = '<option value="" disabled selected>-- اختر الدائرة --</option>';

                // Populate province options
                provinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id;
                    option.textContent = province.province_name;
                    provinceDropdown.appendChild(option);
                });

                // Pre-select the province dropdown based on the parameter
                if (selectedProvince) {
                    const provinceOption = provinceDropdown.querySelector(`option[value="${selectedProvince}"]`);
                    if (provinceOption) {
                        provinceOption.selected = true;
                    }
                }
            })
            .catch(error => console.error('Error fetching provinces:', error));
    }


    // Event listener for state dropdown change
stateDropdown.addEventListener('change', () => {
    const selectedState = stateDropdown.value;
    // Clear province and city dropdowns
    provinceDropdown.innerHTML = '<option value="" disabled selected>-- اختر الدائرة --</option>';
    cityDropdown.innerHTML = '<option value="" disabled selected>-- اختر المدينة --</option>';
    // Fetch and populate provinces for the selected state
    fetchProvinces(selectedState);
});

    // Event listener for province dropdown change
    provinceDropdown.addEventListener('change', () => {
        const selectedProvince = provinceDropdown.value;
        fetchCities(selectedProvince);
    });

    // Initial fetching of provinces and cities
    if (selectedState) {
        fetchProvinces(selectedState);
        if (selectedProvince) {
            fetchCities(selectedProvince);
        }
    }
</script>

<?php
// Close the database connection
mysqli_close($conn);
include('footer.php');
ob_end_flush();
?>
