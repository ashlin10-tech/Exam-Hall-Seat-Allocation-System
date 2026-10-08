<?php
session_start();
require_once "db_connect.php";

/* =========================================================
   FACULTY LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["faculty_logged_in"]) ||
    $_SESSION["faculty_logged_in"] !== true
) {
    header("Location: faculty_login.php");
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

$room_result =
    $stmt->get_result();

$room =
    $room_result->fetch_assoc();

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

$invigilator_result =
    $stmt->get_result();

$invigilator_data =
    $invigilator_result->fetch_assoc();

$invigilator_name =
    !empty(
        $invigilator_data[
            "invigilator_name"
        ]
    )
    ? $invigilator_data[
        "invigilator_name"
    ]
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
        ON sa.student_id =
           s.student_id

    LEFT JOIN department d
        ON s.dept_id =
           d.dept_id

    INNER JOIN exam e
        ON sa.exam_id =
           e.exam_id

    WHERE sa.room_id = ?
      AND sa.allocation_date = ?
      AND sa.session = ?

    ORDER BY

        CAST(
            SUBSTRING(
                sa.seat_no,
                2
            )
            AS UNSIGNED
        ),

        LEFT(
            sa.seat_no,
            1
        )
");

$stmt->bind_param(
    "iss",
    $room_id,
    $date,
    $session
);

$stmt->execute();

$result =
    $stmt->get_result();

/* =========================================================
   STORE SEATS
========================================================= */

$seats = [];

$total_students = 0;

$exam_type = "REGULAR";

while (
    $row =
    $result->fetch_assoc()
) {

    $seats[
        $row["seat_no"]
    ] = $row;

    $total_students++;

    if (
        !empty(
            $row["exam_type"]
        )
    ) {

        $exam_type =
            $row["exam_type"];
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
        $date_object->format(
            "d-m-Y"
        );

} else {

    $display_date =
        $date;
}

/* =========================================================
   CREATE 30 BENCHES
========================================================= */

$letters =
    range("A", "J");

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
            $letter .
            ($row + 1);

        $benches[] = [

            "bench_no" =>
                $bench_number,

            "seat1" =>
                $seat1,

            "seat2" =>
                $seat2

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
    Faculty Seating Layout
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

    background: #eef8f1;

    color: #222;
}

/* =========================================================
   HEADER
========================================================= */

.header {

    background: #087f5b;

    color: white;

    padding: 22px 30px;

    display: flex;

    justify-content:
        space-between;

    align-items: center;
}

.header h1 {

    margin: 0;

    font-size: 26px;
}

.dashboard-btn {

    background: #075f45;

    color: white;

    text-decoration: none;

    padding: 11px 20px;

    border-radius: 7px;

    font-size: 15px;
}

.dashboard-btn:hover {

    background: #064b37;
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

    color: #087f5b;

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

    color: #087f5b;

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

    background: #087f5b;
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
   BENCH GRID

   5 COLUMNS × 6 ROWS
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
        2px solid #087f5b;

    border-radius: 10px;

    overflow: hidden;

    background: white;

    min-height: 135px;

    box-shadow:
        0 3px 8px
        rgba(0,0,0,0.08);
}

/* =========================================================
   BENCH HEADER
========================================================= */

.bench-header {

    background: #087f5b;

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
}

.seat:last-child {

    border-right: none;
}

/* =========================================================
   SEAT NUMBER
========================================================= */

.seat-number {

    color: #087f5b;

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
}

.register-no {

    font-size: 11px;

    color: #555;

    margin-bottom: 3px;
}

.department {

    font-size: 11px;

    color: #087f5b;

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

    .layout-container {

        padding: 12px;
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
        Faculty Seating Layout
    </h1>

    <a
        href="faculty_dashboard.php"
        class="dashboard-btn"
    >
        Dashboard
    </a>

</div>


<div class="container">

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


        <!-- NEW INVIGILATOR -->

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


    <div class="footer">

        Exam Seat Allocation System

    </div>

</div>

</body>

</html>