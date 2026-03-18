

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">

<div class="navbar">

<div class="logo">
<h3>Stage System</h3>
</div>

<div class="nav-links">
<a href="dashboard.php">Dashboard</a>

<a href="tasks.php">Tasks</a>
<a href="profile.php">Profile</a>

<div class="notif-dropdown">
<a href="#">🔔</a>
<div class="notif-box">
<ul>
<?php
include "../includes/db.php";
$user_id = $_SESSION['user_id'];
$notif = $conn->query("SELECT * FROM notifications WHERE user_id='$user_id' ORDER BY id DESC");

if($notif->num_rows>0){
    while($n=$notif->fetch_assoc()){
        echo '<li>'.$n['message'].'</li>';
    }
}else{
    echo '<li>No Notifications</li>';
}
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id='$user_id'");
?>
</ul>
</div>
</div>

<a href="../logout.php">Logout</a>
</div>

</div>