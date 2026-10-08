<?php
session_start();

include 'db_connect.php';

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $faculty_code = trim($_POST["faculty_code"] ?? "");
    $faculty_name = trim($_POST["faculty_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $department = trim($_POST["department"] ?? "");

    if (
        $faculty_code === "" ||
        $faculty_name === "" ||
        $username === "" ||
        $password === "" ||
        $department === ""
    ) {

        $error = "Please fill all fields.";

    } else {

        // Check duplicate faculty code or username
        $check = $conn->prepare(
            "SELECT faculty_id
             FROM faculty
             WHERE faculty_code = ? OR username = ?"
        );

        $check->bind_param(
            "ss",
            $faculty_code,
            $username
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Faculty code or username already exists.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO faculty
                (faculty_code, faculty_name, username, password, department)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $faculty_code,
                $faculty_name,
                $username,
                $password,
                $department
            );

            if ($stmt->execute()) {

                $message = "Faculty added successfully.";

            } else {

                $error = "Unable to add faculty.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Faculty</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff7f7;
            min-height: 100vh;
            color: #333;
        }

        /* HEADER */

        .header {
            background: linear-gradient(
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
                0 4px 15px rgba(69, 10, 10, 0.25);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            width: 45px;
            height: 45px;

            background: rgba(255,255,255,0.15);

            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 23px;
        }

        .header h2 {
            font-size: 23px;
            margin: 0;
        }

        .dashboard-btn {
            text-decoration: none;
            color: white;

            background: rgba(255,255,255,0.15);

            border: 1px solid rgba(255,255,255,0.25);

            padding: 11px 18px;

            border-radius: 8px;

            font-size: 14px;
            font-weight: bold;

            transition: 0.3s;
        }

        .dashboard-btn:hover {
            background: rgba(255,255,255,0.25);
        }

        /* MAIN */

        .main {
            width: 92%;
            max-width: 700px;

            margin: 45px auto;
        }

        .form-card {
            background: white;

            border-radius: 18px;

            padding: 35px;

            border: 1px solid #f0dede;

            box-shadow:
                0 8px 25px rgba(69, 10, 10, 0.10);

            position: relative;

            overflow: hidden;
        }

        .form-card::before {
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

        /* TITLE */

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

        .title-section h1 {
            color: #450a0a;

            font-size: 28px;

            margin-bottom: 7px;
        }

        .title-section p {
            color: #888;

            font-size: 14px;
        }

        /* ALERTS */

        .success {
            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 22px;

            text-align: center;

            font-size: 14px;

            font-weight: bold;
        }

        .error {
            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 22px;

            text-align: center;

            font-size: 14px;

            font-weight: bold;
        }

        /* FORM */

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;

            margin-bottom: 8px;

            font-weight: bold;

            color: #450a0a;

            font-size: 14px;
        }

        input,
        select {
            width: 100%;

            padding: 13px 14px;

            border: 1px solid #e0caca;

            border-radius: 9px;

            background: #fffafa;

            font-size: 14px;

            color: #333;

            transition: 0.3s;
        }

        input::placeholder {
            color: #aaa;
        }

        input:focus,
        select:focus {
            outline: none;

            border-color: #991b1b;

            background: white;

            box-shadow:
                0 0 0 3px rgba(153, 27, 27, 0.08);
        }

        select {
            cursor: pointer;
        }

        /* PASSWORD NOTE */

        .password-note {
            margin-top: -12px;

            margin-bottom: 20px;

            font-size: 12px;

            color: #888;
        }

        /* BUTTON */

        .submit-btn {
            width: 100%;

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

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;

            box-shadow:
                0 4px 12px rgba(127, 29, 29, 0.20);
        }

        .submit-btn:hover {
            background:
                linear-gradient(
                    135deg,
                    #5f1111,
                    #7f1d1d
                );

            transform: translateY(-2px);
        }

        /* BOTTOM */

        .bottom-links {
            display: flex;

            justify-content: center;

            gap: 20px;

            margin-top: 22px;
        }

        .back {
            text-decoration: none;

            color: #7f1d1d;

            font-size: 14px;

            font-weight: bold;
        }

        .back:hover {
            text-decoration: underline;
        }

        /* INFO */

        .info-box {
            margin-top: 25px;

            background: #fffafa;

            border: 1px solid #f0dede;

            border-radius: 10px;

            padding: 14px;

            text-align: center;

            color: #777;

            font-size: 12px;
        }

        /* RESPONSIVE */

        @media (max-width: 650px) {

            .header {
                padding: 18px 20px;

                flex-direction: column;

                gap: 15px;

                align-items: stretch;
            }

            .dashboard-btn {
                text-align: center;
            }

            .main {
                width: 92%;

                margin: 30px auto;
            }

            .form-card {
                padding: 25px 20px;
            }

            .title-section h1 {
                font-size: 24px;
            }

        }

    </style>

</head>

<body>


<!-- HEADER -->

<div class="header">

    <div class="header-left">

        <div class="header-icon">
            👨‍🏫
        </div>

        <h2>Add Faculty</h2>

    </div>

    <a href="admin_dashboard.php" class="dashboard-btn">
        ← Dashboard
    </a>

</div>


<!-- MAIN -->

<div class="main">

    <div class="form-card">

        <!-- TITLE -->

        <div class="title-section">

            <div class="title-icon">
                👨‍🏫
            </div>

            <h1>Add Faculty</h1>

            <p>
                Create a new faculty login account
            </p>

        </div>


        <!-- SUCCESS -->

        <?php if ($message !== ""): ?>

            <div class="success">

                ✓
                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="error">

                ⚠
                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST">


            <!-- FACULTY CODE -->

            <div class="form-group">

                <label>
                    Faculty Code
                </label>

                <input
                    type="text"
                    name="faculty_code"
                    placeholder="Example: FAC004"
                    required
                >

            </div>


            <!-- FACULTY NAME -->

            <div class="form-group">

                <label>
                    Faculty Name
                </label>

                <input
                    type="text"
                    name="faculty_name"
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
                    placeholder="Enter login username"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter login password"
                    required
                >

            </div>

            <div class="password-note">
                Faculty will use this password to log in.
            </div>


            <!-- DEPARTMENT -->

            <div class="form-group">

                <label>
                    Department
                </label>

                <select name="department" required>

                    <option value="">
                        Select Department
                    </option>

                    <option value="CSE">CSE</option>
                    <option value="IT">IT</option>
                    <option value="ECE">ECE</option>
                    <option value="EEE">EEE</option>
                    <option value="MECH">MECH</option>
                    <option value="AIDS">AI & DS</option>
                    <option value="AIML">AI & ML</option>
                    <option value="CYBER">Cyber Security</option>
                    <option value="FOOD TECH">Food Technology</option>
                    <option value="BIOTECH">Biotechnology</option>
                    <option value="BME">Biomedical Engineering</option>
                    <option value="AGRI">Agricultural Engineering</option>
                    <option value="VLSI">VLSI</option>
                    <option value="CIVIL">Civil Engineering</option>

                </select>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="submit-btn"
            >
                👨‍🏫 Add Faculty
            </button>

        </form>


        <!-- INFO -->

        <div class="info-box">

            Faculty accounts can be managed later from
            <strong>Manage Faculty</strong>.

        </div>


        <!-- BACK -->

        <div class="bottom-links">

            <a
                href="admin_dashboard.php"
                class="back"
            >
                ← Back to Admin Dashboard
            </a>

        </div>

    </div>

</div>

</body>

</html>