<?php

session_start();
include 'db_connect.php';


/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header("Location: admin_login.php");
    exit();
}


/* =========================================================
   GET FACULTY ID
========================================================= */

$faculty_id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($faculty_id <= 0) {
    header("Location: manage_faculty.php");
    exit();
}


/* =========================================================
   GET FACULTY DETAILS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        faculty_id,
        faculty_code,
        faculty_name,
        username,
        department
    FROM faculty
    WHERE faculty_id = ?
");

$stmt->bind_param("i", $faculty_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_faculty.php");
    exit();
}

$faculty = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   UPDATE FACULTY
========================================================= */

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $faculty_name = trim($_POST['faculty_name'] ?? "");
    $username = trim($_POST['username'] ?? "");
    $department = trim($_POST['department'] ?? "");


    if (
        $faculty_name === "" ||
        $username === "" ||
        $department === ""
    ) {

        $error = "Please fill all fields.";

    } else {


        /* CHECK USERNAME */

        $check = $conn->prepare("
            SELECT faculty_id
            FROM faculty
            WHERE username = ?
            AND faculty_id != ?
        ");

        $check->bind_param(
            "si",
            $username,
            $faculty_id
        );

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $error = "Username already exists.";

        } else {


            /* UPDATE */

            $update = $conn->prepare("
                UPDATE faculty
                SET
                    faculty_name = ?,
                    username = ?,
                    department = ?
                WHERE faculty_id = ?
            ");

            $update->bind_param(
                "sssi",
                $faculty_name,
                $username,
                $department,
                $faculty_id
            );


            if ($update->execute()) {

                $message = "Faculty details updated successfully.";

                $faculty['faculty_name'] = $faculty_name;
                $faculty['username'] = $username;
                $faculty['department'] = $department;

            } else {

                $error = "Unable to update faculty.";

            }

            $update->close();
        }

        $check->close();
    }
}


/* =========================================================
   DEPARTMENTS
========================================================= */

$departments = [
    "CSE",
    "IT",
    "ECE",
    "EEE",
    "MECH",
    "AIDS",
    "AIML",
    "CYBER",
    "FOOD TECH",
    "BIOTECH",
    "BME",
    "AGRI",
    "VLSI",
    "CIVIL"
];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Faculty</title>


<style>

/* =========================================================
   RESET
========================================================= */

* {
    box-sizing: border-box;
}


/* =========================================================
   BODY
========================================================= */

body {

    margin: 0;

    font-family: Arial, Helvetica, sans-serif;

    background: #fff7f7;

    color: #333;

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

    box-shadow:
        0 4px 15px rgba(69,10,10,0.25);

}


.header-left {

    display: flex;

    align-items: center;

    gap: 12px;

}


.header-icon {

    width: 46px;

    height: 46px;

    border-radius: 12px;

    background:
        rgba(255,255,255,0.15);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;

}


.header h1 {

    margin: 0;

    font-size: 24px;

}


.back-btn {

    text-decoration: none;

    color: white;

    background:
        rgba(255,255,255,0.15);

    border:
        1px solid rgba(255,255,255,0.25);

    padding: 11px 18px;

    border-radius: 8px;

    font-size: 14px;

    font-weight: bold;

    transition: 0.3s;

}


.back-btn:hover {

    background:
        rgba(255,255,255,0.25);

}


/* =========================================================
   MAIN
========================================================= */

.container {

    width: 92%;

    max-width: 720px;

    margin: 45px auto;

}


/* =========================================================
   CARD
========================================================= */

.card {

    background: white;

    padding: 35px;

    border-radius: 18px;

    border: 1px solid #f0dede;

    box-shadow:
        0 8px 25px rgba(69,10,10,0.10);

    position: relative;

    overflow: hidden;

}


.card::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 5px;

    background:
        linear-gradient(
            90deg,
            #7f1d1d,
            #991b1b
        );

}


/* =========================================================
   TITLE
========================================================= */

.title-section {

    text-align: center;

    margin-bottom: 30px;

}


.title-icon {

    width: 65px;

    height: 65px;

    margin: 0 auto 15px;

    border-radius: 16px;

    background: #fce7e7;

    color: #7f1d1d;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

}


.title-section h2 {

    margin: 0;

    color: #450a0a;

    font-size: 27px;

}


.title-section p {

    margin-top: 8px;

    color: #888;

    font-size: 14px;

}


/* =========================================================
   MESSAGES
========================================================= */

.success {

    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;

    padding: 13px 15px;

    border-radius: 9px;

    margin-bottom: 22px;

    text-align: center;

    font-weight: bold;

    font-size: 14px;

}


.error {

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

    padding: 13px 15px;

    border-radius: 9px;

    margin-bottom: 22px;

    text-align: center;

    font-weight: bold;

    font-size: 14px;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 21px;

}


label {

    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: bold;

    color: #450a0a;

}


input,
select {

    width: 100%;

    padding: 13px 14px;

    border: 1px solid #e0caca;

    border-radius: 9px;

    font-size: 14px;

    background: #fffafa;

    color: #333;

    transition: 0.3s;

}


input:focus,
select:focus {

    outline: none;

    border-color: #991b1b;

    background: white;

    box-shadow:
        0 0 0 3px rgba(153,27,27,0.08);

}


input[readonly] {

    background: #f8eeee;

    color: #777;

    cursor: not-allowed;

}


.readonly-note {

    margin-top: -13px;

    margin-bottom: 20px;

    color: #999;

    font-size: 12px;

}


/* =========================================================
   BUTTONS
========================================================= */

.buttons {

    display: flex;

    gap: 12px;

    margin-top: 28px;

}


.save-btn {

    flex: 1;

    border: none;

    padding: 14px;

    border-radius: 9px;

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    font-size: 14px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;

    box-shadow:
        0 4px 12px rgba(127,29,29,0.20);

}


.save-btn:hover {

    background:
        linear-gradient(
            135deg,
            #5f1111,
            #7f1d1d
        );

    transform: translateY(-2px);

}


.cancel-btn {

    flex: 1;

    text-align: center;

    text-decoration: none;

    padding: 14px;

    border-radius: 9px;

    background: #f1f5f9;

    color: #555;

    border: 1px solid #e2e8f0;

    font-size: 14px;

    font-weight: bold;

    transition: 0.3s;

}


.cancel-btn:hover {

    background: #e2e8f0;

}


/* =========================================================
   INFO BOX
========================================================= */

.info-box {

    margin-top: 25px;

    padding: 14px;

    border-radius: 10px;

    background: #fffafa;

    border: 1px solid #f0dede;

    color: #777;

    font-size: 12px;

    text-align: center;

}


.info-box strong {

    color: #7f1d1d;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 650px) {

    .header {

        padding: 18px 20px;

        flex-direction: column;

        gap: 15px;

        align-items: stretch;

    }


    .back-btn {

        text-align: center;

    }


    .container {

        width: 92%;

        margin: 30px auto;

    }


    .card {

        padding: 25px 20px;

    }


    .title-section h2 {

        font-size: 24px;

    }


    .buttons {

        flex-direction: column;

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
            ✎
        </div>

        <h1>
            Edit Faculty
        </h1>

    </div>


    <a
        href="manage_faculty.php"
        class="back-btn"
    >
        ← Back to Faculty
    </a>


</div>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="container">


    <div class="card">


        <!-- TITLE -->

        <div class="title-section">

            <div class="title-icon">
                ✎
            </div>

            <h2>
                Faculty Details
            </h2>

            <p>
                Update faculty information and department
            </p>

        </div>


        <!-- SUCCESS -->

        <?php if ($message !== "") { ?>

            <div class="success">

                ✓
                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>


        <!-- ERROR -->

        <?php if ($error !== "") { ?>

            <div class="error">

                ⚠
                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php } ?>


        <!-- FORM -->

        <form method="POST">


            <!-- FACULTY CODE -->

            <div class="form-group">

                <label>
                    Faculty Code
                </label>


                <input
                    type="text"
                    value="<?php
                        echo htmlspecialchars(
                            $faculty['faculty_code']
                        );
                    ?>"
                    readonly
                >

            </div>


            <div class="readonly-note">

                Faculty code cannot be changed.

            </div>


            <!-- FACULTY NAME -->

            <div class="form-group">

                <label>
                    Faculty Name
                </label>


                <input
                    type="text"
                    name="faculty_name"
                    value="<?php
                        echo htmlspecialchars(
                            $faculty['faculty_name']
                        );
                    ?>"
                    placeholder="Enter faculty name"
                    required
                >

            </div>


            <!-- USERNAME -->

            <div class="form-group">

                <label>
                    Username
                </label>


                <input
                    type="text"
                    name="username"
                    value="<?php
                        echo htmlspecialchars(
                            $faculty['username']
                        );
                    ?>"
                    placeholder="Enter login username"
                    required
                >

            </div>


            <!-- DEPARTMENT -->

            <div class="form-group">

                <label>
                    Department
                </label>


                <select
                    name="department"
                    required
                >

                    <?php foreach ($departments as $dept) { ?>

                        <option
                            value="<?php
                                echo htmlspecialchars($dept);
                            ?>"
                            <?php
                            if (
                                $faculty['department']
                                === $dept
                            ) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php
                            echo htmlspecialchars($dept);
                            ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- BUTTONS -->

            <div class="buttons">


                <button
                    type="submit"
                    class="save-btn"
                >
                    ✓ Save Changes
                </button>


                <a
                    href="manage_faculty.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>


            </div>


        </form>


        <!-- INFO -->

        <div class="info-box">

            <strong>Note:</strong>
            Password is managed separately through
            <strong>Reset Password</strong>.

        </div>


    </div>

</div>


</body>

</html>