<?php
// includes/functions.php

// Keep only utility functions here, no auth functions
// For example:
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

// Other utility functions...