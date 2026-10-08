<?php
session_start();

include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username == "" || $password == "") {

        $error = "Please enter username and password.";

    } else {

        $sql = "
            SELECT *
            FROM faculty
            WHERE username = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $username
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {

                $faculty = mysqli_fetch_assoc($result);

                /*
                 * Check password
                 *
                 * Supports the current plain-text
                 * faculty password used in your database.
                 */

                $password_valid = false;

                /* Check hashed password */

                if (
                    !empty($faculty['password']) &&
                    password_verify(
                        $password,
                        $faculty['password']
                    )
                ) {

                    $password_valid = true;

                }

                /* Check plain-text password */

                if (
                    !$password_valid &&
                    $faculty['password'] === $password
                ) {

                    $password_valid = true;

                }


                if ($password_valid) {

                    $_SESSION['faculty_logged_in'] = true;

                    $_SESSION['faculty_id'] =
                        $faculty['faculty_id'];

                    $_SESSION['faculty_name'] =
                        $faculty['faculty_name'];

                    $_SESSION['faculty_username'] =
                        $faculty['username'];

                    $_SESSION['faculty_department'] =
                        $faculty['department'];


                    header(
                        "Location: faculty_dashboard.php"
                    );

                    exit();

                } else {

                    $error = "Invalid password.";

                }

            } else {

                $error = "Invalid username.";

            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Database error. Please try again.";

        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Faculty Login</title>

    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #064e3b,
                    #047857,
                    #10b981
                );

        }


        .login-container {

            width: 100%;

            max-width: 420px;

            padding: 20px;

        }


        .login-box {

            background: white;

            padding: 38px;

            border-radius: 15px;

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.25);

        }


        /* FACULTY ICON */

        .icon {

            width: 65px;

            height: 65px;

            margin: 0 auto 18px;

            background: #059669;

            color: white;

            border-radius: 50%;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 28px;

        }


        h2 {

            text-align: center;

            margin: 0;

            color: #1f2937;

            font-size: 25px;

        }


        .subtitle {

            text-align: center;

            color: #6b7280;

            margin: 8px 0 28px;

            font-size: 14px;

        }


        .form-group {

            margin-bottom: 20px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: bold;

            color: #374151;

        }


        input {

            width: 100%;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 7px;

            font-size: 15px;

            outline: none;

        }


        input:focus {

            border-color: #059669;

            box-shadow:
                0 0 0 2px
                rgba(5, 150, 105, 0.10);

        }


        .login-btn {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 7px;

            background: #059669;

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

        }


        .login-btn:hover {

            background: #047857;

        }


        .error {

            background: #fee2e2;

            color: #b91c1c;

            padding: 11px;

            border-radius: 6px;

            margin-bottom: 20px;

            text-align: center;

            font-size: 14px;

        }


        .back {

            display: block;

            text-align: center;

            margin-top: 20px;

            color: #059669;

            text-decoration: none;

            font-size: 14px;

        }


        .back:hover {

            text-decoration: underline;

        }


        @media (max-width: 500px) {

            .login-container {

                padding: 15px;

            }

            .login-box {

                padding: 30px 25px;

            }

        }

    </style>

</head>


<body>


<div class="login-container">


    <div class="login-box">


        <!-- FACULTY ICON -->

        <div class="icon">
            👨‍🏫
        </div>


        <h2>
            Faculty Login
        </h2>


        <div class="subtitle">
            Exam Seat Allocation System
        </div>


        <?php if ($error != "") { ?>

            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php } ?>


        <form method="POST">


            <!-- USERNAME -->

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter faculty username"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST['username'] ?? ''
                        );
                    ?>"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="login-btn"
            >
                Login
            </button>


        </form>


        <a
            href="index.php"
            class="back"
        >
            ← Back to Home
        </a>


    </div>

</div>


</body>

</html>