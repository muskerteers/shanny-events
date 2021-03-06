<?php
session_start();

require_once '../backend/auth.php';

$logged_user = new Auth();

if (!$logged_user->is_logged_in()) {
    $logged_user->redirect('../index.php');
} else {
    if ($_SESSION['user_type'] == 1) {
        $logged_user->redirect('../admin/admin_page.php');
    }
}

$active_page = 'events';
$form_active = $edit_profile = $edit_password = $delete_account = $add_amount_form = false;
$event_name = $event_location = $event_date = $event_people = $event_costs = $delete_error = $event_id = $eventId = "";
$event_form_error = $form_submitted = $delete_event_error = $event_date_edit = $success_message = $event_form_succ = "";
$notification_count = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['page']) && $_POST['page'] === 'events') {
        $active_page = 'events';
    } elseif (isset($_POST['page']) && $_POST['page'] === 'profile') {
        $active_page = 'profile';
    } elseif (isset($_POST['page']) && $_POST['page'] === 'notification') {
        $active_page = 'notification';
    } elseif (isset($_POST["form_active"])) {
        $form_active = true;
    } elseif (isset($_POST['event_form'])) { //save new details
        if (
            empty(trim($_POST['event_name'])) &&
            empty(trim($_POST['event_location'])) &&
            empty(trim($_POST['event_date'])) &&
            empty(trim($_POST['event_people'])) &&
            empty(trim($_POST['event_costs']))
        ) {
            $form_active = true;

            $event_form_error = "Please fill every field correctly";
        } else {
            $form_active = true;

            $event_name = trim($_POST['event_name']);
            $event_location = trim($_POST['event_location']);
            $event_date = trim($_POST['event_date']);
            $event_people = trim($_POST['event_people']);
            $event_costs = trim($_POST['event_costs']);

            //check if its edit or new event submitted
            if (trim($_POST['form_submitted']) == "form_new") {

                if ($logged_user->addRequestEvent($event_name, $event_location, $event_date, $event_people, $event_costs)) {
                    $logged_user->redirect('client_page.php');
                    $event_form_succ = "Event request successfully made...await notifications";
                    $form_active = false;
                } else {
                    $form_error = "Something went wrong. Please try again later.";
                    $form_active = true;
                }
            } elseif (trim($_POST['form_submitted']) == "form_edit") {
                $eventId = $_POST['eventId'];

                if ($logged_user->editRequestEvent($eventId, $event_name, $event_location, $event_date, $event_people, $event_costs)) {
                    $logged_user->redirect('client_page.php');
                    $event_form_succ = "Event edit request successfully made...await notifications";
                    $form_active = false;
                } else {
                    $event_form_error = "Something went wrong. Please try again later.";
                    $form_active = false;
                }
            }


        }
    } elseif (isset($_POST['event_delete'])) { //soft delete existing details
        if ($logged_user->deleteEvent($_POST['event_id'])) {
            $logged_user->redirect('client_page.php');
        } else {
            $delete_event_error = "Something went wrong. Please try again later.";
        }

    } elseif (isset($_POST['event_edit'])) { // edit existing details
        $form_active = true;
        $event_id = $_POST['event_id'];

        if ($details = $logged_user->showEditRequestDetails($_POST['event_id'])) {
            $form_submitted = "edit";

            $event_id = $details['id'];
            $event_name = $details['name'];
            $event_location = $details['location'];
            $event_date = $details['date'];
            $event_people = $details['people_count'];
            $event_costs = $details['total_cost'];
        }

    } elseif (isset($_POST['show_edit_profile'])) { // show edit profile page
        $edit_profile = true;
        $active_page = "profile";

    } elseif (isset($_POST['show_edit_password'])) { // show edit password page
        $edit_password = true;
        $active_page = "profile";

    } elseif (isset($_POST['show_delete_account'])) { // show delete account page
        $delete_account = true;
        $active_page = "profile";

    } elseif (isset($_POST['cancel_delete_account'])) { // cancel delete account page
        $delete_account = false;
        $active_page = "profile";

    } elseif (isset($_POST['edit_profile_details'])) { // edit profile
        $user_id = $_POST['user_id'];
        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $mail = $_POST['email'];
        $phone = $_POST['phone'];

        if ($logged_user->editClientInformation($user_id, $fname, $lname, $mail, $phone)) {
            $edit_profile = false;
            $active_page = "profile";
            $success_message = "Profile Details updates successfully";

        } else {
            $edit_profile = true;
            $active_page = "profile";
            $delete_error = "Something went wrong. Please try again later.";
        }
    } elseif (isset($_POST['edit_profile_password'])) { //edit password
        $user_id = $_POST['user_id'];
        $p_pass = $_POST['password_previous'];
        $n_pass = $_POST['password_new'];
        $n_pass_conf = $_POST['password_new_confirm'];


        if (strlen(trim($n_pass)) < 6 && strlen(trim($n_pass_conf)) < 6 && strlen(trim($p_pass)) < 6) {
            $edit_password = true;
            $active_page = "profile";
            $delete_error = "The passwords provided are less than 6 characters.";

        } elseif (empty($delete_error) && ($n_pass !== $n_pass_conf)) {
            $edit_password = true;
            $active_page = "profile";
            $delete_error = "The new passwords don't match, try again.";

        } else {

            $new_pass = password_hash($n_pass, PASSWORD_DEFAULT);

            if ($logged_user->editUserPassword($user_id, $p_pass, $new_pass)) {
                $active_page = "profile";
                $edit_password = false;
                $success_message = "Profile Password updates successfully";
            } else {
                $active_page = "profile";
                $edit_password = true;
                $delete_error = "Wrong Previous Password. Please try again later.";
            }

        }
    } elseif (isset($_POST['delete_account'])) { // delete account

        if ($logged_user->deleteAccount($_POST['user_id'])) {
            $_SESSION = array();
            session_destroy();

            $logged_user->redirect('../index.php');

        } else {
            $delete_error = "Something went wrong. Please try again later.";
        }
    }elseif (isset($_POST['notification_event'])){
        $action = $_POST['notification_event'];
        $active_page = 'notification';

        if ($action == 'view_add_amount'){
            $add_amount_form = true;

            $notification_id = $_POST['notification_id'];
            $eventDetails = $logged_user->viewEventDetails($_POST['event_id']);
            $event_id = $eventDetails['id'];
            $event_name = $eventDetails['name'];
            $event_cost = $eventDetails['total_cost'];
            $event_balance = $eventDetails['total_bal'];

        }elseif ($action == 'add_amount') {
            $p_cost = (int)$_POST['event_cost'];
            $amount = (int)$_POST['amount'];

            $n_cost = ($p_cost + $amount);

            if ($logged_user->updateEventAmount($n_cost)){
                $add_amount_form = false;
                $success_message = "Amount Updated";
            }else{
                $add_amount_form = true;
                $success_message = "Error occurred on update";
            }
        }elseif ($action == 'mark_as_read') {
            if ($logged_user->updateNotification($_POST['notification_id'])){
                $add_amount_form = false;
                $success_message = "Notification Read";
            }else{
                $add_amount_form = true;
                $success_message = "Notification not read";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>
        Client Page
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

        <?php if ((isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) && $_SESSION['user_type'] == 2)
            echo '<a href="../client/client_page.php">Client Dash</a>'
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
                <form action="client_page.php" method="post">
                    <input type="hidden" name="page" value="events">

                    <button class="btn btn-menu-side" type="submit">My Events</button>
                </form>
                <br>
                <br>
                <form action="client_page.php" method="post">
                    <input type="hidden" name="page" value="profile">

                    <button class="btn btn-menu-side" type="submit">My Profile</button>
                </form>
                <br>
                <br>
                <form action="client_page.php" method="post">
                    <input type="hidden" name="page" value="notification">

                    <button class="btn btn-menu-side" type="submit">
                        Notification <?php if ($logged_user->countUserNotifications($_SESSION['id']) > 0) {
                            echo $logged_user->countUserNotifications($_SESSION['id']);
                        } else {
                            echo '';
                        } ?></button>
                </form>
            </div>
        </div>

        <div class="main-content">
            <?php
            if ($active_page == 'events') {
                ?>
                <h2>Events Section</h2>

                <span class="help-block text-center" style="color: red;"><?php echo $event_form_error; ?></span>
                <span class="help-block text-center" style="color: green;"><?php echo $event_form_succ; ?></span>

                <?php if (!$form_active) { ?>
                    <form action="client_page.php" method="post">
                        <input type="hidden" name="form_active" value="true">

                        <button class="btn btn-primary" type="submit">Request Event</button>
                    </form>
                <?php } ?>

                <br>

                <?php

                if ($form_active) {
                    include "event_form.php";
                }

                include 'events.php';

            } elseif ($active_page == 'profile') {
                include 'profile.php';
            } elseif ($active_page == 'notification') {
                include 'client_notification.php';
            }
            ?>

        </div>
    </div>
</div>
</body>
</html>


