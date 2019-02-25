<?php
session_start();

require_once "../backend/connect.php";

// Check if the user is logged in;
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../auth/login.php");
    exit;
}

$active_page = 'events';
$latest_action = true;
$status_id = 3;
$ongoing_action = $completed_action = $view_event = false;
$event_action_error = $event_action_success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // change active pages
    if (isset($_POST['page'])) {
        $page = $_POST['page'];

        if ($page == 'events') {
            $active_page = 'events';
        } elseif ($page == 'clients') {
            $active_page = 'clients';
        } elseif ($page == 'profile') {
            $active_page = 'profile';
        }
    }

    // admin page actions
    if (isset($_POST['admin_page_action'])) {
        $action = $_POST['admin_page_action'];

        if ($action == "latest") {
            $completed_action = $ongoing_action = false;
            $latest_action = true;
            $status_id = 3;
        } elseif ($action == "ongoing") {
            $latest_action = $completed_action = false;
            $ongoing_action = true;
            $status_id = 2;
        } elseif ($action == "completed") {
            $latest_action = $ongoing_action = false;
            $completed_action = true;
            $status_id = 1;
        }
    }

    // view event
    if (isset($_POST['view_event'])) {
        $active_page = 'events';
        $view_event = true;
        $event_id = $_POST['event_id'];
        $status_id = $_POST['status_id'];

        if ($status_id == 3) {
            $completed_action = $ongoing_action = false;
            $latest_action = true;
        } elseif ($status_id == 2) {
            $latest_action = $completed_action = false;
            $ongoing_action = true;
        } elseif ($status_id == 1) {
            $latest_action = $ongoing_action = false;
            $completed_action = true;
        }
    }

    // view_event_action
    if (isset($_POST['view_event_action'])) {
        $action = $_POST['view_event_action'];
        $event_id = $_POST['event_id'];
        $active_page = 'events';

        if ($action == 'accept') {
            $sql = "UPDATE events SET status = 2 WHERE id = '$event_id'";

            if (mysqli_query($conn, $sql)) {
                $active_page = 'events';
                $completed_action = $ongoing_action = false;
                $latest_action = true;
                $event_action_success = "Event status successfully upgraded";
            } else {
                $active_page = 'events';
                $completed_action = $ongoing_action = false;
                $latest_action = true;
                $event_action_error = "Something went wrong. Please try again later.";
            }
        } elseif ($action == 'reject') {
            $sql = "UPDATE events SET status = 4 WHERE id = '$event_id'";

            if (mysqli_query($conn, $sql)) {
                $active_page = 'events';
                $completed_action = $ongoing_action = false;
                $latest_action = true;
                $event_action_success = "Event status successfully rejected";
            } else {
                $active_page = 'events';
                $completed_action = $ongoing_action = false;
                $latest_action = true;
                $event_action_error = "Something went wrong. Please try again later.";
            }
        } elseif ($action == 'done') {
            $sql = "UPDATE events SET status = 1 WHERE id = '$event_id'";

            if (mysqli_query($conn, $sql)) {
                $active_page = 'events';
                $latest_action = $completed_action = false;
                $ongoing_action = true;
                $event_action_success = "Event successfully marked done";
            } else {
                $active_page = 'events';
                $latest_action = $completed_action = false;
                $ongoing_action = true;
                $event_action_error = "Something went wrong. Please try again later.";
            }
        } elseif ($action == 'view_sub_task'){
            // view all subtask
        }
    }


}

?>

<!DOCTYPE html>
<html>
<head>
    <title>
        Admin Page
    </title>

    <link rel="stylesheet" href="../styles/main.css">
</head>
<body>
<div class="topnav">
    <div class="container">
        <a href="../index.php">Home</a>

        <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
            echo '<a href="../auth/login.php">Login</a>'
        ?>

        <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
            echo '<a href="../auth/register.php">Register</a>'
        ?>

        <?php if ((isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) && $_SESSION['user_type'] == 1)
            echo '<a href="../admin/admin_page.php">Admin Dash</a>'
        ?>

        <?php if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true)
            echo $_SESSION["name"];
        ?>

        <?php if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true)
            echo '<a href="../auth/logout.php">Logout</a>'
        ?>
    </div>
</div>

<div class="container">
    <div class="dash-body">
        <div class="side-menu">
            <div id="mySidenav" class="sidenav">
                <form action="admin_page.php" method="post">
                    <input type="hidden" name="page" value="events">

                    <button class="btn btn-menu-side" type="submit">Events</button>
                </form>
                <br>
                <br>
                <form action="admin_page.php" method="post">
                    <input type="hidden" name="page" value="clients">

                    <button class="btn btn-menu-side" type="submit">Clients</button>
                </form>
                <br>
                <br>
                <form action="admin_page.php" method="post">
                    <input type="hidden" name="page" value="profile">

                    <button class="btn btn-menu-side" type="submit">My Profile</button>
                </form>
            </div>
        </div>

        <div class="main-content">
            <?php if ($active_page == 'events') { ?>
                <?php include "events.php"; ?>
            <?php } elseif ($active_page == 'clients') { ?>
                <h2>Clients Section</h2>
            <?php } elseif ($active_page == 'profile') { ?>
                <h2>Profile Page</h2>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>
