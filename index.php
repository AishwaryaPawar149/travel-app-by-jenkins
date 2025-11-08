<?php
require 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Travel Memories ✈️</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        header { text-align: center; color: white; margin-bottom: 40px; }
        header h1 { font-size: 3rem; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        header p { font-size: 1.2rem; opacity: 0.9; }
        .form-container {
            background: white; border-radius: 20px; padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin-bottom: 40px;
        }
        .form-container h2 { color: #667eea; margin-bottom: 30px; font-size: 2rem; }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 1rem; }
        input, textarea {
            width: 100%; padding: 15px; border: 2px solid #e0e0e0;
            border-radius: 10px; font-size: 1rem; transition: all 0.3s;
        }
        input:focus, textarea:focus {
            outline: none; border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        textarea { resize: vertical; min-height: 120px; }
        .file-input-label {
            display: flex; align-items: center; justify-content: center;
            padding: 15px; background: #f8f9fa;
            border: 2px dashed #667eea; border-radius: 10px;
            cursor: pointer; transition: all 0.3s;
        }
        .file-input-label:hover { background: #667eea; color: white; }
        .file-name { margin-top: 10px; color: #666; font-size: 0.9rem; }
        button {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 10px;
            font-size: 1.1rem; font-weight: 600; cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
        .memories-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px; margin-top: 40px;
        }
        .memory-card {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .memory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .memory-image { width: 100%; height: 250px; object-fit: cover; }
        .memory-content { padding: 20px; }
        .memory-title { font-size: 1.5rem; color: #333; margin-bottom: 10px; }
        .memory-location { color: #667eea; font-weight: 600; margin-bottom: 10px; }
        .memory-date { color: #999; font-size: 0.9rem; margin-bottom: 15px; }
        .memory-description { color: #666; line-height: 1.6; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>✈️ My Travel Memories</h1>
            <p>Capture and cherish your adventures forever</p>
        </header>

        <div class="form-container">
            <h2>📝 Add New Memory</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Memory Title *</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Amazing Sunset at Goa Beach">
                </div>

                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" required placeholder="e.g., Goa, India">
                </div>

                <div class="form-group">
                    <label for="travel_date">Travel Date *</label>
                    <input type="date" id="travel_date" name="travel_date" required>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required placeholder="Share your experience..."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Photo *</label>
                    <label for="photo" class="file-input-label">📷 Click to select image</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required>
                </div>

                <button type="submit">🚀 Save Memory</button>
            </form>
        </div>

        <div class="memories-grid">
            <?php
            $result = $conn->query("SELECT * FROM memories ORDER BY id DESC");
            while ($row = $result->fetch_assoc()) {
                echo "<div class='memory-card'>";
                echo "<img src='{$row['photo_url']}' class='memory-image'>";
                echo "<div class='memory-content'>";
                echo "<h3 class='memory-title'>{$row['title']}</h3>";
                echo "<p class='memory-location'>📍 {$row['location']}</p>";
                echo "<p class='memory-date'>📅 {$row['travel_date']}</p>";
                echo "<p class='memory-description'>{$row['description']}</p>";
                echo "</div></div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
