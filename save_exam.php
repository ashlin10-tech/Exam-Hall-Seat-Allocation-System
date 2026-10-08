<?php
session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: exam_form.php");
    exit();
}

$subject   = trim($_POST["subject"] ?? "");
$exam_date = trim($_POST["exam_date"] ?? "");
$session   = trim($_POST["session"] ?? "");
$exam_type = trim($_POST["exam_type"] ?? "");

/* ---------------- VALIDATION ---------------- */

if ($subject === "" || $exam_date === "") {
    showMessage("error", "Please fill all required fields.", "exam_form.php");
    exit();
}

if (!in_array($session, ["FN", "AN"])) {
    showMessage("error", "Invalid exam session.", "exam_form.php");
    exit();
}

if (!in_array($exam_type, ["REGULAR", "SEMESTER"])) {
    showMessage("error", "Invalid exam type.", "exam_form.php");
    exit();
}

/* Validate date format */
$dateObject = DateTime::createFromFormat("Y-m-d", $exam_date);

if (!$dateObject || $dateObject->format("Y-m-d") !== $exam_date) {
    showMessage("error", "Invalid exam date.", "exam_form.php");
    exit();
}

/* Prevent past dates */
$today = date("Y-m-d");

if ($exam_date < $today) {
    showMessage("error", "Exam date cannot be in the past.", "exam_form.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
| We intentionally DO NOT check:
|
| subject + date + session
|
| as a duplicate.
|
| The same subject can exist more than once on the same date/session.
| Each record gets its own exam_id internally.
|--------------------------------------------------------------------------
*/

/* ---------------- INSERT EXAM ---------------- */

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO exam (subject, exam_date, session, exam_type)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    showMessage("error", "Database error while preparing exam.", "exam_form.php");
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $subject,
    $exam_date,
    $session,
    $exam_type
);

if (mysqli_stmt_execute($stmt)) {

    $typeText = ($exam_type === "REGULAR")
        ? "Regular Exam"
        : "Semester Exam";

    showSuccess(
        $subject,
        $exam_date,
        $session,
        $typeText
    );

} else {

    showMessage(
        "error",
        "Unable to save exam. Please try again.",
        "exam_form.php"
    );
}

mysqli_stmt_close($stmt);


/* =========================================================
   ERROR PAGE
========================================================= */

function showMessage($type, $message, $backLink)
{
    $isError = ($type === "error");

    $title = $isError ? "Something went wrong" : "Success";
    $icon  = $isError ? "⚠️" : "✅";

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $title; ?></title>

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #450a0a, #7f1d1d, #991b1b);
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .card {
                background: white;
                width: 100%;
                max-width: 520px;
                padding: 40px;
                border-radius: 20px;
                text-align: center;
                box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            }

            .icon {
                font-size: 55px;
                margin-bottom: 15px;
            }

            h1 {
                color: #7f1d1d;
                margin-bottom: 15px;
            }

            p {
                color: #555;
                font-size: 16px;
                line-height: 1.6;
            }

            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 13px 25px;
                border-radius: 10px;
                text-decoration: none;
                color: white;
                background: #991b1b;
                font-weight: bold;
            }

            .btn:hover {
                background: #7f1d1d;
            }
        </style>
    </head>

    <body>

        <div class="card">

            <div class="icon"><?php echo $icon; ?></div>

            <h1><?php echo htmlspecialchars($title); ?></h1>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

            <a href="<?php echo htmlspecialchars($backLink); ?>" class="btn">
                Go Back
            </a>

        </div>

    </body>
    </html>
    <?php
}


/* =========================================================
   SUCCESS PAGE
========================================================= */

function showSuccess($subject, $date, $session, $typeText)
{
    ?>
    <!DOCTYPE html>
    <html>

    <head>

        <title>Exam Added</title>

        <style>

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: Arial, sans-serif;
                background: linear-gradient(
                    135deg,
                    #450a0a,
                    #7f1d1d,
                    #991b1b
                );

                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .card {
                width: 100%;
                max-width: 550px;
                background: white;
                padding: 40px;
                border-radius: 20px;
                text-align: center;
                box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            }

            .icon {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: #dcfce7;
                color: #16a34a;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                font-size: 40px;
            }

            h1 {
                color: #7f1d1d;
                margin-bottom: 25px;
            }

            .details {
                background: #f8fafc;
                border-radius: 12px;
                padding: 20px;
                text-align: left;
                margin-bottom: 25px;
            }

            .row {
                display: flex;
                justify-content: space-between;
                gap: 20px;
                padding: 10px 0;
                border-bottom: 1px solid #e5e7eb;
            }

            .row:last-child {
                border-bottom: none;
            }

            .label {
                color: #666;
                font-weight: bold;
            }

            .value {
                color: #222;
                font-weight: bold;
                text-align: right;
            }

            .buttons {
                display: flex;
                gap: 10px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                padding: 13px 20px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: bold;
                color: white;
                background: #991b1b;
            }

            .btn.secondary {
                background: #450a0a;
            }

            .btn:hover {
                opacity: 0.9;
            }

        </style>

    </head>

    <body>

        <div class="card">

            <div class="icon">✓</div>

            <h1>Exam Added Successfully</h1>

            <div class="details">

                <div class="row">
                    <span class="label">Subject</span>
                    <span class="value">
                        <?php echo htmlspecialchars($subject); ?>
                    </span>
                </div>

                <div class="row">
                    <span class="label">Exam Date</span>
                    <span class="value">
                        <?php echo date("d-m-Y", strtotime($date)); ?>
                    </span>
                </div>

                <div class="row">
                    <span class="label">Session</span>
                    <span class="value">
                        <?php echo htmlspecialchars($session); ?>
                    </span>
                </div>

                <div class="row">
                    <span class="label">Exam Type</span>
                    <span class="value">
                        <?php echo htmlspecialchars($typeText); ?>
                    </span>
                </div>

            </div>

            <div class="buttons">

                <a href="exam_form.php" class="btn">
                    Add Another Exam
                </a>

                <a href="admin_dashboard.php" class="btn secondary">
                    Dashboard
                </a>

            </div>

        </div>

    </body>

    </html>
    <?php
}
?>