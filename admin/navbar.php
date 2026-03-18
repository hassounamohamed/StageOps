<!--- navbar.php --->
<nav class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="tasks.php">Tasks</a>
    <a href="profile.php">Profile</a>
    <div class="notification">
        <i class="bi bi-bell-fill"></i>
        <span class="badge" id="notif_count">0</span>
    </div>
    <a href="../logout.php">Logout</a>
</nav>

<!-- Add this JavaScript before closing body tag or at the end of navbar -->
<script>
// Check notifications every 5 seconds
function checkNotifications() {
    fetch('notifications.php')
    .then(response => response.text())
    .then(data => {
        const notifBadge = document.getElementById('notif_count');
        if(notifBadge) {
            notifBadge.textContent = data;
            
            // Optional: Add class if has notifications
            if(parseInt(data) > 0) {
                notifBadge.classList.add('has-notif');
            } else {
                notifBadge.classList.remove('has-notif');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Check immediately when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkNotifications();
    // Then check every 5 seconds
    setInterval(checkNotifications, 5000);
});
</script>

<footer class="footer">
    <p>© 2026 Stage Management</p>
</footer>