<?php

session_start();
include 'db_connect.php';


/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (empty($_SESSION["admin_logged_in"])) {

    header("Location: admin_login.php");
    exit();

}


$message = "";
$error = "";


/* =========================================================
   DELETE ASSIGNMENT
========================================================= */

if (isset($_GET["delete_id"])) {

    $delete_id = intval($_GET["delete_id"]);

    if ($delete_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM exam_invigilator
             WHERE invigilator_id = ?"
        );

        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message =
                    "Invigilator assignment deleted successfully.";

            } else {

                $error =
                    "Assignment not found.";

            }

        } else {

            $error =
                "Unable to delete assignment.";

        }

        $stmt->close();
    }
}


/* =========================================================
   ASSIGN FACULTY TO HALL
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $faculty_id =
        intval($_POST["faculty_id"] ?? 0);

    $room_id =
        intval($_POST["room_id"] ?? 0);


    if ($faculty_id <= 0 || $room_id <= 0) {

        $error =
            "Please select faculty and examination hall.";

    } else {


        /* CHECK DUPLICATE ASSIGNMENT */

        $check = $conn->prepare(
            "SELECT invigilator_id
             FROM exam_invigilator
             WHERE faculty_id = ?
             AND room_id = ?"
        );

        $check->bind_param(
            "ii",
            $faculty_id,
            $room_id
        );

        $check->execute();

        $check_result =
            $check->get_result();


        if ($check_result->num_rows > 0) {

            $error =
                "This faculty is already assigned to this hall.";

        } else {


            /* INSERT ASSIGNMENT */

            $stmt = $conn->prepare(
                "INSERT INTO exam_invigilator
                (faculty_id, room_id)
                VALUES (?, ?)"
            );

            $stmt->bind_param(
                "ii",
                $faculty_id,
                $room_id
            );


            if ($stmt->execute()) {

                $message =
                    "Invigilator assigned successfully.";

            } else {

                $error =
                    "Unable to assign invigilator.";

            }


            $stmt->close();

        }


        $check->close();

    }

}


/* =========================================================
   GET FACULTY
========================================================= */

$faculty_result = $conn->query(
    "SELECT
        faculty_id,
        faculty_code,
        faculty_name,
        department
     FROM faculty
     ORDER BY faculty_name ASC"
);


/* =========================================================
   GET ROOMS
========================================================= */

$room_result = $conn->query(
    "SELECT
        room_id,
        room_no,
        capacity
     FROM room
     ORDER BY room_no ASC"
);


/* =========================================================
   GET CURRENT ASSIGNMENTS
========================================================= */

$assignment_result = $conn->query(
    "SELECT
        ei.invigilator_id,

        f.faculty_code,
        f.faculty_name,
        f.department,

        r.room_no,
        r.capacity

     FROM exam_invigilator ei

     INNER JOIN faculty f
        ON ei.faculty_id = f.faculty_id

     INNER JOIN room r
        ON ei.room_id = r.room_id

     ORDER BY
        r.room_no ASC,
        f.faculty_name ASC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Assign Invigilator</title>


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

    background: #fff7f7;

    color: #333;

    min-height: 100vh;

}


/* =========================================================
   HEADER
========================================================= */

.header {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 22px 45px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    box-shadow:
        0 4px 15px
        rgba(69,10,10,0.25);

}


.header-left {

    display: flex;

    align-items: center;

    gap: 13px;

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

    font-size: 24px;

}


.header h1 {

    font-size: 24px;

    margin: 0;

}


.back {

    text-decoration: none;

    color: white;

    background:
        rgba(255,255,255,0.15);

    border:
        1px solid
        rgba(255,255,255,0.25);

    padding: 11px 18px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

    transition: 0.3s;

}


.back:hover {

    background:
        rgba(255,255,255,0.25);

}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {

    width: 94%;

    max-width: 1150px;

    margin: 35px auto;

}


/* =========================================================
   CARD
========================================================= */

.card {

    background: white;

    border-radius: 16px;

    padding: 30px;

    margin-bottom: 25px;

    border:
        1px solid #f0dede;

    box-shadow:
        0 7px 25px
        rgba(69,10,10,0.08);

}


/* =========================================================
   CARD TITLE
========================================================= */

.card-title {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 25px;

}


.card-title-icon {

    width: 45px;

    height: 45px;

    border-radius: 11px;

    background: #fce7e7;

    color: #7f1d1d;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

}


.card-title h2 {

    color: #450a0a;

    font-size: 21px;

}


/* =========================================================
   MESSAGES
========================================================= */

.success {

    background: #dcfce7;

    color: #166534;

    border:
        1px solid #bbf7d0;

    padding: 13px 15px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-size: 14px;

    font-weight: bold;

}


.error {

    background: #fee2e2;

    color: #991b1b;

    border:
        1px solid #fecaca;

    padding: 13px 15px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-size: 14px;

    font-weight: bold;

}


/* =========================================================
   FORM GRID
========================================================= */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 22px;

}


.field label {

    display: block;

    margin-bottom: 8px;

    color: #450a0a;

    font-size: 14px;

    font-weight: bold;

}


select {

    width: 100%;

    padding: 13px 14px;

    border:
        1px solid #e0caca;

    border-radius: 9px;

    background: #fffafa;

    color: #333;

    font-size: 14px;

    outline: none;

    cursor: pointer;

    transition: 0.3s;

}


select:focus {

    border-color: #991b1b;

    background: white;

    box-shadow:
        0 0 0 3px
        rgba(153,27,27,0.08);

}


/* =========================================================
   ASSIGN BUTTON
========================================================= */

.assign-button {

    width: 100%;

    margin-top: 22px;

    padding: 14px;

    border: none;

    border-radius: 9px;

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

    transition: 0.3s;

    box-shadow:
        0 5px 15px
        rgba(127,29,29,0.18);

}


.assign-button:hover {

    background:
        linear-gradient(
            135deg,
            #5f1111,
            #7f1d1d
        );

    transform: translateY(-2px);

}


/* =========================================================
   INFORMATION BOX
========================================================= */

.info {

    margin-top: 22px;

    padding: 16px;

    background: #fff7ed;

    border:
        1px solid #fed7aa;

    border-left:
        5px solid #991b1b;

    border-radius: 9px;

    color: #7c2d12;

    font-size: 13px;

    line-height: 1.6;

}


/* =========================================================
   ASSIGNMENT HEADER
========================================================= */

.assignment-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 20px;

}


.assignment-title {

    display: flex;

    align-items: center;

    gap: 12px;

}


.assignment-title-icon {

    width: 45px;

    height: 45px;

    border-radius: 11px;

    background: #fce7e7;

    color: #7f1d1d;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

}


.assignment-title h2 {

    color: #450a0a;

    font-size: 21px;

}


/* =========================================================
   TOTAL
========================================================= */

.total-box {

    background: #fce7e7;

    color: #7f1d1d;

    padding: 9px 14px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;

}


/* =========================================================
   TABLE
========================================================= */

.table-container {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 750px;

}


thead th {

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 14px;

    text-align: left;

    font-size: 13px;

    font-weight: bold;

}


thead th:first-child {

    border-radius: 8px 0 0 0;

}


thead th:last-child {

    border-radius: 0 8px 0 0;

}


tbody td {

    padding: 14px;

    border-bottom:
        1px solid #f0e0e0;

    color: #555;

    font-size: 13px;

}


tbody tr:hover {

    background: #fffafa;

}


/* =========================================================
   FACULTY
========================================================= */

.faculty-code {

    color: #7f1d1d;

    font-weight: bold;

}


.faculty-name {

    color: #444;

}


/* =========================================================
   DEPARTMENT
========================================================= */

.department {

    display: inline-block;

    background: #fce7e7;

    color: #7f1d1d;

    padding: 5px 10px;

    border-radius: 6px;

    font-size: 11px;

    font-weight: bold;

}


/* =========================================================
   HALL
========================================================= */

.hall {

    display: inline-block;

    background: #f8fafc;

    border:
        1px solid #e2e8f0;

    color: #450a0a;

    padding: 6px 10px;

    border-radius: 6px;

    font-weight: bold;

}


/* =========================================================
   CAPACITY
========================================================= */

.capacity {

    font-weight: bold;

    color: #555;

}


/* =========================================================
   DELETE
========================================================= */

.delete-button {

    display: inline-block;

    text-decoration: none;

    background: #dc2626;

    color: white;

    padding: 8px 12px;

    border-radius: 7px;

    font-size: 12px;

    font-weight: bold;

    transition: 0.3s;

}


.delete-button:hover {

    background: #b91c1c;

    transform: translateY(-1px);

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 45px 20px;

    color: #888;

}


.empty-icon {

    font-size: 38px;

    margin-bottom: 10px;

}


.empty-text {

    font-size: 14px;

}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    margin-top: 18px;

    color: #777;

    font-size: 13px;

}


.footer strong {

    color: #7f1d1d;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 750px) {

    .header {

        padding: 18px 20px;

        flex-direction: column;

        align-items: stretch;

    }


    .back {

        text-align: center;

    }


    .container {

        width: 92%;

        margin: 25px auto;

    }


    .card {

        padding: 22px;

    }


    .form-grid {

        grid-template-columns: 1fr;

    }


    .assignment-header {

        flex-direction: column;

        align-items: flex-start;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="header">


    <div class="header-left">

        <div class="header-icon">

            👨‍🏫

        </div>


        <h1>

            Assign Invigilator

        </h1>

    </div>


    <a
        href="admin_dashboard.php"
        class="back"
    >

        ← Dashboard

    </a>

</div>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="container">


    <!-- =====================================================
         ASSIGN FORM CARD
    ====================================================== -->

    <div class="card">


        <?php if ($message != ""): ?>

            <div class="success">

                ✓
                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <?php if ($error != ""): ?>

            <div class="error">

                ⚠
                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <div class="card-title">


            <div class="card-title-icon">

                👨‍🏫

            </div>


            <h2>

                Assign Faculty to Examination Hall

            </h2>


        </div>


        <form method="POST">


            <div class="form-grid">


                <!-- FACULTY -->

                <div class="field">

                    <label>

                        Faculty

                    </label>


                    <select
                        name="faculty_id"
                        required
                    >

                        <option value="">

                            Select Faculty

                        </option>


                        <?php

                        if ($faculty_result->num_rows > 0):

                            while (
                                $faculty =
                                $faculty_result->fetch_assoc()
                            ):

                        ?>


                            <option
                                value="<?php
                                echo $faculty["faculty_id"];
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $faculty["faculty_code"]
                                );
                                ?>

                                -

                                <?php
                                echo htmlspecialchars(
                                    $faculty["faculty_name"]
                                );
                                ?>

                                (

                                <?php
                                echo htmlspecialchars(
                                    $faculty["department"]
                                );
                                ?>

                                )

                            </option>


                        <?php

                            endwhile;

                        endif;

                        ?>

                    </select>

                </div>


                <!-- HALL -->

                <div class="field">

                    <label>

                        Examination Hall

                    </label>


                    <select
                        name="room_id"
                        required
                    >

                        <option value="">

                            Select Hall

                        </option>


                        <?php

                        if ($room_result->num_rows > 0):

                            while (
                                $room =
                                $room_result->fetch_assoc()
                            ):

                        ?>


                            <option
                                value="<?php
                                echo $room["room_id"];
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $room["room_no"]
                                );
                                ?>

                                -

                                Capacity:

                                <?php
                                echo htmlspecialchars(
                                    $room["capacity"]
                                );
                                ?>

                            </option>


                        <?php

                            endwhile;

                        endif;

                        ?>

                    </select>

                </div>


            </div>


            <button
                type="submit"
                class="assign-button"
            >

                👨‍🏫 Assign Invigilator

            </button>


        </form>


        <div class="info">

            <strong>Important:</strong>

            An invigilator is assigned to the entire
            examination hall. A hall may contain students
            from different departments and different exams.

        </div>


    </div>


    <!-- =====================================================
         CURRENT ASSIGNMENTS
    ====================================================== -->

    <div class="card">


        <div class="assignment-header">


            <div class="assignment-title">


                <div class="assignment-title-icon">

                    📋

                </div>


                <h2>

                    Current Invigilator Assignments

                </h2>


            </div>


            <div class="total-box">

                Total:

                <?php
                echo $assignment_result->num_rows;
                ?>

            </div>


        </div>


        <div class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>
                            Faculty
                        </th>

                        <th>
                            Department
                        </th>

                        <th>
                            Hall
                        </th>

                        <th>
                            Capacity
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if ($assignment_result->num_rows > 0):

                    while (
                        $row =
                        $assignment_result->fetch_assoc()
                    ):

                ?>


                    <tr>


                        <!-- FACULTY -->

                        <td>

                            <span class="faculty-code">

                                <?php
                                echo htmlspecialchars(
                                    $row["faculty_code"]
                                );
                                ?>

                            </span>

                            <span class="faculty-name">

                                -

                                <?php
                                echo htmlspecialchars(
                                    $row["faculty_name"]
                                );
                                ?>

                            </span>

                        </td>


                        <!-- DEPARTMENT -->

                        <td>

                            <span class="department">

                                <?php
                                echo htmlspecialchars(
                                    $row["department"]
                                );
                                ?>

                            </span>

                        </td>


                        <!-- HALL -->

                        <td>

                            <span class="hall">

                                <?php
                                echo htmlspecialchars(
                                    $row["room_no"]
                                );
                                ?>

                            </span>

                        </td>


                        <!-- CAPACITY -->

                        <td>

                            <span class="capacity">

                                <?php
                                echo htmlspecialchars(
                                    $row["capacity"]
                                );
                                ?>

                            </span>

                        </td>


                        <!-- DELETE -->

                        <td>

                            <a
                                href="assign_invigilator.php?delete_id=<?php echo $row["invigilator_id"]; ?>"
                                class="delete-button"

                                onclick="
                                    return confirm(
                                        'Are you sure you want to remove this invigilator assignment?'
                                    );
                                "
                            >

                                Delete

                            </a>

                        </td>


                    </tr>


                <?php

                    endwhile;

                else:

                ?>


                    <tr>

                        <td
                            colspan="5"
                            class="empty"
                        >

                            <div class="empty-icon">

                                👨‍🏫

                            </div>

                            <div class="empty-text">

                                No invigilator assignments available.

                            </div>

                        </td>

                    </tr>


                <?php

                endif;

                ?>


                </tbody>


            </table>


        </div>


        <div class="footer">

            Total invigilator assignments:

            <strong>

                <?php
                echo $assignment_result->num_rows;
                ?>

            </strong>

        </div>


    </div>


</div>


</body>

</html>