<?php
include ('include/navigation.php');
require('../db/db_connection.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to access this page.";
    header("Location: ../index.php");
    exit;
}

$user_query = "SELECT role, name FROM user WHERE id = ?";
$stmt = $conn->prepare($user_query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($role, $judge_name);
$stmt->fetch();
$stmt->close();

if ($role != 1) {
    $_SESSION['error_message'] = "You do not have permission to score.";
    exit;
}

if (!isset($_GET['entry_num'])) {
    echo "No contestant selected.";
    exit;
}

$entry_num = $_GET['entry_num'];

$checkJudgeScoreSql = "SELECT * FROM scores WHERE entry_num = ? AND judge_name = ?";
$stmt = $conn->prepare($checkJudgeScoreSql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("is", $entry_num, $judge_name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "<script>
            window.onload = function() {
                alert('You have already scored this contestant.');
                window.location.href = 'judgeTable.php';
            };
          </script>";
    $stmt->close();
    exit;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Judging Form</title>
    <link rel="stylesheet" href="../float/css/judgeTable.css">
</head>
<body>
    <div class="container">
        <h1>CIVIC PARADE: FLOAT COMPETITION</h1>
        <h1>Welcome, <?php echo htmlspecialchars($judge_name); ?></h1>
        <form action="submit_scores.php" method="post" onsubmit="return confirmSubmission()">
            <input type="hidden" id="entry_num" name="entry_num" value="<?php echo htmlspecialchars($entry_num); ?>">
            <div class="form-group">
                <label for="overall_appearance">Overall Appearance and Impact:</label>
                <input id="overall_appearance" name="overall_appearance" type="number" min="1" max="30" required>
            </div>
            <div class="form-group">
                <label for="artistry_design">Artistry/Design:</label>
                <input id="artistry_design" name="artistry_design" type="number" min="1" max="20" required>
            </div>
            <div class="form-group">
                <label for="craftsmanship">Craftsmanship:</label>
                <input id="craftsmanship" name="craftsmanship" type="number" min="1" max="30" required>
            </div>
            <div class="form-group">
                <label for="relevance_theme">Relevance to Festival Theme:</label>
                <input id="relevance_theme" name="relevance_theme" type="number" min="1" max="20" required>
            </div>
            <div class="buttons">
                <button type="submit">Submit</button>
            </div>
        </form>
        <div id="message" style="display:none;"></div>
    </div>

    <script>
        function confirmSubmission() {
            return confirm('Are you sure you want to submit?');
        }
    </script>
    <script src="../float/js/judgeTable.js"></script>
</body>
</html>
