<?php

session_start();

include 'db_connect.php';


/* =========================
   ADMIN LOGIN CHECK
========================= */

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header("Location: admin_login.php");
    exit();
}


/* =========================
   DELETE ALLOCATION
========================= */

if (
    isset($_GET['delete_allocation']) &&
    $_GET['delete_allocation'] == '1'
) {

    $room_id = intval($_GET['room_id'] ?? 0);

    $date = trim($_GET['date'] ?? '');

    $session = strtoupper(
        trim($_GET['session'] ?? '')
    );


    if (
        $room_id > 0 &&
        !empty($date) &&
        in_array($session, ['FN', 'AN'])
    ) {

        $delete_sql = "
            DELETE FROM seat_allocation
            WHERE room_id = ?
            AND allocation_date = ?
            AND session = ?
        ";

        $delete = $conn->prepare($delete_sql);

        $delete->bind_param(
            "iss",
            $room_id,
            $date,
            $session
        );


        if ($delete->execute()) {

            $delete->close();

            header(
                "Location: view.php?deleted=1"
            );

            exit();

        } else {

            $delete->close();

            header(
                "Location: view.php?deleted=0"
            );

            exit();

        }

    }

}


/* =========================
   GET FILTER VALUES
========================= */

$room_id = isset($_GET['room_id'])
    ? intval($_GET['room_id'])
    : 0;

$date = isset($_GET['date'])
    ? trim($_GET['date'])
    : '';

$session = isset($_GET['session'])
    ? strtoupper(trim($_GET['session']))
    : '';



/* =========================
   GET ROOMS
========================= */

$rooms = [];

$room_query = "
    SELECT room_id, room_no, capacity
    FROM room
    ORDER BY room_no ASC
";

$room_result = $conn->query($room_query);

if ($room_result) {

    while ($row = $room_result->fetch_assoc()) {

        $rooms[] = $row;

    }

}


/* =========================
   GET ALLOCATIONS
========================= */

$allocations = [];

if (
    $room_id > 0 &&
    !empty($date) &&
    in_array($session, ['FN', 'AN'])
) {

    $allocation_sql = "

        SELECT

            sa.allocation_id,
            sa.seat_no,

            s.register_no,
            s.student_name,

            d.dept_name,

            e.subject,
            e.exam_date,
            e.session,

            r.room_no,
            r.capacity

        FROM seat_allocation sa

        INNER JOIN student s
            ON sa.student_id = s.student_id

        INNER JOIN department d
            ON s.dept_id = d.dept_id

        INNER JOIN exam e
            ON sa.exam_id = e.exam_id

        INNER JOIN room r
            ON sa.room_id = r.room_id

        WHERE sa.room_id = ?

        AND sa.allocation_date = ?

        AND sa.session = ?

        ORDER BY

            CAST(
                SUBSTRING(sa.seat_no, 2)
                AS UNSIGNED
            ),

            LEFT(sa.seat_no, 1)

    ";


    $stmt = $conn->prepare(
        $allocation_sql
    );


    $stmt->bind_param(
        "iss",
        $room_id,
        $date,
        $session
    );


    $stmt->execute();

    $result = $stmt->get_result();


    while (
        $row = $result->fetch_assoc()
    ) {

        $allocations[] = $row;

    }


    $stmt->close();

}


/* =========================
   GET SELECTED ROOM
========================= */

$selected_room = null;

foreach ($rooms as $room) {

    if (
        intval($room['room_id']) === $room_id
    ) {

        $selected_room = $room;

        break;

    }

}


/* =========================
   GET INVIGILATORS
========================= */

$invigilators = [];

if ($room_id > 0) {

    $faculty_sql = "

        SELECT
            f.faculty_name,
            f.faculty_code

        FROM exam_invigilator ei

        INNER JOIN faculty f
            ON ei.faculty_id = f.faculty_id

        WHERE ei.room_id = ?

        ORDER BY f.faculty_name ASC

    ";


    $faculty_stmt =
        $conn->prepare($faculty_sql);


    $faculty_stmt->bind_param(
        "i",
        $room_id
    );


    $faculty_stmt->execute();

    $faculty_result =
        $faculty_stmt->get_result();


    while (
        $faculty_row =
        $faculty_result->fetch_assoc()
    ) {

        $invigilators[] =
            $faculty_row;

    }


    $faculty_stmt->close();

}


/* =========================
   DISPLAY DATE
========================= */

$display_date = '';

if (!empty($date)) {

    $date_object =
        DateTime::createFromFormat(
            'Y-m-d',
            $date
        );


    if ($date_object) {

        $display_date =
            $date_object->format('d-m-Y');

    } else {

        $display_date = $date;

    }

}


/* =========================
   SUCCESS / ERROR MESSAGE
========================= */

$deleted_message = '';

$deleted_type = 'success';

if (
    isset($_GET['deleted']) &&
    $_GET['deleted'] == '1'
) {

    $deleted_message =
        "Allocation deleted successfully.";

}


if (
    isset($_GET['deleted']) &&
    $_GET['deleted'] == '0'
) {

    $deleted_message =
        "Unable to delete allocation.";

    $deleted_type = 'error';

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
    View Allocations | Admin
</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    box-sizing: border-box;
}


/* =====================================================
   BODY
===================================================== */

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #fff7f7,
            #fef2f2
        );

    color: #1f2937;

    min-height: 100vh;

}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 92%;

    max-width: 1400px;

    margin: 30px auto 60px;

}


/* =====================================================
   HEADER
===================================================== */

.header {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 22px 28px;

    border-radius: 15px;

    box-shadow:
        0 6px 22px
        rgba(69, 10, 10, 0.22);

    margin-bottom: 22px;

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 15px;

}


.header-left {

    display: flex;

    align-items: center;

    gap: 14px;

}


.header-icon {

    width: 48px;

    height: 48px;

    border-radius: 12px;

    background:
        rgba(255,255,255,0.15);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

}


.header h1 {

    margin: 0;

    font-size: 25px;

}


.header p {

    margin: 5px 0 0;

    color: #fecaca;

    font-size: 13px;

}


/* =====================================================
   DASHBOARD BUTTON
===================================================== */

.back-dashboard {

    text-decoration: none;

    background: white;

    color: #7f1d1d;

    padding: 10px 17px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

    transition: 0.2s;

}


.back-dashboard:hover {

    background: #fee2e2;

    transform: translateY(-1px);

}


/* =====================================================
   MESSAGE
===================================================== */

.message {

    padding: 14px 17px;

    border-radius: 10px;

    margin-bottom: 20px;

    font-weight: 600;

}


.message.success {

    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;

}


.message.error {

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

}


/* =====================================================
   FILTER CARD
===================================================== */

.filter-card {

    background: white;

    padding: 25px;

    border-radius: 15px;

    border: 1px solid #f1d4d4;

    box-shadow:
        0 5px 20px
        rgba(69, 10, 10, 0.07);

    margin-bottom: 22px;

}


.filter-heading {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 20px;

}


.filter-icon {

    width: 40px;

    height: 40px;

    border-radius: 10px;

    background: #fef2f2;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

}


.filter-card h2 {

    margin: 0;

    color: #450a0a;

    font-size: 20px;

}


.filter-card p {

    margin: 4px 0 0;

    color: #64748b;

    font-size: 13px;

}


/* =====================================================
   FILTER FORM
===================================================== */

.filter-form {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

}


.form-group {

    display: flex;

    flex-direction: column;

}


.form-group label {

    font-weight: 600;

    margin-bottom: 7px;

    font-size: 14px;

    color: #374151;

}


.form-group select,

.form-group input {

    padding: 12px;

    border: 1px solid #d1d5db;

    border-radius: 8px;

    font-size: 14px;

    background: white;

    color: #1f2937;

}


.form-group select:focus,

.form-group input:focus {

    outline: none;

    border-color: #991b1b;

    box-shadow:
        0 0 0 3px
        rgba(153,27,27,0.10);

}


/* =====================================================
   FILTER BUTTON
===================================================== */

.filter-button {

    grid-column: span 3;

    margin-top: 2px;

}


.filter-button button {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 8px;

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;

}


.filter-button button:hover {

    background:
        linear-gradient(
            135deg,
            #991b1b,
            #b91c1c
        );

    transform: translateY(-1px);

}


/* =====================================================
   DETAILS CARD
===================================================== */

.details-card {

    background: white;

    padding: 24px;

    border-radius: 15px;

    border: 1px solid #f1d4d4;

    box-shadow:
        0 5px 20px
        rgba(69, 10, 10, 0.07);

    margin-bottom: 22px;

}


.details-header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 20px;

}


.details-header h2 {

    margin: 0;

    color: #450a0a;

    font-size: 22px;

}


/* =====================================================
   SESSION BADGES
===================================================== */

.session-badge {

    padding: 7px 14px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

}


.session-fn {

    background: #dcfce7;

    color: #166534;

}


.session-an {

    background: #fef3c7;

    color: #92400e;

}


/* =====================================================
   INFO GRID
===================================================== */

.info-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

}


.info-box {

    background: #fffafa;

    border: 1px solid #f1d4d4;

    border-radius: 10px;

    padding: 16px;

    text-align: center;

}


.info-box strong {

    display: block;

    font-size: 11px;

    color: #9ca3af;

    margin-bottom: 7px;

    letter-spacing: 0.5px;

}


.info-box span {

    font-size: 18px;

    font-weight: bold;

    color: #7f1d1d;

}


/* =====================================================
   INVIGILATOR
===================================================== */

.invigilator-box {

    margin-top: 18px;

    padding: 16px;

    background: #fff7f7;

    border: 1px solid #f1d4d4;

    border-radius: 10px;

}


.invigilator-box strong {

    color: #7f1d1d;

}


.invigilator-list {

    margin-top: 10px;

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

}


.faculty-badge {

    background: white;

    border: 1px solid #e5baba;

    color: #7f1d1d;

    padding: 7px 11px;

    border-radius: 7px;

    font-size: 13px;

    font-weight: 600;

}


/* =====================================================
   TABLE CARD
===================================================== */

.table-card {

    background: white;

    padding: 24px;

    border-radius: 15px;

    border: 1px solid #f1d4d4;

    box-shadow:
        0 5px 20px
        rgba(69, 10, 10, 0.07);

    overflow-x: auto;

}


.table-title {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 18px;

}


.table-title h2 {

    margin: 0;

    color: #450a0a;

    font-size: 20px;

}


.student-count {

    background: #fff1f2;

    color: #9f1239;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

}


/* =====================================================
   TABLE
===================================================== */

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 950px;

}


th {

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 13px 10px;

    text-align: left;

    font-size: 13px;

}


td {

    padding: 12px 10px;

    border-bottom:
        1px solid #f1e1e1;

    font-size: 13px;

}


tbody tr:hover td {

    background: #fffafa;

}


/* =====================================================
   SEAT BADGE
===================================================== */

.seat-badge {

    display: inline-block;

    min-width: 38px;

    text-align: center;

    padding: 5px 8px;

    border-radius: 6px;

    background: #fef2f2;

    color: #991b1b;

    font-weight: bold;

}


/* =====================================================
   SESSION TABLE BADGE
===================================================== */

.table-session {

    display: inline-block;

    padding: 4px 8px;

    border-radius: 15px;

    font-size: 11px;

    font-weight: bold;

}


.table-session.fn {

    background: #dcfce7;

    color: #166534;

}


.table-session.an {

    background: #fef3c7;

    color: #92400e;

}


/* =====================================================
   ACTION AREA
===================================================== */

.action-buttons {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 10px;

    margin-top: 25px;

    flex-wrap: wrap;

}


/* =====================================================
   VIEW BUTTON
===================================================== */

.view-btn {

    display: inline-block;

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    transition: 0.2s;

}


.view-btn:hover {

    background:
        linear-gradient(
            135deg,
            #991b1b,
            #b91c1c
        );

    transform: translateY(-1px);

}


/* =====================================================
   DELETE BUTTON
===================================================== */

.delete-btn {

    display: inline-block;

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

    background: #dc2626;

    color: white;

    transition: 0.2s;

}


.delete-btn:hover {

    background: #b91c1c;

    transform: translateY(-1px);

}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    text-align: center;

    padding: 50px 25px;

    color: #64748b;

}


.empty-icon {

    font-size: 45px;

    margin-bottom: 12px;

}


.empty h3 {

    margin: 0 0 7px;

    color: #450a0a;

}


.empty p {

    margin: 0;

    font-size: 14px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 900px) {

    .container {

        width: 94%;

    }


    .filter-form {

        grid-template-columns: 1fr;

    }


    .filter-button {

        grid-column: span 1;

    }


    .info-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .header {

        flex-direction: column;

        align-items: stretch;

    }


    .back-dashboard {

        text-align: center;

    }

}


@media (max-width: 600px) {

    .container {

        margin-top: 18px;

    }


    .info-grid {

        grid-template-columns: 1fr;

    }


    .details-header {

        flex-direction: column;

        gap: 12px;

        align-items: flex-start;

    }


    .table-card,
    .details-card,
    .filter-card {

        padding: 18px;

    }

}

</style>

</head>


<body>


<div class="container">


    <!-- =================================================
         HEADER
    ================================================= -->

    <div class="header">

        <div class="header-left">

            <div class="header-icon">
                📋
            </div>

            <div>

                <h1>
                    View Allocations
                </h1>

                <p>
                    View and manage examination hall seat allocations
                </p>

            </div>

        </div>


        <a
            href="admin_dashboard.php"
            class="back-dashboard"
        >
            ← Dashboard
        </a>

    </div>



    <!-- =================================================
         MESSAGE
    ================================================= -->

    <?php if (!empty($deleted_message)): ?>

        <div
            class="message
            <?php echo $deleted_type; ?>"
        >

            <?php if ($deleted_type === 'success'): ?>

                ✅

            <?php else: ?>

                ⚠️

            <?php endif; ?>


            <?php

            echo htmlspecialchars(
                $deleted_message
            );

            ?>

        </div>

    <?php endif; ?>



    <!-- =================================================
         FILTER CARD
    ================================================= -->

    <div class="filter-card">


        <div class="filter-heading">

            <div class="filter-icon">
                🔎
            </div>

            <div>

                <h2>
                    Select Examination Allocation
                </h2>

                <p>
                    Choose a hall, examination date and session.
                </p>

            </div>

        </div>


        <form
            method="GET"
            action="view.php"
            class="filter-form"
        >


            <!-- HALL -->

            <div class="form-group">

                <label>
                    Examination Hall
                </label>


                <select
                    name="room_id"
                    required
                >

                    <option value="">
                        -- Select Hall --
                    </option>


                    <?php foreach ($rooms as $room): ?>

                        <option
                            value="<?php
                                echo $room['room_id'];
                            ?>"
                            <?php

                            if (
                                $room_id ==
                                $room['room_id']
                            ) {

                                echo 'selected';

                            }

                            ?>
                        >

                            Hall
                            <?php

                            echo htmlspecialchars(
                                $room['room_no']
                            );

                            ?>

                            -
                            Capacity:
                            <?php

                            echo htmlspecialchars(
                                $room['capacity']
                            );

                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <!-- DATE -->

            <div class="form-group">

                <label>
                    Examination Date
                </label>


                <input
                    type="date"
                    name="date"
                    value="<?php
                        echo htmlspecialchars(
                            $date
                        );
                    ?>"
                    required
                >

            </div>



            <!-- SESSION -->

            <div class="form-group">

                <label>
                    Session
                </label>


                <select
                    name="session"
                    required
                >

                    <option value="">
                        -- Select Session --
                    </option>


                    <option
                        value="FN"
                        <?php

                        if (
                            $session === 'FN'
                        ) {

                            echo 'selected';

                        }

                        ?>
                    >
                        FN
                    </option>


                    <option
                        value="AN"
                        <?php

                        if (
                            $session === 'AN'
                        ) {

                            echo 'selected';

                        }

                        ?>
                    >
                        AN
                    </option>

                </select>

            </div>



            <!-- BUTTON -->

            <div class="filter-button">

                <button type="submit">

                    🔎 View Allocation

                </button>

            </div>


        </form>

    </div>



    <?php if (
        $room_id > 0 &&
        !empty($date) &&
        in_array(
            $session,
            ['FN', 'AN']
        )
    ): ?>


        <!-- =================================================
             DETAILS
        ================================================= -->

        <div class="details-card">


            <div class="details-header">

                <h2>

                    🏫

                    <?php

                    if ($selected_room) {

                        echo htmlspecialchars(
                            $selected_room['room_no']
                        );

                    } else {

                        echo "Hall";

                    }

                    ?>

                </h2>


                <span
                    class="
                    session-badge
                    <?php

                    echo (
                        $session === 'FN'
                        ? 'session-fn'
                        : 'session-an'
                    );

                    ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $session
                    );

                    ?>

                </span>

            </div>



            <div class="info-grid">


                <!-- HALL -->

                <div class="info-box">

                    <strong>
                        HALL
                    </strong>

                    <span>

                        <?php

                        if ($selected_room) {

                            echo htmlspecialchars(
                                $selected_room['room_no']
                            );

                        } else {

                            echo '-';

                        }

                        ?>

                    </span>

                </div>



                <!-- DATE -->

                <div class="info-box">

                    <strong>
                        DATE
                    </strong>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $display_date
                        );

                        ?>

                    </span>

                </div>



                <!-- SESSION -->

                <div class="info-box">

                    <strong>
                        SESSION
                    </strong>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $session
                        );

                        ?>

                    </span>

                </div>



                <!-- STUDENTS -->

                <div class="info-box">

                    <strong>
                        STUDENTS
                    </strong>

                    <span>

                        <?php

                        echo count(
                            $allocations
                        );

                        ?>

                    </span>

                </div>


            </div>



            <!-- =================================================
                 INVIGILATORS
            ================================================= -->

            <div class="invigilator-box">

                <strong>
                    👨‍🏫 Assigned Invigilator(s)
                </strong>


                <?php if (
                    count($invigilators) > 0
                ): ?>


                    <div
                        class="invigilator-list"
                    >


                        <?php foreach (
                            $invigilators
                            as $faculty
                        ): ?>


                            <span
                                class="faculty-badge"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $faculty['faculty_code']
                                );

                                ?>

                                -

                                <?php

                                echo htmlspecialchars(
                                    $faculty['faculty_name']
                                );

                                ?>

                            </span>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <div
                        style="
                        margin-top:8px;
                        color:#64748b;
                        font-size:13px;
                        "
                    >

                        No invigilator assigned.

                    </div>


                <?php endif; ?>


            </div>


        </div>



        <!-- =================================================
             ALLOCATION TABLE
        ================================================= -->

        <div class="table-card">


            <div class="table-title">

                <h2>
                    🪑 Student Seat Allocations
                </h2>


                <span
                    class="student-count"
                >

                    Total:
                    <?php

                    echo count(
                        $allocations
                    );

                    ?>

                </span>

            </div>



            <?php if (
                count($allocations) > 0
            ): ?>


                <table>

                    <thead>

                        <tr>

                            <th>
                                S.No
                            </th>

                            <th>
                                Seat
                            </th>

                            <th>
                                Register Number
                            </th>

                            <th>
                                Student Name
                            </th>

                            <th>
                                Department
                            </th>

                            <th>
                                Exam
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Session
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php

                        $serial = 1;

                        foreach (
                            $allocations
                            as $allocation
                        ):

                        ?>


                            <tr>


                                <td>

                                    <?php

                                    echo $serial++;

                                    ?>

                                </td>


                                <td>

                                    <span
                                        class="seat-badge"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $allocation['seat_no']
                                        );

                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $allocation['register_no']
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $allocation['student_name']
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $allocation['dept_name']
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $allocation['subject']
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $display_date
                                    );

                                    ?>

                                </td>


                                <td>

                                    <span
                                        class="
                                        table-session
                                        <?php

                                        echo (
                                            $session === 'FN'
                                            ? 'fn'
                                            : 'an'
                                        );

                                        ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $session
                                        );

                                        ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>



                <!-- =================================================
                     ACTION BUTTONS
                ================================================= -->

                <div class="action-buttons">


                    <!-- VIEW SEATING -->

                    <a
                        href="seating_layout.php?room_id=<?php
                            echo $room_id;
                        ?>&date=<?php
                            echo urlencode($date);
                        ?>&session=<?php
                            echo urlencode($session);
                        ?>"
                        class="view-btn"
                    >

                        🪑 View Seating Layout

                    </a>



                    <!-- DELETE ALLOCATION -->

                    <a
                        href="view.php?delete_allocation=1&room_id=<?php
                            echo $room_id;
                        ?>&date=<?php
                            echo urlencode($date);
                        ?>&session=<?php
                            echo urlencode($session);
                        ?>"
                        class="delete-btn"
                        onclick="
                            return confirm(
                                'Are you sure you want to delete the entire allocation for this hall, date and session?'
                            );
                        "
                    >

                        🗑️ Delete Allocation

                    </a>


                </div>


            <?php else: ?>


                <div class="empty">

                    <div class="empty-icon">
                        🪑
                    </div>

                    <h3>
                        No Seat Allocation Found
                    </h3>

                    <p>
                        No students are allocated for the selected hall,
                        date and session.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    <?php endif; ?>


</div>


</body>

</html>