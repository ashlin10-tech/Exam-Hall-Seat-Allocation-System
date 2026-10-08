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
   DELETE FACULTY
========================================================= */

$message = "";
$error = "";

if (isset($_GET['delete'])) {

    $faculty_id = intval($_GET['delete']);

    if ($faculty_id > 0) {

        /* Check whether faculty is assigned as invigilator */

        $check = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM exam_invigilator
            WHERE faculty_id = ?
        ");

        $check->bind_param("i", $faculty_id);
        $check->execute();

        $check_result = $check->get_result();
        $check_row = $check_result->fetch_assoc();

        $check->close();


        if ($check_row['total'] > 0) {

            $error = "This faculty is assigned as an invigilator and cannot be deleted.";

        } else {

            $delete = $conn->prepare("
                DELETE FROM faculty
                WHERE faculty_id = ?
            ");

            $delete->bind_param("i", $faculty_id);

            if ($delete->execute()) {

                header("Location: manage_faculty.php?deleted=1");
                exit();

            } else {

                $error = "Unable to delete faculty.";

            }

            $delete->close();
        }
    }
}


/* =========================================================
   SUCCESS MESSAGE
========================================================= */

if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {

    $message = "Faculty deleted successfully.";

}


/* =========================================================
   DEPARTMENT FILTER
========================================================= */

$department = isset($_GET['department'])
    ? trim($_GET['department'])
    : "";


/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : "";


/* =========================================================
   GET DEPARTMENTS
========================================================= */

$departments = [];

$department_query = $conn->query("
    SELECT DISTINCT department
    FROM faculty
    ORDER BY department
");

if ($department_query) {

    while ($row = $department_query->fetch_assoc()) {

        $departments[] = $row['department'];

    }

}


/* =========================================================
   FACULTY QUERY
========================================================= */

$sql = "
    SELECT
        faculty_id,
        faculty_code,
        faculty_name,
        username,
        department
    FROM faculty
    WHERE 1 = 1
";

$params = [];
$types = "";


/* Department filter */

if ($department !== "") {

    $sql .= " AND department = ?";

    $params[] = $department;
    $types .= "s";

}


/* Search filter */

if ($search !== "") {

    $sql .= "
        AND (
            faculty_code LIKE ?
            OR faculty_name LIKE ?
            OR username LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";

}


/* Sorting */

$sql .= "
    ORDER BY department ASC, faculty_code ASC
";


/* Prepare */

$stmt = $conn->prepare($sql);


/* Bind */

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


/* Execute */

$stmt->execute();

$result = $stmt->get_result();


/* =========================================================
   TOTAL FACULTY COUNT
========================================================= */

$count_query = $conn->query("
    SELECT COUNT(*) AS total
    FROM faculty
");

$count_row = $count_query->fetch_assoc();

$total_faculty = $count_row['total'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Manage Faculty</title>

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
        0 4px 15px rgba(69, 10, 10, 0.25);

}


.header-left {

    display: flex;

    align-items: center;

    gap: 12px;

}


.header-icon {

    width: 46px;

    height: 46px;

    background:
        rgba(255,255,255,0.15);

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;

}


.header h1 {

    margin: 0;

    font-size: 24px;

}


.header-right {

    display: flex;

    align-items: center;

    gap: 15px;

}


.admin-name {

    font-size: 14px;

}


.dashboard-btn {

    background:
        rgba(255,255,255,0.15);

    border:
        1px solid rgba(255,255,255,0.25);

    color: white;

    text-decoration: none;

    padding: 10px 17px;

    border-radius: 8px;

    font-weight: bold;

    font-size: 14px;

    transition: 0.3s;

}


.dashboard-btn:hover {

    background:
        rgba(255,255,255,0.25);

}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container {

    width: 94%;

    max-width: 1450px;

    margin: 35px auto;

}


/* =========================================================
   TOP SECTION
========================================================= */

.top-section {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 22px;

}


.title-area {

    display: flex;

    align-items: center;

    gap: 14px;

}


.title-icon {

    width: 52px;

    height: 52px;

    border-radius: 13px;

    background: #fce7e7;

    color: #7f1d1d;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

}


.top-section h2 {

    margin: 0;

    color: #450a0a;

    font-size: 25px;

}


.title-area p {

    margin: 5px 0 0;

    color: #888;

    font-size: 13px;

}


.total-box {

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    padding: 13px 20px;

    border-radius: 10px;

    font-weight: bold;

    box-shadow:
        0 4px 12px rgba(127,29,29,0.18);

}


/* =========================================================
   MESSAGES
========================================================= */

.success {

    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;

    padding: 13px 18px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-weight: bold;

}


.error {

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

    padding: 13px 18px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-weight: bold;

}


/* =========================================================
   FILTER BOX
========================================================= */

.filter-box {

    background: white;

    padding: 22px;

    border-radius: 14px;

    border: 1px solid #f0dede;

    box-shadow:
        0 5px 18px rgba(69,10,10,0.07);

    margin-bottom: 25px;

}


.filter-title {

    color: #450a0a;

    font-size: 16px;

    font-weight: bold;

    margin-bottom: 17px;

}


.filter-form {

    display: flex;

    gap: 15px;

    align-items: end;

    flex-wrap: wrap;

}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 7px;

}


.form-group label {

    font-size: 13px;

    font-weight: bold;

    color: #555;

}


select,
input[type="text"] {

    width: 260px;

    padding: 12px;

    border: 1px solid #e0caca;

    border-radius: 8px;

    font-size: 14px;

    background: #fffafa;

    outline: none;

    color: #333;

}


select:focus,
input[type="text"]:focus {

    border-color: #991b1b;

    background: white;

    box-shadow:
        0 0 0 3px rgba(153,27,27,0.08);

}


.filter-btn {

    background:
        linear-gradient(
            135deg,
            #7f1d1d,
            #991b1b
        );

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 8px;

    cursor: pointer;

    font-weight: bold;

    font-size: 14px;

    transition: 0.3s;

}


.filter-btn:hover {

    transform: translateY(-2px);

}


.clear-btn {

    background: #f1f5f9;

    color: #555;

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 8px;

    border: 1px solid #e2e8f0;

    font-weight: bold;

    font-size: 14px;

}


.clear-btn:hover {

    background: #e2e8f0;

}


/* =========================================================
   TABLE BOX
========================================================= */

.table-box {

    background: white;

    border-radius: 15px;

    border: 1px solid #f0dede;

    box-shadow:
        0 6px 20px rgba(69,10,10,0.08);

    overflow: hidden;

}


.table-header {

    padding: 19px 22px;

    border-bottom: 1px solid #f0dede;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.table-header h3 {

    margin: 0;

    color: #450a0a;

    font-size: 17px;

}


.result-count {

    background: #fce7e7;

    color: #7f1d1d;

    padding: 5px 11px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 950px;

}


thead {

    background:
        linear-gradient(
            135deg,
            #450a0a,
            #7f1d1d
        );

    color: white;

}


th {

    padding: 14px 15px;

    text-align: left;

    font-size: 13px;

    white-space: nowrap;

}


td {

    padding: 14px 15px;

    border-bottom: 1px solid #f1e3e3;

    font-size: 14px;

}


tbody tr {

    transition: 0.2s;

}


tbody tr:hover {

    background: #fffafa;

}


/* =========================================================
   FACULTY CODE
========================================================= */

.faculty-code {

    font-weight: bold;

    color: #7f1d1d;

}


/* =========================================================
   USERNAME
========================================================= */

.username {

    color: #555;

    font-family: monospace;

    font-size: 13px;

}


/* =========================================================
   DEPARTMENT BADGE
========================================================= */

.department-badge {

    display: inline-block;

    background: #fce7e7;

    color: #7f1d1d;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;

}


.edit-btn,
.reset-btn,
.delete-btn {

    display: inline-block;

    color: white;

    padding: 8px 11px;

    border-radius: 6px;

    text-decoration: none;

    font-size: 12px;

    font-weight: bold;

    transition: 0.2s;

}


.edit-btn {

    background: #7f1d1d;

}


.edit-btn:hover {

    background: #5f1111;

}


.reset-btn {

    background: #047857;

}


.reset-btn:hover {

    background: #065f46;

}


.delete-btn {

    background: #dc2626;

}


.delete-btn:hover {

    background: #b91c1c;

}


.edit-btn:hover,
.reset-btn:hover,
.delete-btn:hover {

    transform: translateY(-1px);

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 55px 20px;

    color: #777;

    font-size: 15px;

}


.empty-icon {

    width: 65px;

    height: 65px;

    margin: 0 auto 15px;

    border-radius: 50%;

    background: #fce7e7;

    color: #7f1d1d;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 28px;

}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    text-align: center;

    padding: 25px;

    color: #888;

    font-size: 12px;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .header {

        padding: 18px 20px;

        flex-direction: column;

        gap: 15px;

        align-items: stretch;

    }


    .header-right {

        flex-direction: column;

    }


    .dashboard-btn {

        text-align: center;

    }


    .container {

        width: 92%;

        margin: 25px auto;

    }


    .top-section {

        flex-direction: column;

        align-items: stretch;

        gap: 15px;

    }


    .total-box {

        text-align: center;

    }


    .filter-form {

        flex-direction: column;

        align-items: stretch;

    }


    select,
    input[type="text"] {

        width: 100%;

    }


    .filter-btn,
    .clear-btn {

        width: 100%;

        text-align: center;

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
            Manage Faculty
        </h1>

    </div>


    <div class="header-right">

        <span class="admin-name">

            Welcome,
            <?php
            echo htmlspecialchars(
                $_SESSION['admin_username']
            );
            ?>

        </span>


        <a
            href="admin_dashboard.php"
            class="dashboard-btn"
        >
            ← Dashboard
        </a>

    </div>

</div>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="container">


    <!-- TOP -->

    <div class="top-section">

        <div class="title-area">

            <div class="title-icon">
                👥
            </div>

            <div>

                <h2>
                    Faculty Management
                </h2>

                <p>
                    View, search and manage faculty accounts
                </p>

            </div>

        </div>


        <div class="total-box">

            Total Faculties:
            <?php echo $total_faculty; ?>

        </div>

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


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <div class="filter-box">

        <div class="filter-title">
            🔍 Search & Filter Faculty
        </div>


        <form
            method="GET"
            action="manage_faculty.php"
            class="filter-form"
        >


            <!-- DEPARTMENT -->

            <div class="form-group">

                <label>
                    Department
                </label>


                <select name="department">

                    <option value="">
                        All Departments
                    </option>


                    <?php foreach ($departments as $dept) { ?>

                        <option
                            value="<?php echo htmlspecialchars($dept); ?>"
                            <?php
                            if ($department === $dept) {
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


            <!-- SEARCH -->

            <div class="form-group">

                <label>
                    Search Faculty
                </label>


                <input
                    type="text"
                    name="search"
                    placeholder="Name, code or username"
                    value="<?php echo htmlspecialchars($search); ?>"
                >

            </div>


            <!-- SEARCH -->

            <button
                type="submit"
                class="filter-btn"
            >
                🔍 Search / Filter
            </button>


            <!-- CLEAR -->

            <a
                href="manage_faculty.php"
                class="clear-btn"
            >
                ✕ Clear
            </a>


        </form>

    </div>


    <!-- =====================================================
         TABLE
    ====================================================== -->

    <div class="table-box">


        <div class="table-header">

            <h3>

                <?php

                if ($department !== "") {

                    echo htmlspecialchars($department);

                } else {

                    echo "All Departments";

                }

                ?>

                — Faculty List

            </h3>


            <span class="result-count">

                <?php echo $result->num_rows; ?>

                Results

            </span>

        </div>


        <div class="table-wrapper">

            <table>


                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Faculty Code
                        </th>

                        <th>
                            Faculty Name
                        </th>

                        <th>
                            Username
                        </th>

                        <th>
                            Department
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $count = 1;


                if ($result->num_rows > 0) {

                    while ($faculty = $result->fetch_assoc()) {

                ?>


                    <tr>


                        <!-- NUMBER -->

                        <td>

                            <?php
                            echo $count++;
                            ?>

                        </td>


                        <!-- FACULTY CODE -->

                        <td>

                            <span class="faculty-code">

                                <?php

                                echo htmlspecialchars(
                                    $faculty['faculty_code']
                                );

                                ?>

                            </span>

                        </td>


                        <!-- FACULTY NAME -->

                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $faculty['faculty_name']
                                );

                                ?>

                            </strong>

                        </td>


                        <!-- USERNAME -->

                        <td>

                            <span class="username">

                                <?php

                                echo htmlspecialchars(
                                    $faculty['username']
                                );

                                ?>

                            </span>

                        </td>


                        <!-- DEPARTMENT -->

                        <td>

                            <span class="department-badge">

                                <?php

                                echo htmlspecialchars(
                                    $faculty['department']
                                );

                                ?>

                            </span>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <div class="actions">


                                <!-- EDIT -->

                                <a
                                    href="edit_faculty.php?id=<?php echo $faculty['faculty_id']; ?>"
                                    class="edit-btn"
                                >
                                    ✎ Edit
                                </a>


                                <!-- RESET -->

                                <a
                                    href="faculty_reset_password.php?id=<?php echo $faculty['faculty_id']; ?>"
                                    class="reset-btn"
                                >
                                    🔑 Reset
                                </a>


                                <!-- DELETE -->

                                <a
                                    href="manage_faculty.php?delete=<?php echo $faculty['faculty_id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm(
                                        'Are you sure you want to delete this faculty?'
                                    );"
                                >
                                    🗑 Delete
                                </a>


                            </div>

                        </td>


                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="6"
                            class="empty"
                        >

                            <div class="empty-icon">
                                👨‍🏫
                            </div>

                            No faculty found.

                        </td>

                    </tr>


                <?php

                }

                ?>


                </tbody>

            </table>

        </div>

    </div>


</div>


<!-- FOOTER -->

<div class="footer">

    Exam Hall Seat Allocation System

</div>


</body>

</html>