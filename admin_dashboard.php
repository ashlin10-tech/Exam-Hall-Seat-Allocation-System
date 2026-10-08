<?php
session_start();
require_once "db_connect.php";

/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit;
}

/* =========================================================
   ADMIN DETAILS
========================================================= */

$admin_username =
    $_SESSION["admin_username"] ?? "Admin";

/* =========================================================
   STATISTICS
========================================================= */

/* Students */

$student_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM student
");

$student_count =
    $student_result->fetch_assoc()["total"];


/* Exams */

$exam_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM exam
");

$exam_count =
    $exam_result->fetch_assoc()["total"];


/* Halls */

$room_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM room
");

$room_count =
    $room_result->fetch_assoc()["total"];


/* Faculty */

$faculty_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM faculty
");

$faculty_count =
    $faculty_result->fetch_assoc()["total"];


/* Allocated Students */

$allocation_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM seat_allocation
");

$allocation_count =
    $allocation_result->fetch_assoc()["total"];


/* Invigilator Assignments */

$invigilator_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM exam_invigilator
");

$invigilator_count =
    $invigilator_result->fetch_assoc()["total"];


/* =========================================================
   TOTAL HALL CAPACITY
========================================================= */

$capacity_result = $conn->query("
    SELECT COALESCE(
        SUM(capacity),
        0
    ) AS total
    FROM room
");

$total_capacity =
    $capacity_result->fetch_assoc()["total"];


/* =========================================================
   ALLOCATION CAPACITY
========================================================= */

$allocation_percentage = 0;

if ($total_capacity > 0) {

    $allocation_percentage =
        round(
            (
                $allocation_count /
                $total_capacity
            ) * 100
        );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Admin Dashboard
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #fdf4f6;

    color: #333;
}

/* =========================================================
   HEADER
========================================================= */

.header {

    background: #800020;

    color: white;

    padding: 20px 35px;

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    box-shadow:
        0 3px 10px
        rgba(0,0,0,0.15);
}

.header-left h1 {

    margin: 0;

    font-size: 27px;
}

.header-left p {

    margin: 5px 0 0;

    font-size: 14px;

    opacity: 0.9;
}

.header-right {

    display: flex;

    align-items: center;

    gap: 10px;
}

.welcome {

    font-size: 14px;

    margin-right: 8px;
}

/* =========================================================
   HEADER BUTTONS
========================================================= */

.change-password {

    background: #198754;

    color: white;

    text-decoration: none;

    padding: 10px 16px;

    border-radius: 6px;

    font-size: 14px;

    font-weight: bold;
}

.change-password:hover {

    background: #146c43;
}

.logout {

    background: #dc3545;

    color: white;

    text-decoration: none;

    padding: 10px 16px;

    border-radius: 6px;

    font-size: 14px;

    font-weight: bold;
}

.logout:hover {

    background: #bb2d3b;
}

/* =========================================================
   MAIN
========================================================= */

.container {

    width: 94%;

    max-width: 1400px;

    margin: 30px auto;
}

/* =========================================================
   WELCOME SECTION
========================================================= */

.welcome-box {

    background: white;

    padding: 22px 25px;

    border-radius: 12px;

    margin-bottom: 25px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.07);
}

.welcome-box h2 {

    margin: 0;

    color: #800020;

    font-size: 24px;
}

.welcome-box p {

    margin: 7px 0 0;

    color: #666;

    font-size: 14px;
}

/* =========================================================
   STATISTICS
========================================================= */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(6, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}

.stat-card {

    background: white;

    border-radius: 12px;

    padding: 20px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.07);

    border-top:
        5px solid #800020;

    text-align: center;

    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.stat-card:hover {

    transform:
        translateY(-4px);

    box-shadow:
        0 7px 20px
        rgba(0,0,0,0.12);
}

.stat-icon {

    font-size: 28px;

    margin-bottom: 8px;
}

.stat-number {

    color: #800020;

    font-size: 28px;

    font-weight: bold;

    margin-bottom: 5px;
}

.stat-label {

    color: #666;

    font-size: 13px;

    font-weight: bold;
}

/* =========================================================
   ALLOCATION SUMMARY
========================================================= */

.summary-box {

    background: white;

    border-radius: 12px;

    padding: 22px 25px;

    margin-bottom: 30px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.07);
}

.summary-header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 15px;
}

.summary-header h3 {

    margin: 0;

    color: #800020;

    font-size: 19px;
}

.summary-number {

    color: #800020;

    font-weight: bold;
}

/* =========================================================
   PROGRESS BAR
========================================================= */

.progress-background {

    width: 100%;

    height: 18px;

    background: #eeeeee;

    border-radius: 20px;

    overflow: hidden;
}

.progress-bar {

    height: 100%;

    width:
        <?= min(
            $allocation_percentage,
            100
        ) ?>%;

    background: #800020;

    border-radius: 20px;

    transition:
        width 0.5s ease;
}

.summary-text {

    margin-top: 10px;

    font-size: 13px;

    color: #666;
}

/* =========================================================
   SECTION TITLE
========================================================= */

.section-title {

    color: #800020;

    font-size: 21px;

    margin:
        0 0 18px;
}

/* =========================================================
   DASHBOARD CARDS
========================================================= */

.dashboard-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;
}

.dashboard-card {

    background: white;

    border-radius: 12px;

    padding: 25px 20px;

    text-align: center;

    text-decoration: none;

    color: #333;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.07);

    border:
        1px solid #eeeeee;

    transition:
        transform 0.2s,
        box-shadow 0.2s,
        border-color 0.2s;
}

.dashboard-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 8px 20px
        rgba(0,0,0,0.12);

    border-color:
        #800020;
}

.card-icon {

    font-size: 36px;

    margin-bottom: 12px;
}

.card-title {

    font-size: 17px;

    font-weight: bold;

    color: #800020;

    margin-bottom: 7px;
}

.card-description {

    font-size: 12px;

    color: #777;

    line-height: 1.4;
}

/* =========================================================
   FOOTER
========================================================= */

.footer {

    text-align: center;

    color: #777;

    font-size: 13px;

    margin: 35px 0 20px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1200px) {

    .stats-grid {

        grid-template-columns:
            repeat(3, 1fr);
    }

    .dashboard-grid {

        grid-template-columns:
            repeat(3, 1fr);
    }
}

@media (max-width: 800px) {

    .header {

        padding: 18px;
    }

    .header-right {

        flex-wrap: wrap;

        justify-content: flex-end;
    }

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .dashboard-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }
}

@media (max-width: 550px) {

    .header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

    .header-right {

        justify-content: flex-start;
    }

    .stats-grid {

        grid-template-columns: 1fr;
    }

    .dashboard-grid {

        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<!-- =====================================================
     HEADER
===================================================== -->

<div class="header">

    <div class="header-left">

        <h1>
            Exam Hall Seat Allocation System
        </h1>

        <p>
            Administrator Panel
        </p>

    </div>


    <div class="header-right">

        <span class="welcome">

            Welcome,
            <strong>
                <?= htmlspecialchars(
                    $admin_username
                ) ?>
            </strong>

        </span>


        <a
            href="admin_change_password.php"
            class="change-password"
        >
            Change Password
        </a>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="container">

    <!-- WELCOME -->

    <div class="welcome-box">

        <h2>
            Admin Dashboard
        </h2>

        <p>
            Manage students, examinations,
            halls, faculty and seat allocation
            from one place.
        </p>

    </div>


    <!-- =================================================
         STATISTICS
    ================================================= -->

    <div class="stats-grid">

        <!-- STUDENTS -->

        <div class="stat-card">

            <div class="stat-icon">
                👨‍🎓
            </div>

            <div class="stat-number">
                <?= number_format(
                    $student_count
                ) ?>
            </div>

            <div class="stat-label">
                Total Students
            </div>

        </div>


        <!-- EXAMS -->

        <div class="stat-card">

            <div class="stat-icon">
                📝
            </div>

            <div class="stat-number">
                <?= number_format(
                    $exam_count
                ) ?>
            </div>

            <div class="stat-label">
                Total Exams
            </div>

        </div>


        <!-- HALLS -->

        <div class="stat-card">

            <div class="stat-icon">
                🏫
            </div>

            <div class="stat-number">
                <?= number_format(
                    $room_count
                ) ?>
            </div>

            <div class="stat-label">
                Total Halls
            </div>

        </div>


        <!-- FACULTY -->

        <div class="stat-card">

            <div class="stat-icon">
                👩‍🏫
            </div>

            <div class="stat-number">
                <?= number_format(
                    $faculty_count
                ) ?>
            </div>

            <div class="stat-label">
                Total Faculty
            </div>

        </div>


        <!-- ALLOCATED -->

        <div class="stat-card">

            <div class="stat-icon">
                💺
            </div>

            <div class="stat-number">
                <?= number_format(
                    $allocation_count
                ) ?>
            </div>

            <div class="stat-label">
                Allocated Seats
            </div>

        </div>


        <!-- INVIGILATORS -->

        <div class="stat-card">

            <div class="stat-icon">
                📋
            </div>

            <div class="stat-number">
                <?= number_format(
                    $invigilator_count
                ) ?>
            </div>

            <div class="stat-label">
                Invigilator Assignments
            </div>

        </div>

    </div>


    <!-- =================================================
         ALLOCATION SUMMARY
    ================================================= -->

    <div class="summary-box">

        <div class="summary-header">

            <h3>
                Seat Allocation Overview
            </h3>

            <div class="summary-number">

                <?= number_format(
                    $allocation_count
                ) ?>

                /

                <?= number_format(
                    $total_capacity
                ) ?>

                seats

            </div>

        </div>


        <div class="progress-background">

            <div class="progress-bar"></div>

        </div>


        <div class="summary-text">

            <?= $allocation_percentage ?>%
            of the total hall capacity
            is currently allocated.

        </div>

    </div>


    <!-- =================================================
         MANAGEMENT
    ================================================= -->

    <h2 class="section-title">
        Management
    </h2>


    <div class="dashboard-grid">

        <!-- 1 -->

        <a
            href="student_form.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                👨‍🎓
            </div>

            <div class="card-title">
                Add Student
            </div>

            <div class="card-description">
                Add student details to
                the system.
            </div>

        </a>


        <!-- 2 -->

        <a
            href="manage_students.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                📋
            </div>

            <div class="card-title">
                Manage Students
            </div>

            <div class="card-description">
                View and manage
                registered students.
            </div>

        </a>


        <!-- 3 -->

        <a
            href="exam_form.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                📝
            </div>

            <div class="card-title">
                Add Exam
            </div>

            <div class="card-description">
                Create a new
                examination.
            </div>

        </a>


        <!-- 4 -->

        <a
            href="delete_exam.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🗑️
            </div>

            <div class="card-title">
                Delete Exam
            </div>

            <div class="card-description">
                Remove an existing
                examination.
            </div>

        </a>


        <!-- 5 -->

        <a
            href="room_form.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🏫
            </div>

            <div class="card-title">
                Add Hall
            </div>

            <div class="card-description">
                Add examination halls
                and capacity.
            </div>

        </a>


        <!-- 6 -->

        <a
            href="delete_room.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🗑️
            </div>

            <div class="card-title">
                Delete Hall
            </div>

            <div class="card-description">
                Remove an existing
                examination hall.
            </div>

        </a>


        <!-- 7 -->

        <a
            href="seat_allocation.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                💺
            </div>

            <div class="card-title">
                Allocate Seats
            </div>

            <div class="card-description">
                Automatically allocate
                students to seats.
            </div>

        </a>


        <!-- 8 -->

        <a
            href="view.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                👁️
            </div>

            <div class="card-title">
                View Allocations
            </div>

            <div class="card-description">
                View and manage
                seat allocations.
            </div>

        </a>


        <!-- 9 -->

        <a
            href="seating_layout_select.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                🪑
            </div>

            <div class="card-title">
                Seating Layout
            </div>

            <div class="card-description">
                View the complete
                hall seating arrangement.
            </div>

        </a>


        <!-- 10 -->

        <a
            href="faculty_form.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                👩‍🏫
            </div>

            <div class="card-title">
                Add Faculty
            </div>

            <div class="card-description">
                Register faculty members
                for invigilation.
            </div>

        </a>


        <!-- 11 -->

        <a
            href="manage_faculty.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                👥
            </div>

            <div class="card-title">
                Manage Faculty
            </div>

            <div class="card-description">
                View and manage
                faculty records.
            </div>

        </a>


        <!-- 12 -->

        <a
            href="assign_invigilator.php"
            class="dashboard-card"
        >

            <div class="card-icon">
                📋
            </div>

            <div class="card-title">
                Assign Invigilator
            </div>

            <div class="card-description">
                Assign faculty members
                to examination halls.
            </div>

        </a>

    </div>


    <!-- =================================================
         FOOTER
    ================================================= -->

    <div class="footer">

        Exam Hall Seat Allocation System

        <br>

        Administrator Panel

    </div>

</div>

</body>

</html>