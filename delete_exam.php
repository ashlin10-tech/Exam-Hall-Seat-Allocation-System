<?php

session_start();

include "db_connect.php";


/* =====================================================
   ADMIN CHECK
===================================================== */

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {

    header("Location: admin_login.php");
    exit();

}


$message = "";
$message_type = "";


/* =====================================================
   DELETE EXAM
===================================================== */

if (isset($_GET['delete_id'])) {

    $exam_id = intval($_GET['delete_id']);


    if ($exam_id <= 0) {

        $message =
            "Invalid exam selected.";

        $message_type = "error";

    }

    else {


        /* =============================================
           CHECK WHETHER EXAM EXISTS
        ============================================= */

        $check_exam = $conn->prepare("
            SELECT
                exam_id,
                subject
            FROM exam
            WHERE exam_id = ?
            LIMIT 1
        ");


        $check_exam->bind_param(
            "i",
            $exam_id
        );


        $check_exam->execute();


        $exam_result =
            $check_exam->get_result();


        if ($exam_result->num_rows === 0) {

            $message =
                "The selected exam does not exist.";

            $message_type = "error";

            $check_exam->close();

        }

        else {


            $exam_data =
                $exam_result->fetch_assoc();


            $check_exam->close();


            /* =========================================
               CHECK WHETHER EXAM IS USED
            ========================================= */

            $check = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM seat_allocation
                WHERE exam_id = ?
            ");


            $check->bind_param(
                "i",
                $exam_id
            );


            $check->execute();


            $result =
                $check->get_result();


            $row =
                $result->fetch_assoc();


            $check->close();


            /* =========================================
               EXAM IS ALREADY USED
            ========================================= */

            if ($row['total'] > 0) {

                $message =
                    "This exam is already used in seat allocation and cannot be deleted. Delete its seating allocation first.";

                $message_type = "error";

            }


            /* =========================================
               DELETE EXAM
            ========================================= */

            else {

                $delete = $conn->prepare("
                    DELETE FROM exam
                    WHERE exam_id = ?
                ");


                $delete->bind_param(
                    "i",
                    $exam_id
                );


                if ($delete->execute()) {

                    if ($delete->affected_rows > 0) {

                        $message =
                            "Exam \"" .
                            $exam_data['subject'] .
                            "\" deleted successfully.";

                        $message_type = "success";

                    }

                    else {

                        $message =
                            "Unable to delete the exam.";

                        $message_type = "error";

                    }

                }

                else {

                    $message =
                        "Unable to delete the exam.";

                    $message_type = "error";

                }


                $delete->close();

            }

        }

    }

}


/* =====================================================
   GET ALL EXAMS
===================================================== */

$exams = $conn->query("
    SELECT
        exam_id,
        subject,
        exam_date,
        session
    FROM exam
    ORDER BY
        exam_date DESC,
        subject ASC
");


if (!$exams) {

    die(
        "Database Error: " .
        htmlspecialchars(
            $conn->error
        )
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

<title>Delete Exam</title>


<style>


/* =====================================================
   GENERAL
===================================================== */

* {

    box-sizing: border-box;

}


body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d,
            #991b1b
        );

    padding: 30px;

}


/* =====================================================
   MAIN CONTAINER
===================================================== */

.container {

    width: 100%;

    max-width: 900px;

    margin: auto;

}


/* =====================================================
   HEADER
===================================================== */

.header {

    background: white;

    padding: 22px 28px;

    border-radius: 15px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 22px;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,0.18);

}


.header-left {

    display: flex;

    align-items: center;

    gap: 15px;

}


.header-icon {

    width: 50px;

    height: 50px;

    border-radius: 50%;

    background: #7f1d1d;

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;

}


.header h1 {

    margin: 0;

    color: #7f1d1d;

    font-size: 25px;

}


.header p {

    margin: 4px 0 0;

    color: #6b7280;

    font-size: 13px;

}


/* =====================================================
   BACK BUTTON
===================================================== */

.back {

    background: #7f1d1d;

    color: white;

    padding: 10px 16px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

    transition: 0.2s;

}


.back:hover {

    background: #991b1b;

}


/* =====================================================
   MESSAGE
===================================================== */

.message {

    padding: 15px 18px;

    border-radius: 9px;

    margin-bottom: 18px;

    font-weight: bold;

    font-size: 14px;

    line-height: 1.5;

}


.success {

    background: #dcfce7;

    color: #166534;

    border-left:
        5px solid #16a34a;

}


.error {

    background: #fee2e2;

    color: #991b1b;

    border-left:
        5px solid #dc2626;

}


/* =====================================================
   EXAM CARD
===================================================== */

.exam-card {

    background: white;

    padding: 22px;

    margin-bottom: 14px;

    border-radius: 12px;

    box-shadow:
        0 7px 20px
        rgba(0,0,0,0.12);

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    transition: 0.2s;

}


.exam-card:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.15);

}


/* =====================================================
   EXAM INFORMATION
===================================================== */

.exam-info {

    flex: 1;

}


.exam-info h3 {

    margin:
        0 0 12px;

    color: #7f1d1d;

    font-size: 19px;

}


.exam-details {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

}


.detail {

    background: #fef2f2;

    color: #7f1d1d;

    padding: 7px 11px;

    border-radius: 6px;

    font-size: 13px;

}


.detail strong {

    margin-left: 3px;

}


/* =====================================================
   DELETE BUTTON
===================================================== */

.delete-btn {

    background: #dc2626;

    color: white;

    border: none;

    padding: 11px 18px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;

    white-space: nowrap;

}


.delete-btn:hover {

    background: #b91c1c;

    transform:
        translateY(-1px);

}


/* =====================================================
   EMPTY
===================================================== */

.no-exams {

    background: white;

    padding: 45px 25px;

    text-align: center;

    border-radius: 12px;

    color: #6b7280;

    box-shadow:
        0 7px 20px
        rgba(0,0,0,0.12);

}


.empty-icon {

    font-size: 40px;

    margin-bottom: 12px;

}


.no-exams h3 {

    margin:
        0 0 7px;

    color: #374151;

}


.no-exams p {

    margin: 0;

    font-size: 14px;

}


/* =====================================================
   FOOTER NOTE
===================================================== */

.note {

    margin-top: 20px;

    background:
        rgba(255,255,255,0.95);

    padding: 14px 18px;

    border-radius: 9px;

    color: #6b7280;

    font-size: 13px;

    line-height: 1.5;

}


.note strong {

    color: #7f1d1d;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width: 650px) {


    body {

        padding: 15px;

    }


    .header {

        padding: 18px;

        align-items: flex-start;

        gap: 15px;

    }


    .header-left {

        align-items: flex-start;

    }


    .header h1 {

        font-size: 21px;

    }


    .header p {

        display: none;

    }


    .back {

        padding:
            9px 12px;

    }


    .exam-card {

        flex-direction: column;

        align-items: stretch;

    }


    .delete-btn {

        width: 100%;

        text-align: center;

    }

}


</style>

</head>


<body>


<div class="container">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="header">


        <div class="header-left">


            <div class="header-icon">

                🗑️

            </div>


            <div>

                <h1>

                    Delete Exam

                </h1>


                <p>

                    Remove an exam from the examination system

                </p>

            </div>


        </div>


        <a
            href="admin_dashboard.php"
            class="back"
        >

            ← Dashboard

        </a>


    </div>


    <!-- =================================================
         MESSAGE
    ================================================== -->

    <?php if ($message !== ""): ?>


        <div
            class="message
            <?php
                echo htmlspecialchars(
                    $message_type
                );
            ?>"
        >

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>


    <?php endif; ?>


    <!-- =================================================
         EXAMS
    ================================================== -->

    <?php if ($exams->num_rows > 0): ?>


        <?php while (
            $exam =
            $exams->fetch_assoc()
        ): ?>


            <div class="exam-card">


                <div class="exam-info">


                    <h3>

                        <?php

                        echo htmlspecialchars(
                            $exam['subject']
                        );

                        ?>

                    </h3>


                    <div class="exam-details">


                        <div class="detail">

                            📅

                            Date:

                            <strong>

                                <?php

                                echo date(
                                    "d-m-Y",
                                    strtotime(
                                        $exam['exam_date']
                                    )
                                );

                                ?>

                            </strong>

                        </div>


                        <div class="detail">

                            🕐

                            Session:

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $exam['session']
                                );

                                ?>

                            </strong>

                        </div>


                    </div>


                </div>


                <a
                    href="delete_exam.php?delete_id=<?php
                        echo (int)$exam['exam_id'];
                    ?>"
                    class="delete-btn"
                    onclick="
                        return confirm(
                            'Are you sure you want to delete this exam?'
                        );
                    "
                >

                    🗑 Delete

                </a>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="no-exams">


            <div class="empty-icon">

                📚

            </div>


            <h3>

                No Exams Available

            </h3>


            <p>

                There are currently no exams in the system.

            </p>


        </div>


    <?php endif; ?>


    <!-- =================================================
         NOTE
    ================================================== -->

    <div class="note">


        <strong>Note:</strong>

        An exam that has already been used
        for seat allocation cannot be deleted.
        Delete its seating allocation first,
        then delete the exam.


    </div>


</div>


</body>

</html>


<?php

$conn->close();

?>