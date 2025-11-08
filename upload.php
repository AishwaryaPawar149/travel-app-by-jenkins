<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoload
require __DIR__ . '/vendor/autoload.php';

// Include database config
require __DIR__ . '/config.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    error_log("Dotenv Error: " . $e->getMessage());
    die("❌ Environment configuration error. Check your .env file.");
}

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $location = $_POST['location'] ?? '';
    $travel_date = $_POST['travel_date'] ?? '';
    $description = $_POST['description'] ?? '';
    $photo = $_FILES['photo'] ?? null;

    if (!$photo || $photo['error'] !== UPLOAD_ERR_OK) {
        die("❌ Error uploading file. Please try again.");
    }

    $bucket = $_ENV['S3_BUCKET_NAME'];
    $key = 'uploads/' . basename($photo['name']);

    // Initialize S3 client
    $s3 = new S3Client([
        'region' => $_ENV['AWS_DEFAULT_REGION'],
        'version' => 'latest',
        'credentials' => [
            'key' => $_ENV['AWS_KEY'],
            'secret' => $_ENV['AWS_SECRET'],
        ],
    ]);

    try {
        // Upload file to S3 **without ACL** to avoid AccessControlListNotSupported error
        $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $photo['tmp_name'],
        ]);

        $photo_url = "https://{$bucket}.s3.{$_ENV['AWS_DEFAULT_REGION']}.amazonaws.com/{$key}";

        // Save to database
        $stmt = $conn->prepare("INSERT INTO memories (title, location, travel_date, description, photo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $location, $travel_date, $description, $photo_url);
        $stmt->execute();

        echo "✅ Memory uploaded successfully! <a href='index.php'>Go back</a>";
    } catch (AwsException $e) {
        error_log("AWS S3 Error: " . $e->getMessage());
        die("❌ AWS S3 Error occurred. Check the error log.");
    } catch (Exception $e) {
        error_log("PHP Error: " . $e->getMessage());
        die("❌ An unexpected error occurred. Check the error log.");
    }
}
?>
