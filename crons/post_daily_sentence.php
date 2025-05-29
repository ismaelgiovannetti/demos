<?php
// Set the timezone to match your server's timezone
date_default_timezone_set('Europe/Paris');

// Path to the config file
$configPath = dirname(__DIR__) . '/includes/config.php';

if (!file_exists($configPath)) {
    die("Error: Could not find config file.\n");
}

require_once $configPath;

// Get the list of sentences
$sentencesPath = dirname(__DIR__) . '/includes/daily_sentences.php';
if (!file_exists($sentencesPath)) {
    die("Error: Could not find daily sentences file.\n");
}

$sentences = include $sentencesPath;

// Get the Dèmos user ID
$stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute(['Dèmos']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: Could not find user 'Dèmos'.\n");
}

// Check if a post was already made today
$today = date('Y-m-d');
$stmt = $db->prepare('SELECT COUNT(*) as count FROM posts WHERE user_id = ? AND DATE(created_at) = ?');
$stmt->execute([$user['id'], $today]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] > 0) {
    die("A post was already made today.\n");
}

// Select a random sentence
$randomSentence = $sentences[array_rand($sentences)];

try {
    $db->beginTransaction();
    
    // Insert the post
    $stmt = $db->prepare('INSERT INTO posts (user_id, content, upvotes, downvotes) VALUES (?, ?, 0, 0)');
    $stmt->execute([$user['id'], $randomSentence]);
    
    $db->commit();
    
    echo "Successfully posted: \"$randomSentence\" as Dèmos\n";
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    die("Error: " . $e->getMessage() . "\n");
}
