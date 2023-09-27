
    </div>
  </main>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="material-icons py-2">settings</i>
    </a>
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-end">
          <h5 class="mt-3 mb-0">Okacha Libraries Dashboard</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-start mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-icons">clear</i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-end">
            <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-dark px-3 mb-2 active" data-class="bg-gradient-dark" onclick="sidebarType(this)">Dark</button>
          <button class="btn bg-gradient-dark px-3 mb-2 ms-2" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
          <button class="btn bg-gradient-dark px-3 mb-2 me-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <!-- Navbar Fixed -->
        <div class="mt-3 d-flex">
          <h6 class="mb-0">Navbar Fixed</h6>
          <div class="form-check form-switch me-auto my-auto">
            <input class="form-check-input mt-1 float-end me-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
          </div>
        </div>
        <hr class="horizontal dark my-3">
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch me-auto my-auto">
            <input class="form-check-input mt-1 float-end me-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
          </div>
        </div>
        <hr class="horizontal dark my-sm-4">
      </div>
    </div>
  </div>
  <?php
  
 
  include('../connect.php');
  $sessionUserId = $_SESSION['id']; 
  $userRole = $_SESSION['role'];
  if ($userRole === 'admin' || $userRole === 'manager') {
    // For admin users, count all libraries by library type
    $sql = "SELECT library_types.library_type, COUNT(libraries.id) AS library_count
            FROM library_types
            LEFT JOIN libraries ON library_types.id = libraries.library_type_id
            GROUP BY library_types.library_type";
} elseif ($userRole === 'member') {
    // For member users, count only their own libraries by library type
    $sql = "SELECT library_types.library_type, COUNT(libraries.id) AS library_count
            FROM library_types
            LEFT JOIN libraries ON library_types.id = libraries.library_type_id
            WHERE libraries.inserted_by = $sessionUserId
            GROUP BY library_types.library_type";
}

$result = mysqli_query($conn, $sql);

$labels = [];
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['library_type'];
        $data[] = $row['library_count'];
    }

    mysqli_free_result($result);
}
  
// Construct the SQL query based on the user's role
if ($userRole === 'admin' || $userRole === 'manager') {
  // For admin users, count libraries by state
  $sql = "SELECT locations.states, COUNT(libraries.id) AS library_count
          FROM locations
          LEFT JOIN libraries ON locations.location_id = libraries.location_id
          GROUP BY locations.states";
} elseif ($userRole === 'member') {
  // For member users, count their own libraries by state
  $sql = "SELECT locations.states, COUNT(libraries.id) AS library_count
          FROM locations
          LEFT JOIN libraries ON locations.location_id = libraries.location_id
          WHERE libraries.inserted_by = $sessionUserId
          GROUP BY locations.states";
}

$result = mysqli_query($conn, $sql);

$stateLabels = [];
$libraryCountsByState = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $stateLabels[] = $row['states'];
        $libraryCountsByState[] = $row['library_count'];
    }

    mysqli_free_result($result);
}


// Construct the SQL query based on the user's role
if ($userRole === 'admin' || $userRole === 'manager') {
  // For admin users, count all libraries for each library type
  $sql = "SELECT library_percentages.library_percentage, COUNT(libraries.id) AS library_percentage_count
          FROM library_percentages
          LEFT JOIN libraries ON library_percentages.id = libraries.library_percentage_id
          GROUP BY library_percentages.library_percentage";
} elseif ($userRole === 'member') {
  // For member users, count only their own libraries for each library type
  $sql = "SELECT library_percentages.library_percentage, COUNT(libraries.id) AS library_percentage_count
          FROM library_percentages
          LEFT JOIN libraries ON library_percentages.id = libraries.library_percentage_id
          WHERE libraries.inserted_by = $sessionUserId
          GROUP BY library_percentages.library_percentage";
}

$result = mysqli_query($conn, $sql);

$percentageLabels = [];
$percentageData = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $percentageLabels[] = $row['library_percentage'];
        $percentageData[] = $row['library_percentage_count'];
    }

    mysqli_free_result($result);
}
  ?>
                        <!-- logOut Modal -->
                        <div class="modal fade" id="logOutModal" tabindex="-1" aria-labelledby="logOutModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                              <div class="modal-content">
                                  <div class="modal-header"> 
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body text-center">
                                      <h5 class="modal-title mb-3">
                                        <i class="fas fa-sign-out-alt fa-rotate-180 fa-lg text-warning" style="font-size: 150px;"></i>
                                      </h5>
                                      <p>هل أنت متأكد من تسجيل الخروج؟</p>
                                  </div>
                                  <div class="modal-footer d-flex justify-content-center">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">غلق</button>
                                      <a class="btn btn-warning" href="../logout.php">تسجيل الخروج</a>
                                  </div>
                              </div>
                          </div>
                        </div>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script>
    
    var ctx = document.getElementById("chart-bars").getContext("2d");

    new Chart(ctx, {
      type: "bar",
            data: {
                labels: <?php echo json_encode($labels);?>,
                datasets: [{
                    label: "عدد المكتبات",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "rgba(255, 255, 255, .8)",
                    data: <?php echo json_encode($data); ?>,
                    maxBarThickness: 6
                }],
            },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
              color: "#fff"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });


    var ctx2 = document.getElementById("chart-line").getContext("2d");

    new Chart(ctx2, {
  type: "bar", 
  data: {
    labels: <?php echo json_encode($stateLabels); ?>,
    datasets: [{
      label: "عدد المكتبات",
      backgroundColor: "rgba(255, 255, 255, .8)",
      data: <?php echo json_encode($libraryCountsByState); ?>,
      borderWidth: 0,
      borderRadius: 4,
      maxBarThickness: 50
    }],
  },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

    new Chart(ctx3, {
      type: "bar",
            data: {
                labels: <?php echo json_encode($percentageLabels);?>,
                datasets: [{
                    label: "عدد المكتبات",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "rgba(255, 255, 255, .8)",
                    data: <?php echo json_encode($percentageData); ?>,
                    maxBarThickness: 6
                }],
            },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
              color: "#fff"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    
  </script>
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
  <script src="../assets/js/material-dashboard.min.js?v=3.0.0"></script>
</body>

</html>