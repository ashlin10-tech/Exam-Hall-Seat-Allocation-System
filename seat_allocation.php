<?php
session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit;
}

include "db_connect.php";

/* --------------------------------
   FETCH DEPARTMENTS
--------------------------------- */

$departments = [];

$sql = "SELECT dept_id, dept_name
        FROM department
        ORDER BY dept_id";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}


/* --------------------------------
   FETCH ROOMS
--------------------------------- */

$rooms = [];

$sql = "SELECT room_id, room_no, capacity
        FROM room
        WHERE capacity >= 60
        ORDER BY room_no";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}


/* --------------------------------
   FETCH EXAMS
--------------------------------- */

$exams = [];

$sql = "SELECT
            exam_id,
            subject,
            exam_date,
            session,
            exam_type
        FROM exam
        ORDER BY exam_date, session, subject, exam_id";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Allocate Seats</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f7eeee;
    color: #333;
}


/* -----------------------------
   HEADER
------------------------------ */

.header {
    background: #800020;
    color: white;
    padding: 18px 30px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    margin: 0;
    font-size: 25px;
}

.back-btn {
    text-decoration: none;
    color: white;
    background: #5e0018;
    padding: 10px 18px;
    border-radius: 6px;
}


/* -----------------------------
   CONTAINER
------------------------------ */

.container {
    width: 92%;
    max-width: 1100px;
    margin: 30px auto;
}


/* -----------------------------
   CARD
------------------------------ */

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;

    box-shadow: 0 4px 12px rgba(0,0,0,0.08);

    margin-bottom: 25px;
}

.card h2 {
    color: #800020;
    margin-top: 0;
}


/* -----------------------------
   ROOM
------------------------------ */

.room-section {
    margin-bottom: 25px;
}

.room-label {
    font-weight: bold;
    color: #800020;
    margin-bottom: 8px;
}

.room-select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 7px;
    font-size: 15px;
}


/* -----------------------------
   ROOM INFO
------------------------------ */

.room-info {
    margin-top: 10px;
    padding: 10px;
    background: #f8f0f2;
    border-radius: 6px;
    color: #800020;
    display: none;
}


/* -----------------------------
   DEPARTMENT CARD
------------------------------ */

.department-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 15px;
    background: #fff;
}

.department-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.department-name {
    font-weight: bold;
    font-size: 17px;
    color: #800020;
}

.enable-btn {
    background: #800020;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.enable-btn.active {
    background: #444;
}


/* -----------------------------
   DEPARTMENT CONTENT
------------------------------ */

.department-content {
    display: none;
    margin-top: 18px;
}

.department-content.show {
    display: block;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
    color: #555;
}

.exam-select,
.student-count {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}


/* -----------------------------
   CLEAR BUTTON
------------------------------ */

.clear-btn {
    margin-top: 8px;
    background: #777;
    color: white;
    border: none;
    padding: 7px 14px;
    border-radius: 5px;
    cursor: pointer;
}


/* -----------------------------
   TOTAL
------------------------------ */

.total-box {
    background: #800020;
    color: white;
    padding: 16px;
    border-radius: 8px;
    margin-top: 20px;

    display: flex;
    justify-content: space-between;

    font-size: 17px;
    font-weight: bold;
}


/* -----------------------------
   RULES
------------------------------ */

.rules {
    background: #fffdf0;
    border: 1px solid #e5d27c;
    border-radius: 10px;
    padding: 20px;
    margin-top: 25px;
}

.rules h3 {
    color: #800020;
    margin-top: 0;
}

.rules ul {
    line-height: 1.8;
    padding-left: 22px;
}


/* -----------------------------
   ALLOCATE BUTTON
------------------------------ */

.allocate-btn {
    width: 100%;
    margin-top: 25px;

    background: #800020;
    color: white;

    border: none;
    border-radius: 7px;

    padding: 15px;

    font-size: 17px;
    font-weight: bold;

    cursor: pointer;
}

.allocate-btn:hover {
    background: #650019;
}


/* -----------------------------
   EMPTY ROOM
------------------------------ */

.no-room {
    color: #b00020;
    font-weight: bold;
    padding: 10px;
}


/* -----------------------------
   RESPONSIVE
------------------------------ */

@media(max-width: 600px) {

    .container {
        width: 95%;
    }

    .department-header {
        flex-direction: column;
        align-items: flex-start;
    }

}

</style>

</head>


<body>


<!-- HEADER -->

<div class="header">

    <h1>Allocate Seats</h1>

    <a href="admin_dashboard.php" class="back-btn">
        Back to Dashboard
    </a>

</div>


<div class="container">


<form
    id="allocationForm"
    method="POST"
    action="save_allocation.php"
>


<!-- --------------------------------
     ROOM
--------------------------------- -->

<div class="card">

    <h2>1. Select Exam Hall</h2>

    <?php if (count($rooms) == 0) { ?>

        <div class="no-room">
            No hall with capacity of at least 60 students is available.
        </div>

    <?php } else { ?>

        <div class="room-section">

            <div class="room-label">
                Exam Hall
            </div>

            <select
                name="room_id"
                id="room_id"
                class="room-select"
                required
                onchange="updateRoomInfo()"
            >

                <option value="">
                    -- Select Hall --
                </option>

                <?php foreach ($rooms as $room) { ?>

                    <option
                        value="<?php echo $room["room_id"]; ?>"
                        data-capacity="<?php echo $room["capacity"]; ?>"
                    >

                        <?php
                        echo htmlspecialchars($room["room_no"]);
                        ?>

                        -

                        Capacity:

                        <?php
                        echo htmlspecialchars($room["capacity"]);
                        ?>

                    </option>

                <?php } ?>

            </select>


            <div
                id="roomInfo"
                class="room-info"
            ></div>

        </div>

    <?php } ?>

</div>


<!-- --------------------------------
     DEPARTMENTS
--------------------------------- -->

<div class="card">

    <h2>2. Select Departments and Students</h2>


<?php foreach ($departments as $dept) { ?>

    <div
        class="department-card"
        data-dept="<?php echo $dept["dept_id"]; ?>"
    >

        <div class="department-header">

            <div class="department-name">

                <?php
                echo htmlspecialchars($dept["dept_name"]);
                ?>

            </div>


            <button
                type="button"
                class="enable-btn"
                onclick="toggleDepartment(
                    <?php echo $dept["dept_id"]; ?>,
                    this
                )"
            >

                Select Department

            </button>

        </div>


        <div
            id="content_<?php echo $dept["dept_id"]; ?>"
            class="department-content"
        >


            <!-- EXAM -->

            <div class="form-group">

                <label>
                    Exam
                </label>


                <select
                    name="exam_id[<?php echo $dept["dept_id"]; ?>]"
                    class="exam-select"
                    id="exam_<?php echo $dept["dept_id"]; ?>"
                    disabled
                    onchange="calculateTotal()"
                >

                    <option value="">
                        -- Select Exam --
                    </option>


                    <?php foreach ($exams as $exam) { ?>

                        <option
                            value="<?php echo $exam["exam_id"]; ?>"
                            data-date="<?php echo htmlspecialchars($exam["exam_date"]); ?>"
                            data-session="<?php echo htmlspecialchars($exam["session"]); ?>"
                            data-type="<?php echo htmlspecialchars($exam["exam_type"]); ?>"
                        >

                            <?php
                            echo htmlspecialchars($exam["subject"]);
                            ?>

                            |

                            <?php
                            echo htmlspecialchars($exam["exam_date"]);
                            ?>

                            |

                            <?php
                            echo htmlspecialchars($exam["session"]);
                            ?>

                            |

                            <?php
                            echo htmlspecialchars($exam["exam_type"]);
                            ?>

                        </option>

                    <?php } ?>

                </select>


                <button
                    type="button"
                    class="clear-btn"
                    onclick="clearExam(
                        <?php echo $dept["dept_id"]; ?>
                    )"
                >

                    Clear

                </button>

            </div>


            <!-- STUDENT COUNT -->

            <div class="form-group">

                <label>
                    Number of Students
                </label>


                <input
                    type="number"
                    name="student_count[<?php echo $dept["dept_id"]; ?>]"
                    id="count_<?php echo $dept["dept_id"]; ?>"
                    class="student-count"
                    min="1"
                    max="60"
                    value=""
                    disabled
                    oninput="calculateTotal()"
                >

            </div>


            <!-- SELECTED DEPARTMENT -->

            <input
                type="hidden"
                name="selected_departments[]"
                value="<?php echo $dept["dept_id"]; ?>"
                disabled
                id="selected_<?php echo $dept["dept_id"]; ?>"
            >

        </div>

    </div>

<?php } ?>


<!-- TOTAL -->

<div class="total-box">

    <span>
        Total Students
    </span>

    <span id="totalStudents">
        0
    </span>

</div>

</div>


<!-- --------------------------------
     RULES
--------------------------------- -->

<div class="card rules">

    <h3>📌 Seating Rules</h3>

    <ul>

        <li>
            Hall layout:
            <strong>6 rows × 10 columns</strong>
        </li>

        <li>
            Physical seats:
            <strong>60</strong>
        </li>

        <li>
            Physical benches:
            <strong>30</strong>
        </li>

        <li>
            <strong>Regular Exam:</strong>
            2 students per bench
        </li>

        <li>
            <strong>Regular Exam maximum:</strong>
            60 students
        </li>

        <li>
            <strong>Semester Exam:</strong>
            1 student per bench
        </li>

        <li>
            <strong>Semester Exam maximum:</strong>
            20 students
        </li>

        <li>
            Regular Exam must have an
            <strong>even number of students</strong>
        </li>

        <li>
            Students from the same department cannot sit immediately
            adjacent horizontally or vertically.
        </li>

        <li>
            For Regular Exams, students sharing the same bench must
            belong to different departments.
        </li>

        <li>
            All selected exams must have the same
            <strong>date, session and exam type</strong>.
        </li>

        <li>
            The <strong>same exam</strong> can be assigned to multiple departments.
        </li>

        <li>
            Different exam records can also have the same subject.
        </li>

    </ul>

</div>


<!-- SUBMIT -->

<button
    type="submit"
    class="allocate-btn"
>
    🪑 Allocate Seats
</button>


</form>

</div>


<script>


/* --------------------------------
   TOGGLE DEPARTMENT
--------------------------------- */

function toggleDepartment(deptId, button) {

    const content =
        document.getElementById("content_" + deptId);

    const exam =
        document.getElementById("exam_" + deptId);

    const count =
        document.getElementById("count_" + deptId);

    const selected =
        document.getElementById("selected_" + deptId);


    if (content.classList.contains("show")) {

        content.classList.remove("show");

        exam.disabled = true;
        count.disabled = true;
        selected.disabled = true;

        exam.value = "";
        count.value = "";

        button.classList.remove("active");

        button.innerText =
            "Select Department";

    } else {

        content.classList.add("show");

        exam.disabled = false;
        count.disabled = false;
        selected.disabled = false;

        button.classList.add("active");

        button.innerText =
            "Selected";
    }

    calculateTotal();
}


/* --------------------------------
   CLEAR EXAM
--------------------------------- */

function clearExam(deptId) {

    const exam =
        document.getElementById("exam_" + deptId);

    const count =
        document.getElementById("count_" + deptId);

    exam.value = "";

    count.value = "";

    calculateTotal();
}


/* --------------------------------
   ROOM INFO
--------------------------------- */

function updateRoomInfo() {

    const select =
        document.getElementById("room_id");

    const info =
        document.getElementById("roomInfo");


    if (!select.value) {

        info.style.display = "none";
        info.innerHTML = "";

        return;
    }


    const option =
        select.options[select.selectedIndex];


    const capacity =
        option.getAttribute("data-capacity");


    info.innerHTML =
        "Hall Capacity: <strong>"
        + capacity
        + "</strong> | "
        + "System seating capacity: <strong>60</strong>";


    info.style.display = "block";
}


/* --------------------------------
   TOTAL STUDENTS
--------------------------------- */

function calculateTotal() {

    let total = 0;


    const counts =
        document.querySelectorAll(".student-count");


    counts.forEach(function(input) {

        if (
            !input.disabled &&
            input.value !== ""
        ) {

            total +=
                parseInt(input.value) || 0;
        }

    });


    document.getElementById(
        "totalStudents"
    ).innerText = total;


    return total;
}


/* --------------------------------
   GET SELECTED EXAM DATA
--------------------------------- */

function getSelectedExamData() {

    const exams =
        document.querySelectorAll(".exam-select");


    const selected = [];


    exams.forEach(function(select) {

        if (
            !select.disabled &&
            select.value !== ""
        ) {

            const option =
                select.options[
                    select.selectedIndex
                ];


            selected.push({

                examId: select.value,

                date:
                    option.getAttribute(
                        "data-date"
                    ),

                session:
                    option.getAttribute(
                        "data-session"
                    ),

                type:
                    option.getAttribute(
                        "data-type"
                    )

            });
        }

    });


    return selected;
}


/* --------------------------------
   SAME EXAM IS ALLOWED
--------------------------------- */

function checkDuplicateExams() {

    return true;
}


/* --------------------------------
   CHECK EXAM COMPATIBILITY
--------------------------------- */

function checkExamCompatibility() {

    const selected =
        getSelectedExamData();


    if (selected.length === 0) {

        alert(
            "Please select at least one department and exam."
        );

        return false;
    }


    const first =
        selected[0];


    for (
        let i = 1;
        i < selected.length;
        i++
    ) {

        if (
            selected[i].date !==
            first.date
        ) {

            alert(
                "All selected exams must have the same exam date."
            );

            return false;
        }


        if (
            selected[i].session !==
            first.session
        ) {

            alert(
                "All selected exams must have the same session (FN/AN)."
            );

            return false;
        }


        if (
            selected[i].type !==
            first.type
        ) {

            alert(
                "All selected exams must have the same exam type."
            );

            return false;
        }

    }


    return true;
}


/* --------------------------------
   CHECK STUDENT COUNTS
--------------------------------- */

function checkStudentCounts() {

    const selected =
        getSelectedExamData();


    if (selected.length === 0) {
        return false;
    }


    const examType =
        selected[0].type;


    const total =
        calculateTotal();


    if (total <= 0) {

        alert(
            "Please enter the number of students."
        );

        return false;
    }


    if (
        examType === "REGULAR"
    ) {

        if (total > 60) {

            alert(
                "Regular Exam allows a maximum of 60 students."
            );

            return false;
        }


        if (total % 2 !== 0) {

            alert(
                "Regular Exam must have an even number of students."
            );

            return false;
        }

    }


    if (
        examType === "SEMESTER"
    ) {

        if (total > 20) {

            alert(
                "Semester Exam allows a maximum of 20 students."
            );

            return false;
        }

    }


    return true;
}


/* --------------------------------
   FORM SUBMIT
--------------------------------- */

document
    .getElementById("allocationForm")
    .addEventListener(
        "submit",
        function(event) {

            event.preventDefault();


            const room =
                document.getElementById(
                    "room_id"
                ).value;


            if (!room) {

                alert(
                    "Please select an exam hall."
                );

                return;
            }


            if (!checkDuplicateExams()) {
                return;
            }


            if (!checkExamCompatibility()) {
                return;
            }


            if (!checkStudentCounts()) {
                return;
            }


            const selectedDepartments =
                document.querySelectorAll(
                    ".department-content.show"
                );


            if (
                selectedDepartments.length === 0
            ) {

                alert(
                    "Please select at least one department."
                );

                return;
            }


            if (
                confirm(
                    "Are you sure you want to allocate seats?"
                )
            ) {

                this.submit();
            }

        }
    );

</script>

</body>

</html>