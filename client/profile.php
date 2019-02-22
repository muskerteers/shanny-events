<?php

session_start();

require_once "../backend/connect.php";

$user_id = $_SESSION['id'];

$sql = "SELECT * FROM users WHERE id = '$user_id'";

$data = mysqli_query($conn, $sql);

$details = mysqli_fetch_row($data);

$firstname = $details[1];
$lastname = $details[2];
$email = $details[3];
$phone = $details[4];
$password = $details[5];
?>

<div class="profile-page">
    <h1 class="profile-title">Profile</h1>

    <h4 class="profile-sub-title">Account Details</h4>

    <div>
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td>Full Name</td>
                <td><?php echo $firstname . '  ' . $lastname ?></td>
            </tr>

            <tr>
                <td>Email</td>
                <td><?php echo $email ?></td>
            </tr>

            <tr>
                <td>Phone</td>
                <td><?php echo $phone ?></td>
            </tr>

            <tr>
                <td>Password</td>
                <td>******</td>
                <td>
                    <form action="client_page.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="show_edit_password" value="show_edit_password">

                        <button class="delete_form_btn" style="width: 30%;padding: 5px;">Edit</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
    <br>
    <div class="profile-actions">
        <form action="client_page.php" method="post" style="float: left; width: 50%;">
            <input type="hidden" name="show_edit_profile" value="show_edit_profile">

            <button class="edit_form_btn" style="text-decoration: none;">EDIT</button>
        </form>

        <form action="client_page.php" method="post" style="float: left;width: 50%;">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="delete_account" value="delete_account">

            <button class="delete_form_btn" style="text-decoration: none;">DELETE</button>
        </form>
    </div>

    <div>
        <h4 style="color: #5cb85c;"><?php echo $success_message; ?></h4>
        <h4 style="color: #d9534f;"><?php echo $delete_error; ?></h4>
    </div>

    <div class="edit-profile-form">
        <?php if ($edit_profile) { ?>
            <form id="edit_form" action="client_page.php" method="post">
                <h3>Edit Profile</h3>

                <div class="edit-form-group">
                    <label for="firstname">First Name</label>
                    <input value="<?php echo $firstname; ?>" type="text" tabindex="3" id="firstname" name="firstname"
                           required/>
                </div>

                <div class="edit-form-group">
                    <label for="firstname">Last Name</label>
                    <input value="<?php echo $lastname; ?>" type="text" tabindex="3" id="firstname" name="lastname"
                           required>
                </div>

                <div class="edit-form-group">
                    <label for="email">Email</label>
                    <input value="<?php echo $email; ?>" type="text" tabindex="3" id="email" name="email" required>
                </div>

                <div class="edit-form-group">
                    <label for="phone">Phone</label>
                    <input value="<?php echo $phone; ?>" type="text" tabindex="3" id="phone" name="phone" required>
                </div>

                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="edit_profile_details" value="edit_profile_details">

                <div class="edit-form-group">
                    <button name="submit" type="submit" id="contact-submit" class="edit_form_btn"
                            data-submit="...Sending">Submit Details
                    </button>
                </div>
            </form>
        <?php } ?>

        <?php if ($edit_password) { ?>
            <form id="edit_form" action="client_page.php" method="post">
                <h3>Edit Password</h3>

                <div class="edit-form-group">
                    <label for="password_previous">Previous Password</label>
                    <input placeholder="******" type="password" tabindex="3" id="password_previous"
                           name="password_previous" required/>
                </div>

                <div class="edit-form-group">
                    <label for="password_new">New Password</label>
                    <input placeholder="******" type="password" tabindex="3" id="password_new" name="password_new"
                           required/>
                </div>

                <div class="edit-form-group">
                    <label for="password_new_confirm">Confirm New Password</label>
                    <input placeholder="******" type="password" tabindex="3" id="password_new_confirm"
                           name="password_new_confirm" required/>
                </div>

                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="edit_profile_password" value="edit_profile_password">

                <div class="edit-form-group">
                    <button name="submit" type="submit" id="contact-submit" class="edit_form_btn"
                            data-submit="...Sending">Submit Password
                    </button>
                </div>
            </form>
        <?php } ?>

        <?php if ($delete_account) { ?>
            <form id="edit_form" action="client_page.php" method="post">
                <h3>Delete Account</h3>

                <h4>Are you sure you want to delete your account</h4>


                <div class="edit-form-group">
                    <button name="submit" type="submit" id="contact-submit" class="cancel_form_btn"
                            data-submit="...Sending">Cancel
                    </button>
                    <button name="submit" type="submit" id="contact-submit" class="delete_form_btn"
                            data-submit="...Sending">Delete
                    </button>
                </div>
            </form>
        <?php } ?>


    </div>

</div>
