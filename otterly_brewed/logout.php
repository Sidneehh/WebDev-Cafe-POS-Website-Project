<?php
session_start();
session_destroy();
header('Location: otter_homepage.html');
exit();
?>