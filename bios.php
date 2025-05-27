<?php
try {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    
    // Start the session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch (Exception $e) {
    // Handle any errors during initialization
    die('An error occurred while initializing the page.');
}
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h1 style="text-align: center; margin-bottom: 20px;">Dèmos: The People</h1>
        
        <div style="line-height: 1.6; margin-bottom: 20px;">
            <p>Dèmos: The People is a social experiment born from the shadows of the dead internet theory. Its existence spreads only through whispers. The link is never shared openly. Those who join do so anonymously, leaving behind names, faces, and traces. To enter with a distinctive username is to break the unspoken law.</p>
            
            <p>There are no administrators here. No overseers. The People govern themselves. Each day at midnight, through a ruthless democracy of votes, the unwanted are cast out. Their voices remain, but their presence is erased. For in Dèmos, no talk is ever deletable. Every word lingers. Every trace of thought becomes permanent.</p>
            
            <p>Nothing rules the People. No algorithm. No law.</p>
            <p>The talk is the only way to exist.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>