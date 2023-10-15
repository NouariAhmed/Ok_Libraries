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

  var progressContainer = document.querySelector(".progress-container");
  var progressBar = document.getElementById("myProgressBar");
  var progressText = document.querySelector(".progress-text");
  var successMessage = document.getElementById("successMessage");

  // Simulate progress
  var progress = 0;
  var interval = setInterval(function () {
      progress += 10;
      progressBar.style.width = progress + "%";
      progressText.textContent = "يتم إضافة المكتبة " + progress + "%";
      if (progress >= 100) {
          clearInterval(interval);
          progressContainer.style.display = "none"; // Hide the entire progress container
          successMessage.style.display = "block";
      }
  }, 250);

   // Get references to the select elements
const stateSelect = document.getElementById('state');
const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');

// Disable the Province and City selects by default
provinceSelect.disabled = true;
citySelect.disabled = true;

// Event listener to handle State selection
stateSelect.addEventListener('change', function () {
    const selectedState = stateSelect.value;
    // If no state is selected, clear and disable the Province and City selects
    if (selectedState === '') {
        clearProvinceAndCity();
    } else {
        // Fetch provinces based on the selected state from the server using Ajax
        fetchProvinces(selectedState);
    }
});

// Event listener to handle Province selection
provinceSelect.addEventListener('change', function () {
    const selectedProvince = provinceSelect.value;
    // If no province is selected, disable the City select and show appropriate message
    if (selectedProvince === '') {
        clearCity();
    } else {
        // Fetch cities based on the selected province from the server using Ajax
        fetchCities(selectedProvince);
    }
});


// Function to fetch provinces using Ajax
function fetchProvinces(state) {
  fetch('get_provinces.php?state=' + state)
    .then(response => response.json())
    .then(data => {
      // Generate the Province select options
      const provinceOptions = data.map(province => `<option value="${province}">${province}</option>`);
      // Display the Province select
      provinceSelect.innerHTML = '<option value="">-- إختر الدائرة --</option>' + provinceOptions.join('');
      // Enable the Province select
      provinceSelect.disabled = false;
      // Clear and disable the City select
      clearCity();
    })
    .catch(error => console.error('حدث خطأ:', error));
}

// Function to fetch cities using Ajax
function fetchCities(province) {
  fetch('get_cities.php?province=' + province)
    .then(response => response.json())
    .then(data => {
      // Generate the City select options
      const cityOptions = data.map(city => `<option value="${city.cities}">${city.cities}</option>`);
      // Display the City select
      citySelect.innerHTML = '<option value="">-- إختر البلدية --</option>' + cityOptions.join('');
      // Enable the City select
      citySelect.disabled = false;
    })
    .catch(error => console.error('حدث خطأ:', error));
}

// Function to clear and disable the City select
function clearCity() {
  citySelect.innerHTML = '<option value="">-- إختر البلدية --</option>';
  citySelect.disabled = true;
}

// Function to clear and disable the Province and City selects
function clearProvinceAndCity() {
  provinceSelect.innerHTML = '<option value="">-- إختر الدائرة --</option>';
  provinceSelect.disabled = true;
  clearCity();
}