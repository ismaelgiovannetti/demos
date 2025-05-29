<?php
// Set the timezone to match your server's timezone
date_default_timezone_set('Europe/Paris');

// Log file setup
$logFile = dirname(__DIR__) . '/logs/daily_sentence.log';
$logDir = dirname($logFile);

// Create logs directory if it doesn't exist
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Function to write log messages
function logMessage($message, $level = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage; // Also output to console
}

logMessage("Starting daily sentence posting script");

// Path to the config file
$configPath = dirname(__DIR__) . '/includes/config.php';

if (!file_exists($configPath)) {
    $error = "Error: Could not find config file at $configPath";
    logMessage($error, 'ERROR');
    die($error . PHP_EOL);
}

logMessage("Loading config from $configPath");
require_once $configPath;

// Get the list of sentences
$sentencesPath = dirname(__DIR__) . '/includes/daily_sentences.php';
if (!file_exists($sentencesPath)) {
    $error = "Error: Could not find daily sentences file at $sentencesPath";
    logMessage($error, 'ERROR');
    die($error . PHP_EOL);
}

logMessage("Loading sentences from $sentencesPath");
$sentences = include $sentencesPath;
logMessage("Loaded " . count($sentences) . " sentences");

// Get the Dèmos user ID
logMessage("Fetching Dèmos user from database");
try {
    $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute(['Dèmos']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Error: Could not find user 'Dèmos' in the database";
        logMessage($error, 'ERROR');
        die($error . PHP_EOL);
    }
    logMessage("Found Dèmos user with ID: " . $user['id']);
} catch (Exception $e) {
    $error = "Database error while fetching user: " . $e->getMessage();
    logMessage($error, 'ERROR');
    die($error . PHP_EOL);
}

// Check if a post was already made today
$today = date('Y-m-d');
try {
    logMessage("Checking for existing posts for today ($today)");
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND DATE(created_at) = ?');
    $stmt->execute([$user['id'], $today]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        $message = "A post was already made today. Exiting.";
        logMessage($message);
        die($message . PHP_EOL);
    }
} catch (Exception $e) {
    $error = "Error checking for existing posts: " . $e->getMessage();
    logMessage($error, 'ERROR');
    die($error . PHP_EOL);
}

// Select a random sentence
$randomSentence = $sentences[array_rand($sentences)];
logMessage("Selected random sentence: \"$randomSentence\"");

try {
    logMessage("Starting database transaction");
    $db->beginTransaction();
    
    // Insert the post
    logMessage("Inserting new post into database");
    $stmt = $db->prepare('INSERT INTO posts (user_id, content, upvotes, downvotes) VALUES (?, ?, 0, 0)');
    $stmt->execute([$user['id'], $randomSentence]);
    $postId = $db->lastInsertId();
    
    $db->commit();
    logMessage("Successfully committed transaction. New post ID: $postId");
    
    $successMessage = "Successfully posted: \"$randomSentence\" as Dèmos (Post ID: $postId)";
    logMessage($successMessage);
    echo $successMessage . PHP_EOL;
    
} catch (Exception $e) {
    $error = "Error in database operation: " . $e->getMessage();
    logMessage($error, 'ERROR');
    
    if (isset($db) && $db->inTransaction()) {
        logMessage("Rolling back transaction due to error");
        $db->rollBack();
    }
    
    die($error . PHP_EOL);
}

logMessage("Script execution completed successfully");
