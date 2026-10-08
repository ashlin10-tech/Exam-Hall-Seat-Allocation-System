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
   GET PARAMETERS
========================================================= */

$room_id = isset($_GET["room_id"])
    ? intval($_GET["room_id"])
    : 0;

$date = isset($_GET["date"])
    ? $_GET["date"]
    : "";

$session = isset($_GET["session"])
    ? $_GET["session"]
    : "";

/* =========================================================
   VALIDATION
========================================================= */

if (
    $room_id <= 0 ||
    empty($date) ||
    empty($session)
) {
    die("Invalid seating layout details.");
}

/* =========================================================
   GET ROOM DETAILS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        room_id,
        room_no,
        capacity
    FROM room
    WHERE room_id = ?
");

$stmt->bind_param(
    "i",
    $room_id
);

$stmt->execute();

$room_result = $stmt->get_result();

$room = $room_result->fetch_assoc();

$stmt->close();

if (!$room) {
    die("Hall not found.");
}

/* =========================================================
   GET INVIGILATOR
========================================================= */

$stmt = $conn->prepare("
    SELECT
        GROUP_CONCAT(
            DISTINCT CONCAT(
                f.faculty_code,
                ' - ',
                f.faculty_name
            )
            SEPARATOR ', '
        ) AS invigilator_name
    FROM exam_invigilator ei
    INNER JOIN faculty f
        ON ei.faculty_id = f.faculty_id
    WHERE ei.room_id = ?
");

$stmt->bind_param(
    "i",
    $room_id
);

$stmt->execute();

$invigilator_result = $stmt->get_result();

$invigilator_data =
    $invigilator_result->fetch_assoc();

$invigilator_name =
    !empty($invigilator_data["invigilator_name"])
    ? $invigilator_data["invigilator_name"]
    : "Not Assigned";

$stmt->close();

/* =========================================================
   GET SEATING DATA
========================================================= */

$stmt = $conn->prepare("
    SELECT
        sa.seat_no,
        sa.student_id,
        s.register_no,
        s.student_name,
        d.dept_name,
        e.subject,
        e.exam_type,
        e.exam_date,
        e.session
    FROM seat_allocation sa

    INNER JOIN student s
        ON sa.student_id = s.student_id

    LEFT JOIN department d
        ON s.dept_id = d.dept_id

    INNER JOIN exam e
        ON sa.exam_id = e.exam_id

    WHERE sa.room_id = ?
      AND sa.allocation_date = ?
      AND sa.session = ?

    ORDER BY
        CAST(
            SUBSTRING(sa.seat_no, 2)
            AS UNSIGNED
        ),
        LEFT(sa.seat_no, 1)
");

$stmt->bind_param(
    "iss",
    $room_id,
    $date,
    $session
);

$stmt->execute();

$result = $stmt->get_result();

/* =========================================================
   STORE SEATS
========================================================= */

$seats = [];

$total_students = 0;

$exam_type = "REGULAR";

$subjects = [];

while ($row = $result->fetch_assoc()) {

    $seats[$row["seat_no"]] = $row;

    $total_students++;

    if (!empty($row["exam_type"])) {
        $exam_type = $row["exam_type"];
    }

    if (
        !empty($row["subject"]) &&
        !in_array(
            $row["subject"],
            $subjects
        )
    ) {
        $subjects[] = $row["subject"];
    }
}

$stmt->close();

/* =========================================================
   DATE DISPLAY
========================================================= */

$date_object =
    DateTime::createFromFormat(
        "Y-m-d",
        $date
    );

if ($date_object) {

    $display_date =
        $date_object->format("d-m-Y");

} else {

    $display_date = $date;
}

/* =========================================================
   CREATE 30 BENCHES
=========================================================

   A1 + A2
   A3 + A4
   A5 + A6

   B1 + B2
   B3 + B4
   B5 + B6

   ...

   J1 + J2
   J3 + J4
   J5 + J6

========================================================= */

$letters = range("A", "J");

$benches = [];

$bench_number = 1;

for (
    $row = 1;
    $row <= 5;
    $row += 2
) {

    foreach (
        $letters as $letter
    ) {

        $seat1 =
            $letter . $row;

        $seat2 =
            $letter . ($row + 1);

        $benches[] = [
            "bench_no" => $bench_number,
            "seat1" => $seat1,
            "seat2" => $seat2
        ];

        $bench_number++;
    }
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
    Seating Layout
</title>

<style>

/* =========================================================
   GLOBAL
========================================================= */

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

    color: #222;
}

/* =========================================================
   HEADER
========================================================= */

.header {

    background: #800020;

    color: white;

    padding: 22px 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.header h1 {

    margin: 0;

    font-size: 26px;
}

.dashboard-btn {

    background: #5f0018;

    color: white;

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 7px;

    font-size: 15px;
}

.dashboard-btn:hover {

    background: #450012;
}

/* =========================================================
   PRINT BUTTON
========================================================= */

.print-section {

    display: flex;

    justify-content: flex-end;

    margin-bottom: 18px;
}

.print-btn {

    background: #800020;

    color: white;

    border: none;

    padding: 12px 22px;

    border-radius: 8px;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    box-shadow:
        0 3px 8px
        rgba(0,0,0,0.12);
}

.print-btn:hover {

    background: #5f0018;
}

/* =========================================================
   CONTAINER
========================================================= */

.container {

    width: 96%;

    max-width: 1400px;

    margin: 24px auto;
}

/* =========================================================
   INFORMATION
========================================================= */

.info-box {

    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.08);

    display: grid;

    grid-template-columns:
        repeat(6, 1fr);

    gap: 15px;

    margin-bottom: 20px;
}

.info-card {

    border:
        1px solid #d8d8d8;

    border-radius: 8px;

    padding: 14px;

    background: #fafafa;

    min-height: 75px;
}

.info-card strong {

    display: block;

    color: #800020;

    font-size: 15px;

    margin-bottom: 7px;
}

.info-card span {

    font-size: 14px;

    color: #333;

    word-break: break-word;
}

/* =========================================================
   LEGEND
========================================================= */

.legend {

    background: white;

    padding: 18px 22px;

    border-radius: 12px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.08);

    margin-bottom: 20px;
}

.legend-title {

    color: #800020;

    font-weight: bold;

    margin-bottom: 12px;
}

.legend-items {

    display: flex;

    gap: 30px;

    align-items: center;
}

.legend-item {

    display: flex;

    align-items: center;

    gap: 9px;
}

.legend-box {

    width: 25px;

    height: 25px;

    border-radius: 4px;

    border:
        1px solid #ccc;
}

.occupied {

    background: #800020;
}

.empty {

    background: #eeeeee;
}

/* =========================================================
   LAYOUT
========================================================= */

.layout-container {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.08);
}

/* =========================================================
   FRONT / BOARD
========================================================= */

.front-board {

    background: #333;

    color: white;

    text-align: center;

    font-size: 17px;

    font-weight: bold;

    padding: 12px;

    border-radius: 6px;

    margin-bottom: 25px;
}

/* =========================================================
   ROW LABELS
========================================================= */

.row-labels {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 18px;

    margin-bottom: 10px;
}

.row-label {

    background: #f5e1e6;

    color: #800020;

    border:
        1px solid #d9aab7;

    border-radius: 8px;

    text-align: center;

    padding: 10px;

    font-weight: bold;

    font-size: 14px;
}

/* =========================================================
   BENCH GRID

   5 COLUMNS × 6 ROWS

   Each visual column contains 6 benches.

========================================================= */

.bench-grid {

    display: grid;

    grid-template-columns:
        repeat(
            5,
            minmax(180px, 1fr)
        );

    grid-template-rows:
        repeat(6, auto);

    grid-auto-flow: column;

    gap: 18px;

    width: 100%;
}

/* =========================================================
   BENCH
========================================================= */

.bench {

    border:
        2px solid #800020;

    border-radius: 10px;

    overflow: hidden;

    background: white;

    min-height: 135px;

    box-shadow:
        0 3px 8px
        rgba(0,0,0,0.08);

    break-inside: avoid;
}

/* =========================================================
   BENCH HEADER
========================================================= */

.bench-header {

    background: #800020;

    color: white;

    text-align: center;

    padding: 8px;

    font-size: 14px;

    font-weight: bold;
}

/* =========================================================
   TWO SEATS
========================================================= */

.bench-seats {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    min-height: 100px;
}

/* =========================================================
   SEAT
========================================================= */

.seat {

    padding: 12px 6px;

    text-align: center;

    border-right:
        1px solid #dddddd;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    min-width: 0;
}

.seat:last-child {

    border-right: none;
}

/* =========================================================
   SEAT NUMBER
========================================================= */

.seat-number {

    color: #800020;

    font-size: 16px;

    font-weight: bold;

    margin-bottom: 6px;
}

/* =========================================================
   STUDENT
========================================================= */

.student-name {

    font-size: 13px;

    font-weight: bold;

    line-height: 1.3;

    margin-bottom: 4px;

    word-break: break-word;
}

.register-no {

    font-size: 11px;

    color: #555;

    margin-bottom: 3px;
}

.department {

    font-size: 11px;

    color: #800020;

    font-weight: bold;
}

/* =========================================================
   EMPTY
========================================================= */

.seat.empty {

    background: #eeeeee;
}

.seat.empty .seat-number {

    color: #999;
}

.empty-text {

    color: #888;

    font-size: 11px;
}

/* =========================================================
   FOOTER
========================================================= */

.footer {

    text-align: center;

    margin: 25px 0;

    color: #666;

    font-size: 13px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1200px) {

    .info-box {

        grid-template-columns:
            repeat(3, 1fr);
    }
}

@media (max-width: 900px) {

    .bench-grid {

        grid-template-columns:
            repeat(
                3,
                minmax(180px, 1fr)
            );

        grid-template-rows:
            repeat(10, auto);
    }

    .row-labels {

        grid-template-columns:
            repeat(3, 1fr);
    }
}

@media (max-width: 650px) {

    .header {

        padding: 18px;
    }

    .header h1 {

        font-size: 20px;
    }

    .info-box {

        grid-template-columns:
            1fr;
    }

    .bench-grid {

        grid-template-columns:
            1fr;

        grid-template-rows:
            repeat(30, auto);

        grid-auto-flow: row;
    }

    .row-labels {

        grid-template-columns:
            1fr;
    }

    .layout-container {

        padding: 12px;
    }

    .print-section {

        justify-content: center;
    }
}

/* =========================================================
   PRINT STYLES
========================================================= */

@media print {

    @page {

        size: A4 landscape;

        margin: 8mm;
    }

    html,
    body {

        background: white !important;

        margin: 0 !important;

        padding: 0 !important;
    }

    body {

        color: black !important;
    }

    /* Hide website navigation */

    .header {

        display: none !important;
    }

    .print-section {

        display: none !important;
    }

    .dashboard-btn {

        display: none !important;
    }

    .footer {

        display: none !important;
    }

    /* Main container */

    .container {

        width: 100% !important;

        max-width: none !important;

        margin: 0 !important;

        padding: 0 !important;
    }

    /* Information section */

    .info-box {

        display: grid !important;

        grid-template-columns:
            repeat(6, 1fr) !important;

        gap: 6px !important;

        padding: 0 !important;

        margin-bottom: 8px !important;

        box-shadow: none !important;

        border: none !important;
    }

    .info-card {

        min-height: auto !important;

        padding: 7px !important;

        border:
            1px solid #777 !important;

        border-radius: 3px !important;

        background: white !important;
    }

    .info-card strong {

        color: #000 !important;

        font-size: 8px !important;

        margin-bottom: 3px !important;
    }

    .info-card span {

        color: #000 !important;

        font-size: 8px !important;
    }

    /* Legend */

    .legend {

        padding: 5px !important;

        margin-bottom: 8px !important;

        box-shadow: none !important;

        border:
            1px solid #777 !important;

        border-radius: 3px !important;
    }

    .legend-title {

        color: #000 !important;

        font-size: 9px !important;

        margin-bottom: 4px !important;
    }

    .legend-items {

        gap: 15px !important;
    }

    .legend-item {

        font-size: 8px !important;
    }

    .legend-box {

        width: 12px !important;

        height: 12px !important;
    }

    /* Layout */

    .layout-container {

        padding: 8px !important;

        margin: 0 !important;

        box-shadow: none !important;

        border:
            1px solid #000 !important;

        border-radius: 3px !important;

        page-break-inside: avoid;
    }

    /* Front */

    .front-board {

        background: #333 !important;

        color: white !important;

        padding: 6px !important;

        margin-bottom: 7px !important;

        font-size: 10px !important;

        border-radius: 2px !important;

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;
    }

    /* Row labels */

    .row-labels {

        display: grid !important;

        grid-template-columns:
            repeat(5, 1fr) !important;

        gap: 7px !important;

        margin-bottom: 5px !important;
    }

    .row-label {

        background: #f5e1e6 !important;

        color: #800020 !important;

        border:
            1px solid #800020 !important;

        padding: 4px !important;

        font-size: 8px !important;

        border-radius: 2px !important;

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;
    }

    /* Bench grid */

    .bench-grid {

        display: grid !important;

        grid-template-columns:
            repeat(5, 1fr) !important;

        grid-template-rows:
            repeat(6, auto) !important;

        grid-auto-flow: column !important;

        gap: 7px !important;

        width: 100% !important;
    }

    /* Bench */

    .bench {

        min-height: 0 !important;

        height: 78px !important;

        border:
            1px solid #800020 !important;

        border-radius: 2px !important;

        box-shadow: none !important;

        page-break-inside: avoid;

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;
    }

    /* Bench header */

    .bench-header {

        background: #800020 !important;

        color: white !important;

        padding: 3px !important;

        font-size: 7px !important;

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;
    }

    /* Seats */

    .bench-seats {

        min-height: 0 !important;

        height: 60px !important;
    }

    .seat {

        padding: 3px 2px !important;

        min-height: 0 !important;

        height: 60px !important;

        border-right:
            1px solid #aaa !important;
    }

    .seat-number {

        color: #800020 !important;

        font-size: 8px !important;

        margin-bottom: 2px !important;
    }

    .student-name {

        color: #000 !important;

        font-size: 7px !important;

        line-height: 1.1 !important;

        margin-bottom: 2px !important;
    }

    .register-no {

        color: #333 !important;

        font-size: 6px !important;

        margin-bottom: 1px !important;
    }

    .department {

        color: #800020 !important;

        font-size: 6px !important;
    }

    .empty-text {

        color: #555 !important;

        font-size: 7px !important;
    }

    .seat.empty {

        background: #eeeeee !important;

        -webkit-print-color-adjust: exact;

        print-color-adjust: exact;
    }

}

</style>

</head>

<body>

<!-- =====================================================
     HEADER
===================================================== -->

<div class="header">

    <h1>
        Seating Layout
    </h1>

    <a
        href="admin_dashboard.php"
        class="dashboard-btn"
    >
        Dashboard
    </a>

</div>


<div class="container">

    <!-- =================================================
         PRINT BUTTON
    ================================================= -->

    <div class="print-section">

        <button
            type="button"
            class="print-btn"
            onclick="window.print()"
        >
            🖨 Print Seating Arrangement
        </button>

    </div>


    <!-- =================================================
         INFORMATION
    ================================================= -->

    <div class="info-box">

        <div class="info-card">

            <strong>
                Hall
            </strong>

            <span>
                <?= htmlspecialchars(
                    $room["room_no"]
                ) ?>
            </span>

        </div>


        <div class="info-card">

            <strong>
                Date
            </strong>

            <span>
                <?= htmlspecialchars(
                    $display_date
                ) ?>
            </span>

        </div>


        <div class="info-card">

            <strong>
                Session
            </strong>

            <span>
                <?= htmlspecialchars(
                    $session
                ) ?>
            </span>

        </div>


        <div class="info-card">

            <strong>
                Exam Type
            </strong>

            <span>
                <?= htmlspecialchars(
                    $exam_type
                ) ?>
            </span>

        </div>


        <div class="info-card">

            <strong>
                Students
            </strong>

            <span>
                <?= $total_students ?>
                /
                <?= htmlspecialchars(
                    $room["capacity"]
                ) ?>
            </span>

        </div>


        <div class="info-card">

            <strong>
                Invigilator
            </strong>

            <span>
                <?= htmlspecialchars(
                    $invigilator_name
                ) ?>
            </span>

        </div>

    </div>


    <!-- =================================================
         LEGEND
    ================================================= -->

    <div class="legend">

        <div class="legend-title">
            Seating Legend
        </div>

        <div class="legend-items">

            <div class="legend-item">

                <div
                    class="legend-box occupied"
                ></div>

                <span>
                    Occupied Seat
                </span>

            </div>


            <div class="legend-item">

                <div
                    class="legend-box empty"
                ></div>

                <span>
                    Empty Seat
                </span>

            </div>

        </div>

    </div>


    <!-- =================================================
         SEATING LAYOUT
    ================================================= -->

    <div class="layout-container">

        <div class="front-board">
            FRONT / BOARD
        </div>


        <!-- =================================================
             ROW LABELS
        ================================================= -->

        <div class="row-labels">

            <div class="row-label">
                ROW 1
            </div>

            <div class="row-label">
                ROW 2
            </div>

            <div class="row-label">
                ROW 3
            </div>

            <div class="row-label">
                ROW 4
            </div>

            <div class="row-label">
                ROW 5
            </div>

        </div>


        <div class="bench-grid">

            <?php foreach (
                $benches as $bench
            ): ?>

                <div class="bench">

                    <div class="bench-header">

                        Bench
                        <?= $bench["bench_no"] ?>

                    </div>


                    <div class="bench-seats">

                        <!-- =============================
                             SEAT 1
                        ============================== -->

                        <?php

                        $seat_no =
                            $bench["seat1"];

                        $student =
                            $seats[$seat_no]
                            ?? null;

                        ?>

                        <div class="seat
                            <?= $student
                                ? ""
                                : "empty"
                            ?>"
                        >

                            <div class="seat-number">

                                <?= htmlspecialchars(
                                    $seat_no
                                ) ?>

                            </div>


                            <?php if ($student): ?>

                                <div class="student-name">

                                    <?= htmlspecialchars(
                                        $student[
                                            "student_name"
                                        ]
                                    ) ?>

                                </div>


                                <div class="register-no">

                                    <?= htmlspecialchars(
                                        $student[
                                            "register_no"
                                        ]
                                    ) ?>

                                </div>


                                <div class="department">

                                    <?= htmlspecialchars(
                                        $student[
                                            "dept_name"
                                        ] ?? ""
                                    ) ?>

                                </div>

                            <?php else: ?>

                                <div class="empty-text">
                                    Empty
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- =============================
                             SEAT 2
                        ============================== -->

                        <?php

                        $seat_no =
                            $bench["seat2"];

                        $student =
                            $seats[$seat_no]
                            ?? null;

                        ?>

                        <div class="seat
                            <?= $student
                                ? ""
                                : "empty"
                            ?>"
                        >

                            <div class="seat-number">

                                <?= htmlspecialchars(
                                    $seat_no
                                ) ?>

                            </div>


                            <?php if ($student): ?>

                                <div class="student-name">

                                    <?= htmlspecialchars(
                                        $student[
                                            "student_name"
                                        ]
                                    ) ?>

                                </div>


                                <div class="register-no">

                                    <?= htmlspecialchars(
                                        $student[
                                            "register_no"
                                        ]
                                    ) ?>

                                </div>


                                <div class="department">

                                    <?= htmlspecialchars(
                                        $student[
                                            "dept_name"
                                        ] ?? ""
                                    ) ?>

                                </div>

                            <?php else: ?>

                                <div class="empty-text">
                                    Empty
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>


    <!-- =================================================
         FOOTER
    ================================================= -->

    <div class="footer">

        Exam Seat Allocation System

    </div>

</div>

</body>

</html>