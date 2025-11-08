<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require config and composer autoload
require 'config.php';
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set error log file
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error.log");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $location = $_POST['location'];
    $travel_date = $_POST['travel_date'];
    $description = $_POST['description'];
    $photo = $_FILES['photo'];

    $bucket = $_ENV['S3_BUCKET_NAME'];
    $key = 'uploads/' . basename($photo['name']);

    // Initialize S3 client
    $s3 = new S3Client([
        'region' => $_ENV['AWS_DEFAULT_REGION'],
        'version' => 'latest',
        'credentials' => [
            'key'    => $_ENV['AWS_KEY'],
            'secret' => $_ENV['AWS_SECRET'],
        ]
    ]);

    try {
        // Upload file to S3 without ACL
        $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $key,
            'SourceFile' => $photo['tmp_name']
        ]);

        $photo_url = "https://{$bucket}.s3.{$_ENV['AWS_DEFAULT_REGION']}.amazonaws.com/{$key}";

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO memories (title, location, travel_date, description, photo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $location, $travel_date, $description, $photo_url);
        $stmt->execute();

        echo "✅ Memory uploaded successfully! <a href='index.php'>Go back</a>";

    } catch (AwsException $e) {
        // Log AWS errors to error.log
        error_log("AWS S3 Error: " . $e->getMessage());
        echo "❌ AWS S3 Error occurred. Check error.log for details.";
    } catch (Exception $e) {
        // Log other PHP errors to error.log
        error_log("PHP Error: " . $e->getMessage());
        echo "❌ An unexpected error occurred. Check error.log for details.";
    }
}
?>
