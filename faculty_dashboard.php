<?php

session_start();
include 'db_connect.php';

/* =========================================================
   CHECK FACULTY LOGIN
========================================================= */

if (
    !isset($_SESSION['faculty_logged_in']) ||
    $_SESSION['faculty_logged_in'] !== true
) {
    header("Location: faculty_login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];
$faculty_name = $_SESSION['faculty_name'];
$faculty_department = $_SESSION['faculty_department'];


/* =========================================================
   GET HALLS ASSIGNED TO THIS FACULTY
========================================================= */

$sql = "
    SELECT
        r.room_id,
        r.room_no,
        r.capacity
    FROM exam_invigilator ei
    INNER JOIN room r
        ON ei.room_id = r.room_id
    WHERE ei.faculty_id = ?
    ORDER BY r.room_no
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();

$rooms_result = $stmt->get_result();

$rooms = [];


/* =========================================================
   GET EXAMINATION DETAILS FOR EACH HALL
========================================================= */

while ($room = $rooms_result->fetch_assoc()) {

    $room_id = $room['room_id'];

    /*
       Get:
       - Exam date
       - FN / AN session
       - Subject
       - Exam type
       - Number of students
    */

    $session_sql = "
        SELECT
            sa.allocation_date,
            sa.session,

            GROUP_CONCAT(
                DISTINCT e.subject
                ORDER BY e.subject
                SEPARATOR ', '
            ) AS subjects,

            GROUP_CONCAT(
                DISTINCT e.exam_type
                ORDER BY e.exam_type
                SEPARATOR ', '
            ) AS exam_types,

            COUNT(DISTINCT sa.student_id) AS student_count

        FROM seat_allocation sa

        INNER JOIN exam e
            ON sa.exam_id = e.exam_id

        WHERE sa.room_id = ?

        GROUP BY
            sa.allocation_date,
            sa.session

        ORDER BY
            sa.allocation_date DESC,
            sa.session
    ";

    $session_stmt = $conn->prepare($session_sql);
    $session_stmt->bind_param("i", $room_id);
    $session_stmt->execute();

    $session_result = $session_stmt->get_result();

    $sessions = [];

    while ($session = $session_result->fetch_assoc()) {

        $sessions[] = $session;
    }

    $session_stmt->close();

    $room['sessions'] = $sessions;

    $rooms[] = $room;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Faculty Dashboard</title>


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

            background: #f0fdf4;

            color: #064e3b;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .header {

            background:
                linear-gradient(
                    135deg,
                    #064e3b,
                    #047857,
                    #10b981
                );

            color: white;

            padding: 18px 35px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            min-height: 72px;

            box-shadow:
                0 4px 15px
                rgba(6, 78, 59, 0.20);
        }


        .header h1 {

            margin: 0;

            font-size: 26px;
        }


        .header-right {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .faculty-info {

            font-size: 14px;

            margin-right: 5px;

            font-weight: 500;
        }


        /* =====================================================
           CHANGE PASSWORD
        ===================================================== */

        .change-password {

            background: #16a34a;

            color: white;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

            transition: 0.3s;
        }


        .change-password:hover {

            background: #15803d;

            transform: translateY(-1px);
        }


        /* =====================================================
           LOGOUT
        ===================================================== */

        .logout {

            background: #dc2626;

            color: white;

            text-decoration: none;

            padding: 10px 18px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

            transition: 0.3s;
        }


        .logout:hover {

            background: #b91c1c;

            transform: translateY(-1px);
        }


        /* =====================================================
           MAIN CONTAINER
        ===================================================== */

        .container {

            width: 82%;

            max-width: 1100px;

            margin: 35px auto;
        }


        /* =====================================================
           WELCOME CARD
        ===================================================== */

        .welcome-card {

            background: white;

            padding: 28px;

            border-radius: 14px;

            box-shadow:
                0 5px 20px
                rgba(6, 78, 59, 0.08);

            margin-bottom: 30px;

            border: 1px solid #d1fae5;

            position: relative;

            overflow: hidden;
        }


        .welcome-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            width: 100%;

            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    #047857,
                    #10b981
                );
        }


        .welcome-card h2 {

            margin-top: 0;

            margin-bottom: 12px;

            color: #064e3b;

            font-size: 25px;
        }


        .welcome-card p {

            color: #64748b;

            font-size: 16px;

            margin-bottom: 15px;
        }


        /* =====================================================
           DEPARTMENT
        ===================================================== */

        .department {

            display: inline-block;

            background: #dcfce7;

            color: #166534;

            padding: 7px 12px;

            border-radius: 6px;

            font-size: 14px;

            font-weight: bold;

            border: 1px solid #bbf7d0;
        }


        /* =====================================================
           ASSIGNMENT TITLE
        ===================================================== */

        .section-title {

            font-size: 23px;

            margin-bottom: 18px;

            color: #064e3b;
        }


        /* =====================================================
           ROOM CARD
        ===================================================== */

        .room-card {

            background: white;

            border-radius: 14px;

            padding: 28px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px
                rgba(6, 78, 59, 0.08);

            border-left: 5px solid #059669;

            border-top: 1px solid #d1fae5;

            border-right: 1px solid #d1fae5;

            border-bottom: 1px solid #d1fae5;
        }


        .room-card h3 {

            margin-top: 0;

            margin-bottom: 18px;

            color: #047857;

            font-size: 22px;
        }


        /* =====================================================
           CAPACITY
        ===================================================== */

        .capacity {

            margin-bottom: 22px;

            color: #475569;
        }


        .capacity strong {

            color: #064e3b;
        }


        /* =====================================================
           SESSION TITLE
        ===================================================== */

        .session-title {

            font-weight: bold;

            color: #064e3b;

            margin-bottom: 12px;

            font-size: 15px;
        }


        /* =====================================================
           SESSION CARD
        ===================================================== */

        .session-card {

            background: #f0fdf4;

            border: 1px solid #bbf7d0;

            border-radius: 9px;

            padding: 18px;

            margin-bottom: 14px;
        }


        /* =====================================================
           SESSION TOP
        ===================================================== */

        .session-top {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 16px;

            gap: 10px;
        }


        .session-date {

            font-size: 14px;

            color: #334155;

            font-weight: 500;
        }


        /* =====================================================
           SESSION BADGE
        ===================================================== */

        .session-badge {

            background: #d1fae5;

            color: #047857;

            padding: 5px 10px;

            border-radius: 5px;

            font-size: 13px;

            font-weight: bold;

            border: 1px solid #a7f3d0;
        }


        /* =====================================================
           EXAM INFORMATION
        ===================================================== */

        .exam-info {

            background: white;

            border: 1px solid #d1fae5;

            border-radius: 8px;

            padding: 15px;

            margin-bottom: 14px;
        }


        .info-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 7px 0;

            border-bottom: 1px solid #ecfdf5;

            gap: 20px;
        }


        .info-row:last-child {

            border-bottom: none;
        }


        .info-label {

            color: #64748b;

            font-size: 14px;

            font-weight: 600;
        }


        .info-value {

            color: #064e3b;

            font-size: 14px;

            font-weight: bold;

            text-align: right;
        }


        /* =====================================================
           SUBJECT
        ===================================================== */

        .subject-value {

            color: #047857;

            font-size: 16px;

            font-weight: bold;
        }


        /* =====================================================
           EXAM TYPE BADGES
        ===================================================== */

        .exam-type {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 5px;

            font-size: 12px;

            font-weight: bold;
        }


        .regular {

            background: #dbeafe;

            color: #1d4ed8;
        }


        .semester {

            background: #fef3c7;

            color: #92400e;
        }


        /* =====================================================
           STUDENT COUNT
        ===================================================== */

        .student-count {

            background: #ecfdf5;

            border: 1px solid #a7f3d0;

            color: #047857;

            padding: 9px 12px;

            border-radius: 6px;

            font-weight: bold;

            font-size: 14px;

            margin-bottom: 14px;
        }


        /* =====================================================
           LAYOUT BUTTON
        ===================================================== */

        .layout-button {

            display: block;

            width: 100%;

            text-align: center;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #047857,
                    #059669
                );

            color: white;

            padding: 11px 15px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

            transition: 0.3s;

            box-shadow:
                0 3px 8px
                rgba(4, 120, 87, 0.15);
        }


        .layout-button:hover {

            background:
                linear-gradient(
                    135deg,
                    #065f46,
                    #047857
                );

            transform: translateY(-1px);
        }


        /* =====================================================
           NO SESSION
        ===================================================== */

        .no-session {

            color: #64748b;

            background: #f0fdf4;

            padding: 15px;

            border-radius: 7px;

            border: 1px solid #d1fae5;

            font-size: 14px;
        }


        /* =====================================================
           NO ASSIGNMENT
        ===================================================== */

        .no-assignment {

            background: white;

            padding: 35px;

            border-radius: 14px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(6, 78, 59, 0.08);

            color: #64748b;

            border: 1px solid #d1fae5;
        }


        .no-assignment h3 {

            color: #047857;

            margin-bottom: 10px;
        }


        .no-assignment p {

            margin-bottom: 0;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 768px) {

            .header {

                padding: 15px 20px;

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;
            }


            .header-right {

                width: 100%;

                flex-wrap: wrap;
            }


            .faculty-info {

                width: 100%;

                margin-bottom: 5px;
            }


            .container {

                width: 92%;
            }


            .welcome-card,
            .room-card {

                padding: 22px;
            }


            .session-top {

                gap: 10px;

                flex-wrap: wrap;
            }


            .info-row {

                flex-direction: column;

                align-items: flex-start;

                gap: 4px;
            }


            .info-value {

                text-align: left;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="header">

    <h1>Faculty Dashboard</h1>


    <div class="header-right">

        <span class="faculty-info">

            <?php
            echo htmlspecialchars($faculty_name);
            ?>

            |

            <?php
            echo htmlspecialchars($faculty_department);
            ?>

        </span>


        <a
            href="faculty_change_password.php"
            class="change-password"
        >
            Change Password
        </a>


        <a
            href="faculty_logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>



<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<div class="container">


    <!-- =====================================================
         WELCOME
    ===================================================== -->

    <div class="welcome-card">

        <h2>

            Welcome,

            <?php
            echo htmlspecialchars($faculty_name);
            ?>

        </h2>


        <p>

            Here you can view your assigned examination halls,
            examination details and complete seating layouts.

        </p>


        <span class="department">

            Department:

            <?php
            echo htmlspecialchars($faculty_department);
            ?>

        </span>

    </div>



    <!-- =====================================================
         ASSIGNMENTS
    ===================================================== -->

    <h2 class="section-title">

        My Invigilation Assignments

    </h2>



    <?php if (count($rooms) > 0): ?>


        <?php foreach ($rooms as $room): ?>


            <div class="room-card">


                <!-- HALL -->

                <h3>

                    Hall:

                    <?php
                    echo htmlspecialchars($room['room_no']);
                    ?>

                </h3>


                <!-- CAPACITY -->

                <div class="capacity">

                    <strong>Capacity:</strong>

                    <?php
                    echo htmlspecialchars($room['capacity']);
                    ?>

                    students

                </div>


                <!-- SESSION TITLE -->

                <div class="session-title">

                    Allocated Examination Sessions

                </div>



                <?php if (count($room['sessions']) > 0): ?>


                    <?php foreach ($room['sessions'] as $session): ?>


                        <div class="session-card">


                            <!-- DATE + SESSION -->

                            <div class="session-top">


                                <span class="session-date">

                                    <?php

                                    echo date(
                                        "d-m-Y",
                                        strtotime(
                                            $session['allocation_date']
                                        )
                                    );

                                    ?>

                                </span>


                                <span class="session-badge">

                                    <?php

                                    echo htmlspecialchars(
                                        $session['session']
                                    );

                                    ?>

                                </span>


                            </div>



                            <!-- EXAM INFORMATION -->

                            <div class="exam-info">


                                <!-- SUBJECT -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Exam / Subject
                                    </span>

                                    <span class="info-value subject-value">

                                        <?php

                                        echo htmlspecialchars(
                                            $session['subjects']
                                        );

                                        ?>

                                    </span>

                                </div>



                                <!-- EXAM TYPE -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Exam Type
                                    </span>

                                    <span class="info-value">

                                        <?php

                                        $exam_types = explode(
                                            ', ',
                                            $session['exam_types']
                                        );

                                        foreach ($exam_types as $type) {

                                            $class = '';

                                            if (
                                                strtoupper($type)
                                                === 'REGULAR'
                                            ) {

                                                $class = 'regular';

                                            } elseif (
                                                strtoupper($type)
                                                === 'SEMESTER'
                                            ) {

                                                $class = 'semester';
                                            }

                                            echo '<span class="exam-type '
                                                . $class
                                                . '">'
                                                . htmlspecialchars($type)
                                                . '</span> ';
                                        }

                                        ?>

                                    </span>

                                </div>



                                <!-- DATE -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Exam Date
                                    </span>

                                    <span class="info-value">

                                        <?php

                                        echo date(
                                            "d-m-Y",
                                            strtotime(
                                                $session['allocation_date']
                                            )
                                        );

                                        ?>

                                    </span>

                                </div>



                                <!-- SESSION -->

                                <div class="info-row">

                                    <span class="info-label">
                                        Session
                                    </span>

                                    <span class="info-value">

                                        <?php

                                        echo htmlspecialchars(
                                            $session['session']
                                        );

                                        ?>

                                    </span>

                                </div>


                            </div>



                            <!-- STUDENT COUNT -->

                            <div class="student-count">

                                Total Allocated Students:

                                <?php

                                echo htmlspecialchars(
                                    $session['student_count']
                                );

                                ?>

                            </div>



                            <!-- SEATING LAYOUT BUTTON -->

                            <a
                                href="faculty_seating_layout.php?room_id=<?php echo $room['room_id']; ?>&date=<?php echo urlencode($session['allocation_date']); ?>&session=<?php echo urlencode($session['session']); ?>"
                                class="layout-button"
                            >

                                View Complete Seating Layout

                            </a>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="no-session">

                        No examination session has been allocated
                        for this hall yet.

                    </div>


                <?php endif; ?>


            </div>


        <?php endforeach; ?>


    <?php else: ?>


        <div class="no-assignment">

            <h3>

                No Invigilation Assignments

            </h3>


            <p>

                You currently have no examination halls assigned.

            </p>

        </div>


    <?php endif; ?>


</div>


</body>

</html>