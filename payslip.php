<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "workpausedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["employee_id"])) {
    $emp_id = $_POST["employee_id"];
    $res = $conn->query("SELECT * FROM payroll WHERE employee_id='$emp_id' ORDER BY id DESC LIMIT 1");

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $leave_q = $conn->query("SELECT COUNT(*) as leave_days FROM leaves WHERE employee_id='$emp_id' AND status='Approved'");
        $leave = $leave_q->fetch_assoc();
        $leave_deduction = ($row["basic_salary"] / 30) * ($leave["leave_days"] ?? 0);
        $total_deductions = $row["deductions"] + $leave_deduction;
        $net_salary = ($row["basic_salary"] + $row["allowances"]) - $total_deductions;
    } else {
        die("Payroll not found.");
    }
} else {
    die("Invalid access.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <style>
        body {
            font-family: 'Arial';
            padding: 40px;
            background-color: #f4f4f4;
        }
        .back-btn {
            display: block;
            text-align: center;
            margin: 15px auto 0;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
        }
        .back-btn:hover {
            background: #5a6268;
        }

        .slip {
            width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        h2, h3 {
            text-align: center;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .print-btn {
            display: block;
            margin: 30px auto;
            padding: 10px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="slip">
    <h2>Employee Payslip</h2>
    <h3>Employee ID: <?= $emp_id ?></h3>
    <table>
        <tr><td>Basic Salary</td><td><?= number_format($row["basic_salary"], 2) ?> INR</td></tr>
        <tr><td>Allowances</td><td><?= number_format($row["allowances"], 2) ?> INR</td></tr>
        <tr><td>Leave Deduction</td><td><?= number_format($leave_deduction, 2) ?> INR</td></tr>
        <tr><td>Other Deductions</td><td><?= number_format($row["deductions"], 2) ?> INR</td></tr>
        <tr><td><strong>Net Salary</strong></td><td><strong><?= number_format($net_salary, 2) ?> INR</strong></td></tr>
    </table>
    <button onclick="window.print()" class="print-btn">Print / Save as PDF</button>
    <a href="payroll.php" class="back-btn">← Back to Dashboard</a>

</div>

</body>
</html>