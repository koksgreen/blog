<?php

class User
{

    private $db;

    public function __construct(Database $db) {
        $this->db = $db->getConnection(); // ✅ use getConnection()
    }

    public function register($username, $email, $password)
{
    try {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);

        return ['success' => true, 'message' => 'Registration successful!'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}


    // public function register($username, $email, $password)
    // {
    //     try {
    //         //Check if user already exists
    //         $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    //         $stmt->execute([$username, $email]);
    //         if ($stmt->fetch()) {
    //             return 'Username or email already exists';
    //         }

    //         //create new user
    //         $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    //         $stmt = $this->db->prepare("INSERT INTO users (username, email, password VALUES (?, ?, ?)");
    //         $stmt->execute([$username, $email, $hashedPassword]);

    //         return true;
    //     } catch (PDOException $e) {
    //         return 'Registration failed. Please try again.';
    //     }
    // }

public function login($username, $password) {
    try {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // ✅ return the actual user row
            return $user;
        }

        return false; // login failed
    } catch (PDOException $e) {
        return false;
    }
}


//     public function login($username, $password)
// {
//     try {
//         $stmt = $this->db->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
//         $stmt->execute([$username, $username]);
//         $user = $stmt->fetch();

//         if ($user && password_verify($password, $user['password'])) {
            

//             $_SESSION['user_id'] = $user['id'];
//             $_SESSION['username'] = $user['username'];
//             $_SESSION['email'] = $user['email'];

//             return [
//                 'id' => $user['id'],
//                 'username' => $user['username'],
//                 'email' => $user['email']
//             ];

//         } else {
//             return false;
//         }
//     } catch (PDOException $e) {
//         return false;
//     }
// }


    public function logout(){
        session_destroy();
        return['success' => true, 'message' => 'Logged out successfukky'];
    }

    public function isLoggedIn(){
        return isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['email']);
    }

    public function getCurrentUser(){
        if ($this->isLoggedIn()){
            return[
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }

    public function getuserbyId($id){
        try{
            $stmt = $this->db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        }catch(PDOException $e) {
            return null;
        }
    }

    public function getAllusers(){
        try{
            $stmt = $this->db->prepare("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }catch (PDOException $e){
            return [];
        }
    }
}
