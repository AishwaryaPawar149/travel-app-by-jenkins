<?php
// Show all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if all fields are set
    if (!isset($_POST['title'], $_POST['location'], $_POST['travel_date'], $_POST['description'], $_FILES['photo'])) {
        die("❌ Error: Form data not complete.");
    }

    $title = $_POST['title'];
    $location = $_POST['location'];
    $travel_date = $_POST['travel_date'];
    $description = $_POST['description'];
    $photo = $_FILES['photo'];

    // Check file upload
    if ($photo['error'] !== 0) {
        die("❌ File upload error: " . $photo['error']);
    }

    $bucket = $_ENV['S3_BUCKET_NAME'];
    $key = 'uploads/' . basename($photo['name']);

    // Initialize S3 client
    try {
        $s3 = new S3Client([
            'region' => $_ENV['AWS_DEFAULT_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key' => $_ENV['AWS_KEY'],
                'secret' => $_ENV['AWS_SECRET'],
            ]
        ]);
    } catch (Exception $e) {
        die("❌ AWS Client Initialization Error: " . $e->getMessage());
    }

    try {
        // Upload file to S3
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $photo['tmp_name'],
            'ACL' => 'public-read',
        ]);

        // Construct public URL
        $photo_url = "https://{$bucket}.s3.{$_ENV['AWS_DEFAULT_REGION']}.amazonaws.com/{$key}";

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO memories (title, location, travel_date, description, photo_url) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("❌ Database prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $title, $location, $travel_date, $description, $photo_url);

        if (!$stmt->execute()) {
            die("❌ Database execute failed: " . $stmt->error);
        }

        echo "✅ Memory uploaded successfully! <a href='index.php'>Go back</a>";

    } catch (AwsException $e) {
        die("❌ AWS S3 Error: " . $e->getMessage());
    } catch (Exception $e) {
        die("❌ General Error: " . $e->getMessage());
    }
} else {
    die("❌ Invalid request method.");
}
?>
