<?php

// Refresh Button Component
function renderRefreshButton() {
    echo '
        <div style="position: absolute; right: 900px;">
            <button onclick="location.reload()" class="refresh-button">Refresh Page</button>
        </div>
    ';
}

// Render the refresh button
renderRefreshButton();
?>

<style>
    .refresh-button {
        background-color: #ff0000; /* Red color */
        color: #ffffff;
        border: none;
        padding: 10px 20px;
        font-size: 1.5em;
        border-radius: 5px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .refresh-button:hover {
        background-color: #cc0000; /* Darker shade on hover */
    }
</style>
