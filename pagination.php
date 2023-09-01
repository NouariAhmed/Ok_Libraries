<div id="pagination-controls" class="text-center mt-4">
    <?php
    // Custom code for fixed number of displayed buttons (6 buttons)
    $range = 2; // number of Btns displayed on page
    $startPage = max(1, $currentPage - $range);
    $endPage = min($totalPages, $currentPage + $range);
    $prevPage = max(1, $currentPage - 1);
    $nextPage = min($totalPages, $currentPage + 1);

    // Construct the filter query string
    $filterQueryArray = $_GET; // Copy the GET parameters
    unset($filterQueryArray['page']); // Remove the existing 'page' parameter
    $filterQueryString = http_build_query($filterQueryArray);

    // Display "Previous" button with "disabled" style if on first page
    echo '<a href="?page=' . $prevPage . '&' . $filterQueryString . '" class="btn btn-secondary text-white ' . ($currentPage === 1 ? 'disabled' : '') . '">السابق</a>';

    // Display page numbers with ellipsis
    if ($startPage > 1) {
        echo '<a href="?page=1&' . $filterQueryString . '" class="btn btn-secondary text-white mx-1">1</a>';
        if ($startPage > 2) {
            echo '<span>...</span>';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i === $currentPage) ? 'active' : '';
        echo '<a href="?page=' . $i . '&' . $filterQueryString . '" class="btn btn-secondary text-white mx-1 ' . $activeClass . '">' . $i . '</a>';
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            echo '<span>...</span>';
        }
        echo '<a href="?page=' . $totalPages . '&' . $filterQueryString . '" class="btn btn-secondary text-white mx-1">' . $totalPages . '</a>';
    }

    // Display "Next" button with "disabled" style if on last page
    echo '<a href="?page=' . $nextPage . '&' . $filterQueryString . '" class="btn btn-secondary text-white ' . ($currentPage == $totalPages || $totalPages === 1 ? 'disabled' : '') . '">التالي</a>';
    ?>
</div>
