<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

include 'db.php'; 

$user_id = $_SESSION['user_id'];


$query = "SELECT id, class FROM students WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$student_id = $student['id'];
$class = $student['class'];


$grades_query = "
    SELECT subjects.name, grades.grade
    FROM grades
    JOIN subjects ON grades.subject_id = subjects.id
    WHERE grades.student_id = ?";
$stmt = $conn->prepare($grades_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$grades_result = $stmt->get_result();


$schedule_query = "SELECT day, time_slot, subjects.name as subject FROM schedule JOIN subjects ON schedule.subject_id = subjects.id WHERE class = ? ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')";
$stmt = $conn->prepare($schedule_query);
$stmt->bind_param("s", $class);
$stmt->execute();
$schedule_result = $stmt->get_result();


$bulletin_query = "SELECT pdf_path FROM bulletins WHERE student_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($bulletin_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$bulletin_result = $stmt->get_result();
$bulletin = $bulletin_result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EduGestion - Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f3f4f6;
      color: #111827;
      display: flex;
      height: 100vh;
    }
    .sidebar {
      width: 240px;
      background-color: #2563eb;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 32px 16px;
    }
    .sidebar h2 {
      font-size: 1.5rem;
      margin-bottom: 32px;
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 12px;
      border-radius: 6px;
      font-weight: 600;
      display: block;
      transition: background-color 0.3s ease;
    }
    .sidebar a:hover {
      background-color: #1e40af;
    }
    .main {
      flex: 1;
      padding: 32px;
      overflow-y: auto;
    }
    .card {
      background-color: white;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      margin-bottom: 24px;
    }
    .card h3 {
      margin-top: 0;
      font-size: 1.25rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #e5e7eb;
      text-align: left;
    }
    th {
      background-color: #f9fafb;
    }
    @media (max-width: 767px) {
      body {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        flex-direction: row;
        overflow-x: auto;
        white-space: nowrap;
      }
      .sidebar a {
        flex: 1;
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <nav class="sidebar">
    <h2>🎓 Student</h2>
    <a href="#notes">📘 My Grades</a>
    <a href="#schedule">🕒 Timetable</a>
    <a href="#bulletin">📄 My Bulletin</a>
    <a href="logout.php">🚪 Logout</a>
  </nav>

  <main class="main">
    <section class="card" id="notes">
      <h3>My Grades</h3>
      <table>
        <thead>
          <tr><th>Subject</th><th>Grade</th></tr>
        </thead>
        <tbody>
          <?php while($row = $grades_result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['name'])?></td>
            <td><?=htmlspecialchars($row['grade'])?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <section class="card" id="schedule">
      <h3>Timetable</h3>
      <table>
        <thead>
          <tr><th>Day</th><th>Time</th><th>Subject</th></tr>
        </thead>
        <tbody>
          <?php while($row = $schedule_result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['day'])?></td>
            <td><?=htmlspecialchars($row['time_slot'])?></td>
            <td><?=htmlspecialchars($row['subject'])?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <section class="card" id="bulletin">
      <h3>My Bulletin</h3>
      <?php if ($bulletin): ?>
      <p>You can download your bulletin below:</p>
      <a href="<?=htmlspecialchars($bulletin['pdf_path'])?>" download style="color: #2563eb; font-weight: 600;">📥 Download Bulletin (PDF)</a>
      <?php else: ?>
      <p>No bulletin available.</p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
