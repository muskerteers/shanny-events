<?php

require_once 'connect.php';

class Auth
{
    private $conn;
    private $email_err;
    private $password_err;

    public function __construct()
    {
        $database = new Database();
        $db = $database->dbConnect();
        $this->conn = $db;
        $this->email_err = $this->password_err = '';
    }

    public function runQuery($sql)
    {
        $stmt = mysqli_prepare($this->conn, $sql);
        return $stmt;
    }

    //function for the registration of the users
    public function register($fname, $lname, $email, $phone, $pass, $utype)
    {
        try {
            $password = md5($pass);

            $stmt = $this->runQuery("INSERT INTO users(first_name, last_name, email, phone, password, user_type) 
                  VALUES('$fname', '$lname', '$email', '$phone', '$password', '$utype')");

            mysqli_stmt_execute($stmt);

            $this->redirect("../auth/login.php");

        } catch (mysqli_sql_exception $ex) {
            echo $ex->getMessage();
        }
    }

    //function for user login
    public function login($email, $pass)
    {
        try {
            $stmt = $this->runQuery("SELECT id, email, password, user_type FROM users WHERE email = '$email' AND deleted_at IS NULL OR deleted_at = ''");

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $user_type);

                    if (mysqli_stmt_fetch($stmt)) {
                        // Password is correct, so start a new session
                        if (password_verify($pass, $hashed_password)) {
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["user_type"] = $user_type;

                            //redirect user according to user type
                            if ($user_type == 1) {
                                // admin/ event planner
                                $this->redirect('../admin/admin_page.php');
                                return true;
                            } elseif ($user_type == 2) {
                                // customer
                                $this->redirect('../client/client_page.php');
                                return true;
                            }

                        } else {
                            $this->password_err = "The password you entered was not valid.";
                            return false;
                        }
                    }
                } else {
                    $this->email_err = "No account found with that email address.";
                    return false;
                }
            }

        } catch (mysqli_sql_exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function is_logged_in()
    {
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
            return true;
        } else {
            return false;
        }
    }

    public function redirect($url)
    {
        header("Location: $url");
    }

    public function logout()
    {
        session_destroy();

        $_SESSION['loggedin'] = false;
    }

    public function getClientInformation($userId)
    {
        try {
            $stmt = $this->runQuery("SELECT * FROM users WHERE id = $userId");

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                return $row;
            }

        } catch (mysqli_sql_exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function editClientInformation($user_id, $fname, $lname, $mail, $phone)
    {
        try {
            $stmt = $this->runQuery("UPDATE users SET first_name = '$fname', last_name = '$lname', email = '$mail', phone = '$phone' WHERE id='$user_id'");

            if (mysqli_stmt_execute($stmt)) {
                return true;
            }

            return false;
        } catch (mysqli_sql_exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function editUserPassword($user_id, $p_pass, $new_pass)
    {
        $stmt = $this->runQuery("SELECT password FROM users WHERE id = '$user_id'");

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $hashed_pass);

                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($p_pass, $hashed_pass)) {
                        $stmt = $this->runQuery("UPDATE users SET password = '$new_pass' WHERE id='$user_id'");

                        if (mysqli_stmt_execute($stmt)) {
                            return true;
                        }

                        return false;
                    }
                }
            }
        }
    }

    public function deleteAccount($userId)
    {
        $deleted_date = date("Y/m/d"); // today's date

        $stmt = $this->runQuery("UPDATE users SET deleted_at = '$deleted_date' WHERE id='$userId'");

        if (mysqli_stmt_execute($stmt)) {
            return true;
        }

        return false;
    }

    public function getAllEvents($userId)
    {
        $stmt = $this->runQuery("SELECT * FROM events WHERE user_id = '$userId' AND deleted_at IS NULL OR deleted_at = ''");

        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $item){
                echo $item['name'];
            }
        }
    }
}


?>