<?php
include_once 'DataSource.php';
class SessionUser {
    static private $valid = false;
    static private $roles = array();
    static private $checked_login = false;

    static function start($mysqli,$session_id="") {
        $session_name = ($session_id ? $session_id : "sec_session_id"); // Set a custom session name
        $secure = false; // Set to true if using https.
        $httponly = true; // This stops javascript being able to access the session id.
        
        ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
        session_name($session_name); // Sets the session name to the one set above.
        session_start(); // Start the php session
        session_regenerate_id(true); // regenerated the session, delete the old one.
        
        SessionUser::isValidUser();
    }
    
    static function login($email, $password) {
        // Using prepared Statements means that SQL injection is not possible.
        
        if ($stmt = DS::get()->prepare("SELECT id, username, password, salt FROM users WHERE email = ? LIMIT 1")) {
            $stmt->bind_param('s', $email); // Bind "$email" to parameter.
            $stmt->execute(); // Execute the prepared query.
            $stmt->store_result();
            $stmt->bind_result($user_id, $username, $db_password, $salt); // get variables from result.
            $stmt->fetch();
            
            $password = hash('sha512', $password.$salt); // hash the password with the unique salt.
            
            if($stmt->num_rows == 1) { // If the user exists
                // We check if the account is locked from too many login attempts
                if(SessionUser::checkbrute($user_id) == true) {
                    // Account is locked
                    // Send an email to user saying their account is locked
                    die("LOCKED");
                    return false;
                } else {
                    if($db_password == $password) { // Check if the password in the database matches the password the user submitted.
                        // Password is correct!
                        $ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user.
                        $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
                        
                        $user_id = preg_replace("/[^0-9]+/", "", $user_id); // XSS protection as we might print this value
                        $_SESSION['user_id'] = $user_id;
                        $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username); // XSS protection as we might print this value
                        $_SESSION['username'] = $username;
                        $_SESSION['login_string'] = hash('sha512', $password.$ip_address.$user_browser);
                        // Login successful.
                        return true;
                    } else {
                        // Password is not correct
                        // We record this attempt in the database
                        $now = time();
                        DS::get()->query("INSERT INTO login_attempts (user_id) VALUES ('$user_id')");
                        return false;
                    }
                }
            } else {
                // No user exists. 
                return false;
            }
        }
    }
    
    static function logout() {
        // Unset all session values
        $_SESSION = array();
        // get session parameters 
        $params = session_get_cookie_params();
        // Delete the actual cookie.
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        // Destroy session
        session_destroy();
    }

    static function login_check() {
        // Check if all session variables are set
        if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
            $user_id = $_SESSION['user_id'];
            $login_string = $_SESSION['login_string'];
            $username = $_SESSION['username'];
            $ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
            $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
            
            if ($stmt = DS::get()->prepare("SELECT password, roles FROM users WHERE id = ? LIMIT 1")) { 
                $stmt->bind_param('i', $user_id); // Bind "$user_id" to parameter.
                $stmt->execute(); // Execute the prepared query.
                $stmt->store_result();

                if($stmt->num_rows == 1) { // If the user exists
                    $stmt->bind_result($password,$roles); // get variables from result.
                    $stmt->fetch();
                    $login_check = hash('sha512', $password.$ip_address.$user_browser);
                    if($login_check == $login_string) {
                        // Logged In!!!!
                        SessionUser::$roles = explode(",", $roles);
                        return true;
                    } else {
                        // Not logged in
                        return false;
                    }
                } else {
                    // Not logged in
                    return false;
                }
            } else {
                // Not logged in
                return false;
            }
        } else {
            // Not logged in
            return false;
        }
    }
    
    static private function checkbrute($user_id) {
        // Get timestamp of current time
        $now = time();
        // All login attempts are counted from the past 2 hours. 
        $valid_attempts = $now - (2 * 60 * 60); 
        
        if ($stmt = DS::get()->prepare("SELECT time FROM login_attempts WHERE user_id = ? AND time > '$valid_attempts'")) {
            $stmt->bind_param('i', $user_id);
            // Execute the prepared query.
            $stmt->execute();
            $stmt->store_result();
            // If there has been more than 5 failed logins
            if($stmt->num_rows > 5) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    static public function registerUser($user) {
        $user["salt"] = SessionUser::generatePassword(16);
        $user["password"] = hash('sha512', $user["password"].$user["salt"]); // hash the password with the unique salt.
        
        DS::insert("users", $user);
    }
    
    static public function generatePassword($length) {
        $pass = "";
        for($x = 1; $x < $length; $x++) {
            if($x % ceil(rand(1,2))) {
                $pass.=rand(0, 9);
            } else if($x % ceil(rand(3,6))) {
                $pass.=chr(rand(65, 90)); // 65 - 90, 97 - 122
            } else {
                $pass.=chr(rand(97, 122)); // 65 - 90, 97 - 122
            }
        }
        return $pass;
    }
    
    static public function isValidUser() {
        if(!SessionUser::$checked_login) {
            SessionUser::$valid = SessionUser::login_check();
        }
        
        return SessionUser::$valid;
    }
    
    static public function getUserRoles() {
        return SessionUser::$roles;
    }
    
    static public function hasRoles($roles,$checkInRoles = null,$ownerId=-1) {
        if(!$checkInRoles) {
             $checkInRoles = SessionUser::getUserRoles();
        }
        
        $checkInRoles = count($checkInRoles) ? $checkInRoles : 'anonymous';
        
        $userRoles = is_array($checkInRoles) ? $checkInRoles : explode(",",$checkInRoles);
        $roles = (is_array($roles) ? $roles : explode(",",$roles));
        
        // admin role may view any page
        if((array_search('admin',$userRoles)!==false && array_search('system',$roles)===false) || (array_search('own',$roles)!==false && (isset($_SESSION['user_id']) && $_SESSION['user_id']==$ownerId))) {
            return true;
        }

        // if an array has been passed check each item
        foreach($roles as $role) {
            if(array_search($role,$userRoles)!==false) {
                return true;
            }
        }

        return false;
    }

    
    static public function install() {
        $tables = DS::list_tables();
        
        // check if the users table exists, if not create it.
        if(array_search('users',$tables)===false) {
            // generate the create table query
            $query = "CREATE TABLE users (";
            $query.= "id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, ";
            $query.= "username VARCHAR(128) NOT NULL, ";
            $query.= "email VARCHAR(128) NOT NULL, ";
            $query.= "password VARCHAR(512) NOT NULL, ";
            $query.= "salt VARCHAR(16) NOT NULL, ";
            $query.= "roles TEXT NOT NULL, ";
            $query.= "active TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 1, ";
            $query.= "createDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
            $query.= "PRIMARY KEY (id)";
            $query.= ") ENGINE = InnoDB;";

            if(DS::query($query)) {
                //message_add("The Users table has been created.");
            }
        }
    }
}
?>