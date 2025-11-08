<?php
require 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Travel Memories ✈️</title>
    <link rel="stylesheet" href="style.css"> <!-- optional separate CSS -->
</head>
<body>
    <h1>My Travel Memories</h1>

    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Title" required><br>
        <input type="text" name="location" placeholder="Location" required><br>
        <input type="date" name="travel_date" required><br>
        <textarea name="description" placeholder="Description" required></textarea><br>
        <input type="file" name="photo" accept="image/*" required><br>
        <button type="submit">Upload Memory</button>
    </form>

    <hr>

    <h2>All Memories</h2>
    <?php
    $result = $conn->query("SELECT * FROM memories ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<div>";
        echo "<h3>{$row['title']} ({$row['location']})</h3>";
        echo "<img src='{$row['photo_url']}' width='300'><br>";
        echo "<p>{$row['description']}</p>";
        echo "<small>{$row['travel_date']}</small>";
        echo "</div><hr>";
    }
    ?>
</body>
</html>
