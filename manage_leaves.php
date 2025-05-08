<?php
session_start();
require_once "config.php";

// Ensure only admin can access this action
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['action'], $_GET['id'])) {
    $leave_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Determine new status based on action
    if ($action === 'approve') {
        $status = 'Approved';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } else {
        die("Invalid action.");
    }

    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE leaves SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $leave_id);

    if ($stmt->execute()) {
        $stmt->close();
        
        // Redirect to the reports page after the action is taken
        header("Location:reports.php");  // Modify this to your actual reports page URL
        exit();
    } else {
        echo "Failed to update status.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>

