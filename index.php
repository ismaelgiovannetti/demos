<?php
try {
    require_once 'includes/config.php';

    // Get selected date from URL or use today's date
    $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Build the query with date filter
    $query = '
        SELECT p.*, u.username
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE DATE(p.created_at) = :selected_date
        ORDER BY p.created_at DESC
    ';
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':selected_date', $selectedDate);

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error in index.php: ' . $e->getMessage());
    $posts = [];
    $_SESSION['message'] = 'Error loading posts. Please try again later.';
} catch (Exception $e) {
    error_log('Unexpected error in index.php: ' . $e->getMessage());
    $posts = [];
    $_SESSION['message'] = 'An unexpected error occurred.';
}
?>
<?php include 'includes/header.php'; ?>



<div style="text-align: center; margin-bottom: 20px;">
    <h1>Dèmos : The People</h1>
    <form id="dateForm" style="margin: 20px 0;">
        <input type="date" id="datePicker" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">
    </form>
</div>

<script>
// Auto-submit form when date changes
document.getElementById('datePicker').addEventListener('change', function() {
    document.getElementById('dateForm').submit();
});

// Set default date to today if no date is selected
if (!new URLSearchParams(window.location.search).has('date')) {
    const today = new Date().toISOString().split('T')[0];
    window.history.replaceState({}, '', `${window.location.pathname}?date=${today}`);
}
</script>

<?php if (empty($posts)): ?>
    <p style="text-align: center; color: red; margin: 10px 0; font-weight: bold;">Dèmos : So silent... <a href="post.php">Talk.</a></p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <div>
            <p>
                <strong><?php echo htmlspecialchars($post['username']); ?></strong> - 
                <?php echo date('M j, Y g:i a', strtotime($post['created_at'])); ?>
            </p>
            <p style="margin: 10px 0 20px 0; font-size: 1.1em;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <hr>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
