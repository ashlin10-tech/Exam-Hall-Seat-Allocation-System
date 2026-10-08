<?php

session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Add Exam</title>

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
        0 4px 15px rgba(69,10,10,0.25);
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
        0 8px 25px rgba(69,10,10,0.10);

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

/* FORM */

.form-group {
    margin-bottom: 22px;
}

label {
    display: block;

    margin-bottom: 8px;

    font-weight: bold;

    color: #450a0a;

    font-size: 14px;
}

input[type="text"],
input[type="date"] {
    width: 100%;

    padding: 13px 14px;

    border: 1px solid #e0caca;

    border-radius: 9px;

    background: #fffafa;

    font-size: 14px;
}

input:focus {
    outline: none;

    border-color: #991b1b;

    background: white;

    box-shadow:
        0 0 0 3px rgba(153,27,27,0.08);
}

/* EXAM TYPE */

.exam-types {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 12px;
}

.exam-type input {
    display: none;
}

.exam-type label {
    border: 2px solid #e5caca;

    border-radius: 10px;

    padding: 15px;

    text-align: center;

    cursor: pointer;

    background: #fffafa;

    transition: 0.3s;

    margin: 0;
}

.exam-type label strong {
    display: block;

    font-size: 15px;

    margin-bottom: 5px;
}

.exam-type label small {
    color: #777;

    font-size: 11px;
}

.exam-type input:checked + label {
    background: #fce7e7;

    border-color: #991b1b;

    color: #450a0a;

    box-shadow:
        0 0 0 3px rgba(153,27,27,0.08);
}

/* SESSION */

.session-buttons {
    display: flex;

    gap: 12px;
}

.session-buttons input {
    display: none;
}

.session-buttons label {
    flex: 1;

    text-align: center;

    padding: 13px;

    border: 2px solid #e5caca;

    border-radius: 9px;

    cursor: pointer;

    background: #fffafa;

    color: #7f1d1d;

    margin: 0;
}

.session-buttons input:checked + label {
    background: #fce7e7;

    border-color: #991b1b;

    color: #450a0a;
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
}

.submit-btn:hover {
    background:
        linear-gradient(
            135deg,
            #5f1111,
            #7f1d1d
        );
}

/* INFO */

.info-box {
    margin-top: 25px;

    background: #fffafa;

    border: 1px solid #f0dede;

    border-radius: 10px;

    padding: 15px;

    font-size: 12px;

    color: #777;

    line-height: 1.7;
}

.bottom-links {
    text-align: center;

    margin-top: 22px;
}

.back {
    text-decoration: none;

    color: #7f1d1d;

    font-size: 14px;

    font-weight: bold;
}

/* RESPONSIVE */

@media(max-width:600px) {

    .header {
        padding: 18px 20px;

        flex-direction: column;

        gap: 15px;

        align-items: stretch;
    }

    .dashboard-btn {
        text-align: center;
    }

    .form-card {
        padding: 25px 20px;
    }

    .exam-types {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<div class="header">

    <div class="header-left">

        <div class="header-icon">
            📝
        </div>

        <h2>
            Add Exam
        </h2>

    </div>

    <a
        href="admin_dashboard.php"
        class="dashboard-btn"
    >
        ← Dashboard
    </a>

</div>


<div class="main">

<div class="form-card">

<div class="title-section">

    <div class="title-icon">
        📝
    </div>

    <h1>
        Add Examination
    </h1>

    <p>
        Create a new examination
    </p>

</div>


<form
    method="POST"
    action="save_exam.php"
    onsubmit="return validateExam()"
>

<!-- SUBJECT -->

<div class="form-group">

<label>
    Subject
</label>

<input
    type="text"
    name="subject"
    placeholder="Enter subject name"
    required
>

</div>


<!-- DATE -->

<div class="form-group">

<label>
    Exam Date
</label>

<input
    type="date"
    name="exam_date"
    id="exam_date"
    required
>

</div>


<!-- EXAM TYPE -->

<div class="form-group">

<label>
    Exam Type
</label>

<div class="exam-types">

    <div class="exam-type">

        <input
            type="radio"
            name="exam_type"
            id="regular"
            value="REGULAR"
            checked
        >

        <label for="regular">

            <strong>
                Regular Exam
            </strong>

            <small>
                2 students per bench
            </small>

        </label>

    </div>


    <div class="exam-type">

        <input
            type="radio"
            name="exam_type"
            id="semester"
            value="SEMESTER"
        >

        <label for="semester">

            <strong>
                Semester Exam
            </strong>

            <small>
                1 student per bench
            </small>

        </label>

    </div>

</div>

</div>


<!-- SESSION -->

<div class="form-group">

<label>
    Session
</label>

<div class="session-buttons">

    <div>

        <input
            type="radio"
            name="session"
            id="fn"
            value="FN"
            checked
        >

        <label for="fn">
            FN
        </label>

    </div>


    <div>

        <input
            type="radio"
            name="session"
            id="an"
            value="AN"
        >

        <label for="an">
            AN
        </label>

    </div>

</div>

</div>


<button
    type="submit"
    class="submit-btn"
>
    📝 Add Exam
</button>

</form>


<div class="info-box">

<strong>Seating Rules</strong><br>

Regular Exam → 2 students per bench.
Students sharing a bench must belong to different departments.<br>

Semester Exam → 1 student per bench.
Maximum 15 students in a 30-seat hall.

</div>


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


<script>

const today =
    new Date().toISOString().split("T")[0];

document.getElementById("exam_date").min = today;

function validateExam() {

    const date =
        document.getElementById("exam_date").value;

    if (date < today) {

        alert("Exam date cannot be in the past.");

        return false;
    }

    return true;
}

</script>

</body>

</html>