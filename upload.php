<?php
require 'config.php';
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set error logging
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error.log");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $location = $_POST['location'];
    $travel_date = $_POST['travel_date'];
    $description = $_POST['description'];
    $photo = $_FILES['photo'];

    $bucket = 'travel-memory-bucket-by-aish';
    $key = 'uploads/' . basename($photo['name']);

    // Initialize S3 client using environment variables
    $s3 = new S3Client([
        'region' => 'ap-south-1',
        'version' => 'latest',
        'credentials' => [
            'key'    => $_ENV['AWS_KEY'],
            'secret' => $_ENV['AWS_SECRET'],
        ],
    ]);

    try {
        // Upload to S3
        $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $photo['tmp_name'],
            'ACL' => 'public-read', // remove if bucket does not support ACLs
        ]);

        $photo_url = "https://{$bucket}.s3.ap-south-1.amazonaws.com/{$key}";

        // Save to database
        $stmt = $conn->prepare("INSERT INTO memories (title, location, travel_date, description, photo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $location, $travel_date, $description, $photo_url);
        $stmt->execute();

        echo "✅ Memory uploaded successfully! <a href='index.php'>Go back</a>";
    } catch (AwsException $e) {
        // Log AWS errors
        error_log("AWS S3 Error: " . $e->getMessage());
        echo "❌ AWS S3 Error occurred. Please check the error log.";
    } catch (Exception $e) {
        // Log other PHP errors
        error_log("PHP Error: " . $e->getMessage());
        echo "❌ An error occurred. Please check the error log.";
    }
}
?>
