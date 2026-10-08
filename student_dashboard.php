<?php

session_start();


/* =========================================================
   STUDENT LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['student_logged_in']) ||
    $_SESSION['student_logged_in'] !== true
) {

    header("Location: student_login.php");
    exit();

}


include 'db_connect.php';


$student_id =
    $_SESSION['student_id'];


/* =========================================================
   GET STUDENT DETAILS
========================================================= */

$student_sql = "
    SELECT
        s.register_no,
        s.student_name,
        s.year,
        d.dept_name
    FROM student s
    INNER JOIN department d
        ON s.dept_id = d.dept_id
    WHERE s.student_id = ?
";


$stmt =
    mysqli_prepare(
        $conn,
        $student_sql
    );


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);


mysqli_stmt_execute($stmt);


$student_result =
    mysqli_stmt_get_result($stmt);


$student =
    mysqli_fetch_assoc($student_result);


/* =========================================================
   GET EXAM AND SEAT DETAILS
========================================================= */

$allocation_sql = "
    SELECT
        e.subject,
        e.exam_date,
        sa.session,
        r.room_no,
        sa.seat_no
    FROM seat_allocation sa

    INNER JOIN exam e
        ON sa.exam_id = e.exam_id

    INNER JOIN room r
        ON sa.room_id = r.room_id

    WHERE sa.student_id = ?

    ORDER BY
        e.exam_date ASC,
        sa.session ASC
";


$stmt2 =
    mysqli_prepare(
        $conn,
        $allocation_sql
    );


mysqli_stmt_bind_param(
    $stmt2,
    "i",
    $student_id
);


mysqli_stmt_execute($stmt2);


$allocation_result =
    mysqli_stmt_get_result($stmt2);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Student Dashboard</title>


<style>

/* =========================================================
   RESET
========================================================= */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* =========================================================
   BODY
========================================================= */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #faf7ff;

    color: #1f2937;

    min-height: 100vh;

}


/* =========================================================
   HEADER
========================================================= */

.header {

    background:
        linear-gradient(
            135deg,
            #3b0764,
            #6d28d9,
            #9333ea
        );

    color: white;

    padding: 17px 45px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 4px 15px
        rgba(76,29,149,0.25);

}


/* LEFT */

.header-left {

    display: flex;

    align-items: center;

    gap: 13px;

}


.header-icon {

    width: 45px;

    height: 45px;

    background:
        rgba(255,255,255,0.15);

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 24px;

}


.header-left h2 {

    font-size: 21px;

    margin: 0;

}


.header-left p {

    margin-top: 4px;

    color: #e9d5ff;

    font-size: 13px;

}


/* RIGHT */

.header-right {

    display: flex;

    align-items: center;

    gap: 9px;

}


.student-name {

    margin-right: 7px;

    font-size: 14px;

    font-weight: bold;

    color: #f3e8ff;

}


/* CHANGE PASSWORD */

.change-password {

    background: #16a34a;

    color: white;

    text-decoration: none;

    padding: 9px 14px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;

    transition: 0.2s;

}


.change-password:hover {

    background: #15803d;

    transform: translateY(-1px);

}


/* LOGOUT */

.logout {

    background: #dc2626;

    color: white;

    text-decoration: none;

    padding: 9px 14px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;

    transition: 0.2s;

}


.logout:hover {

    background: #b91c1c;

    transform: translateY(-1px);

}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {

    width: 92%;

    max-width: 1150px;

    margin: 32px auto 0;

}


/* =========================================================
   WELCOME CARD
========================================================= */

.welcome-box {

    background: white;

    padding: 27px;

    border-radius: 16px;

    border:
        1px solid #eadcff;

    box-shadow:
        0 5px 20px
        rgba(76,29,149,0.08);

    margin-bottom: 30px;

    position: relative;

    overflow: hidden;

}


.welcome-box::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 4px;

    background:
        linear-gradient(
            90deg,
            #6d28d9,
            #9333ea
        );

}


.welcome-title {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 21px;

}


.welcome-icon {

    width: 43px;

    height: 43px;

    background: #f3e8ff;

    color: #6d28d9;

    border-radius: 11px;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 22px;

}


.welcome-box h2 {

    color: #3b0764;

    font-size: 22px;

}


.welcome-subtitle {

    color: #8b7b9e;

    font-size: 13px;

    margin-top: 3px;

}


/* =========================================================
   STUDENT INFORMATION
========================================================= */

.student-info {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 14px;

}


.info-box {

    background: #faf7ff;

    border:
        1px solid #eee5f7;

    padding: 16px;

    border-radius: 11px;

    transition: 0.2s;

}


.info-box:hover {

    border-color: #d8b4fe;

    transform: translateY(-2px);

}


.info-box strong {

    display: block;

    color: #8b7b9e;

    font-size: 12px;

    margin-bottom: 7px;

    text-transform: uppercase;

    letter-spacing: 0.3px;

}


.info-box span {

    font-size: 15px;

    font-weight: bold;

    color: #4c1d95;

}


/* =========================================================
   SECTION TITLE
========================================================= */

.section-heading {

    display: flex;

    align-items: center;

    gap: 11px;

    margin-bottom: 17px;

}


.section-icon {

    width: 42px;

    height: 42px;

    border-radius: 11px;

    background: #f3e8ff;

    color: #6d28d9;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

}


.section-title {

    color: #3b0764;

    font-size: 21px;

}


.section-subtitle {

    color: #8b7b9e;

    font-size: 12px;

    margin-top: 3px;

}


/* =========================================================
   EXAM CARD
========================================================= */

.exam-card {

    background: white;

    border:
        1px solid #eadcff;

    border-radius: 16px;

    padding: 24px;

    margin-bottom: 18px;

    box-shadow:
        0 5px 18px
        rgba(76,29,149,0.07);

    transition: 0.2s;

}


.exam-card:hover {

    box-shadow:
        0 8px 25px
        rgba(76,29,149,0.12);

    transform: translateY(-2px);

}


/* =========================================================
   EXAM HEADER
========================================================= */

.exam-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom:
        1px solid #eee5f7;

    padding-bottom: 17px;

    margin-bottom: 18px;

}


.exam-header-left {

    display: flex;

    align-items: center;

    gap: 11px;

}


.exam-subject-icon {

    width: 39px;

    height: 39px;

    background: #f3e8ff;

    color: #7c3aed;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;

}


.exam-header h3 {

    color: #5b21b6;

    font-size: 18px;

}


/* =========================================================
   SESSION BADGES
========================================================= */

.session {

    padding: 7px 14px;

    border-radius: 20px;

    font-weight: bold;

    font-size: 12px;

}


.fn {

    background: #ede9fe;

    color: #6d28d9;

}


.an {

    background: #fef3c7;

    color: #92400e;

}


/* =========================================================
   EXAM DETAILS
========================================================= */

.exam-details {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 13px;

}


.detail {

    background: #faf7ff;

    border:
        1px solid #eee5f7;

    padding: 16px 12px;

    border-radius: 11px;

    text-align: center;

}


.detail strong {

    display: block;

    color: #8b7b9e;

    font-size: 12px;

    margin-bottom: 7px;

    text-transform: uppercase;

}


.detail span {

    font-size: 16px;

    font-weight: bold;

    color: #374151;

}


/* =========================================================
   HALL
========================================================= */

.hall {

    color: #6d28d9 !important;

    font-size: 20px !important;

}


/* =========================================================
   SEAT
========================================================= */

.seat {

    color: #16a34a !important;

    font-size: 23px !important;

}


/* =========================================================
   ROW NUMBER
========================================================= */

.row-number {

    color: #2563eb !important;

    font-size: 19px !important;

}


/* =========================================================
   NO ALLOCATION
========================================================= */

.no-allocation {

    background: white;

    padding: 45px 25px;

    text-align: center;

    border-radius: 16px;

    border:
        1px solid #eadcff;

    box-shadow:
        0 5px 18px
        rgba(76,29,149,0.07);

}


.no-allocation-icon {

    width: 65px;

    height: 65px;

    background: #f3e8ff;

    color: #7c3aed;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 15px;

    font-size: 29px;

}


.no-allocation h3 {

    color: #4c1d95;

    margin-bottom: 8px;

}


.no-allocation p {

    color: #8b7b9e;

    font-size: 14px;

}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    text-align: center;

    padding: 30px 20px;

    color: #9b8aaa;

    font-size: 12px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .exam-details {

        grid-template-columns:
            repeat(3, 1fr);

    }

}


@media (max-width: 900px) {

    .student-info {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .exam-details {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .header {

        padding: 16px 25px;

    }

}


@media (max-width: 650px) {

    .header {

        flex-direction: column;

        gap: 15px;

        text-align: center;

    }


    .header-left {

        justify-content: center;

    }


    .header-right {

        flex-wrap: wrap;

        justify-content: center;

    }


    .student-name {

        width: 100%;

        margin-right: 0;

    }


    .student-info {

        grid-template-columns: 1fr;

    }


    .exam-details {

        grid-template-columns: 1fr;

    }


    .exam-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

    }


    .container {

        width: 94%;

        margin-top: 22px;

    }


    .welcome-box,

    .exam-card {

        padding: 20px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<div class="header">


    <div class="header-left">


        <div class="header-icon">

            🎓

        </div>


        <div>

            <h2>

                Exam Seat Allocation System

            </h2>


            <p>

                Student Dashboard

            </p>

        </div>


    </div>


    <div class="header-right">


        <span class="student-name">

            👤

            <?php

            echo htmlspecialchars(
                $student['student_name']
            );

            ?>

        </span>


        <a
            href="change_student_password.php"
            class="change-password"
        >

            🔐 Change Password

        </a>


        <a
            href="student_logout.php"
            class="logout"
        >

            Logout

        </a>


    </div>

</div>



<!-- =====================================================
     MAIN
====================================================== -->

<div class="container">


    <!-- =================================================
         STUDENT INFORMATION
    ================================================== -->

    <div class="welcome-box">


        <div class="welcome-title">


            <div class="welcome-icon">

                👋

            </div>


            <div>

                <h2>

                    Welcome,
                    <?php

                    echo htmlspecialchars(
                        $student['student_name']
                    );

                    ?>

                </h2>


                <div class="welcome-subtitle">

                    Your student information

                </div>

            </div>


        </div>


        <div class="student-info">


            <!-- REGISTER NUMBER -->

            <div class="info-box">

                <strong>

                    Register Number

                </strong>


                <span>

                    <?php

                    echo htmlspecialchars(
                        $student['register_no']
                    );

                    ?>

                </span>

            </div>


            <!-- DEPARTMENT -->

            <div class="info-box">

                <strong>

                    Department

                </strong>


                <span>

                    <?php

                    echo htmlspecialchars(
                        $student['dept_name']
                    );

                    ?>

                </span>

            </div>


            <!-- YEAR -->

            <div class="info-box">

                <strong>

                    Year

                </strong>


                <span>

                    <?php

                    echo htmlspecialchars(
                        $student['year']
                    );

                    ?>

                </span>

            </div>


            <!-- STUDENT ID -->

            <div class="info-box">

                <strong>

                    Student ID

                </strong>


                <span>

                    <?php

                    echo htmlspecialchars(
                        $student_id
                    );

                    ?>

                </span>

            </div>


        </div>

    </div>



    <!-- =================================================
         EXAM SECTION TITLE
    ================================================== -->

    <div class="section-heading">


        <div class="section-icon">

            📝

        </div>


        <div>

            <h2 class="section-title">

                My Examination Details

            </h2>


            <div class="section-subtitle">

                Your allocated examination seats

            </div>

        </div>


    </div>



    <!-- =================================================
         EXAM DETAILS
    ================================================== -->

    <?php if (
        mysqli_num_rows($allocation_result) > 0
    ) { ?>


        <?php while (
            $allocation =
            mysqli_fetch_assoc($allocation_result)
        ) {

            /*
             * =================================================
             * CALCULATE ROW NUMBER FROM SEAT NUMBER
             *
             * A1-A6  -> Row 1
             * B1-B6  -> Row 2
             * C1-C6  -> Row 3
             * D1-D6  -> Row 4
             * E1-E6  -> Row 5
             * F1-F6  -> Row 6
             * G1-G6  -> Row 7
             * H1-H6  -> Row 8
             * I1-I6  -> Row 9
             * J1-J6  -> Row 10
             * =================================================
             */

            $seat_number =
                strtoupper(
                    trim(
                        $allocation['seat_no']
                    )
                );

            $row_letter =
                substr(
                    $seat_number,
                    0,
                    1
                );

            $row_number =
                ord($row_letter)
                - ord('A')
                + 1;

        ?>


            <div class="exam-card">


                <!-- EXAM HEADER -->

                <div class="exam-header">


                    <div class="exam-header-left">


                        <div class="exam-subject-icon">

                            📚

                        </div>


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $allocation['subject']
                            );

                            ?>

                        </h3>


                    </div>


                    <span
                        class="session <?php
                            echo strtolower(
                                $allocation['session']
                            );
                        ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $allocation['session']
                        );

                        ?>

                    </span>


                </div>



                <!-- EXAM DETAILS -->

                <div class="exam-details">


                    <!-- DATE -->

                    <div class="detail">

                        <strong>

                            📅 Exam Date

                        </strong>


                        <span>

                            <?php

                            echo htmlspecialchars(
                                $allocation['exam_date']
                            );

                            ?>

                        </span>

                    </div>



                    <!-- SESSION -->

                    <div class="detail">

                        <strong>

                            🕐 Session

                        </strong>


                        <span>

                            <?php

                            echo htmlspecialchars(
                                $allocation['session']
                            );

                            ?>

                        </span>

                    </div>



                    <!-- HALL -->

                    <div class="detail">

                        <strong>

                            🏫 Hall

                        </strong>


                        <span class="hall">

                            <?php

                            echo htmlspecialchars(
                                $allocation['room_no']
                            );

                            ?>

                        </span>

                    </div>



                    <!-- SEAT -->

                    <div class="detail">

                        <strong>

                            💺 Seat Number

                        </strong>


                        <span class="seat">

                            <?php

                            echo htmlspecialchars(
                                $allocation['seat_no']
                            );

                            ?>

                        </span>

                    </div>



                    <!-- ROW NUMBER -->

                    <div class="detail">

                        <strong>

                            🪑 Row Number

                        </strong>


                        <span class="row-number">

                            <?php

                            echo "Row "
                                . $row_number;

                            ?>

                        </span>

                    </div>


                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


        <!-- =================================================
             NO ALLOCATION
        ================================================== -->

        <div class="no-allocation">


            <div class="no-allocation-icon">

                💺

            </div>


            <h3>

                No Seat Allocation Found

            </h3>


            <p>

                Your examination seat has not been
                allocated yet.

            </p>


        </div>


    <?php } ?>


</div>



<!-- =====================================================
     FOOTER
====================================================== -->

<div class="footer">

    Exam Seat Allocation System

</div>


</body>

</html>