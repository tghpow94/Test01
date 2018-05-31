<?php

class UserManager {
    private $db;

    public function __construct(PDO $database) {
        $this->db = $database;
        $ini = $this->db->prepare("SET NAMES 'utf8'");
        $ini->execute(array());
    }
	
	public function getUserById($id) {
        $result = $this->db->prepare("SELECT ID, Firstname, Lastname, Username, Email, Created, LastModified FROM user where ID = :id");
        $result->execute(array(
            ":id" => $id
        ));

        $response["user"] = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($response["data"])) {
            $response["success"] = true;
        } else {
            $response["success"] = false;
			$response["message"] = "user not found";
        }

        return $response;
    }
	
	public function getUserByEmail($email) {
        $result = $this->db->prepare("SELECT ID, Firstname, Lastname, Username, Email, Created, LastModified FROM user where Email = :email");
        $result->execute(array(
            ":email" => $email
        ));

        $response["user"] = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($response["data"])) {
            $response["success"] = true;
        } else {
            $response["success"] = false;
			$response["message"] = "user not found";
        }

        return $response;
    }
	
	public function getUserByUsername($username) {
        $result = $this->db->prepare("SELECT ID, Firstname, Lastname, Username, Email, Created, LastModified FROM user where Username = :username");
        $result->execute(array(
            ":username" => $username
        ));

        $response["user"] = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($response["data"])) {
            $response["success"] = true;
        } else {
            $response["success"] = false;
			$response["message"] = "user not found";
        }

        return $response;
    }
	
	public function getUserConnect($email, $password) {
        
        if (isset($email) && isset($password)) {
            $result = $this->db->prepare("SELECT * FROM user where Email = :email");
            $result->execute(array(
                ":email" => $email
            ));
			
			$user = $result->fetch(PDO::FETCH_ASSOC);
            if (isset($user["ID"])) {
				$password = hash("sha256", $password . $user["Salt"]);
				if ($password == $user["Password"]) {
					$response["success"] = true;
					$response["message"] = "login successful";
					$response["user"] = $user;
				} else {
					$response["success"] = false;
					$response["message"] = "login fail";
				}
            } else {
                $response["success"] = false;
				$response["message"] = "login fail";
            }
        } else {
            $response["success"] = false;
			$response["message"] = "credentials not set";
        }
        return $response;
    }
	
	public function addUser($user){
		$response["success"] = true;
		if (isset($user)) {
			$getUser = getUserByEmail($user["email"]);
			if ($getUser["success"] == true) {
				$response["success"] = false;
				$response["message"] = "Email address is already taken \n";
			}
			$getUser = getUserByUsername($user["username"]);
			if ($getUser["success"] == true) {
				$response["success"] = false;
				$response["message"] .= "Username is already taken \n";
			}
			
			if (validEmail($user["email"]) && $response["success"] == true) {
				$salt = genererCode();
				$result = $this->db->prepare("INSERT INTO (Firstname, Lastname, Username, Password, Email, Created, LastModified, Salt) VALUES (:firstName, :lastname, :username, :password, :email, NOW(), NOW(), :salt)");
				$result->execute(array(
					":firstName" => $user["firstname"],
					":lastname" => $user["lastname"],
					":username" => $user["username"],
					":password" => hash('sha256', $user["password"].$salt),
					":email" => $user["email"],
					":salt" => $salt
				));
			
				$user = $result->fetch(PDO::FETCH_ASSOC);
				$response["success"] = true;
				$response["message"] = $user;
			}
		} else {
			$response["success"] = false;
			$response["message"] = "user information not set"
		}
		return $response;
	}
	
	function genererCode() {
        $characts    = 'abcdefghijklmnopqrstuvwxyz';
        $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characts   .= '1234567890';
		$characts   .= ',?;.:/=+ù%µ£$*&é@#è!çà-_\<>';
        $code_aleatoire      = '';

        for($i=0;$i < 15;$i++){
            $code_aleatoire .= substr($characts,rand()%(strlen($characts)),1);
        }
        return $code_aleatoire;
    }

    /*public function getUsers() {
        $result = $this->db->prepare("SELECT id, name, firstName FROM Users ");
        $result->execute(array(
        ));

        $tabUsers = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($tabUsers[0]['id'])) {
            $tabUsers[0]['error'] = false;
        } else {
            return $tabUsers;
            $tabUsers[0]['error'] = true;
        }

        return $tabUsers;
    }*/
	
    /*public function getUserDroit($id) {
        $result = $this->db->prepare("SELECT * FROM User_right where idUser = :idUser");
        $result->execute(array(
            ":idUser" => $id
        ));

        $tabUsers = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($tabUsers[0]['idRight']))
            return $tabUsers[0]['idRight'];
        else
            return null;
    }*/

    /*public function getUserDroitName($id) {
        $result = $this->db->prepare("SELECT * FROM Rights WHERE id IN (SELECT idRight FROM User_right WHERE idRight = id AND idUser = :id)");
        $result->execute(array(
            ":id" => $id
        ));

        $tabUsers = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($tabUsers[0]['name']))
            return $tabUsers[0]['name'];
        else
            return null;
    }*/

    /*public function resetPassword($mail) {
        $result = $this->db->prepare("SELECT * FROM Users where email = :email");
        $result->execute(array(
            ":email" => $mail
        ));

        $tabUsers = $result->fetchAll(PDO::FETCH_ASSOC);

        if (isset($tabUsers[0]['id'])) {
            $user = $tabUsers[0];
            $code = $this->genererCode();

            $result = $this->db->prepare("DELETE FROM Activation WHERE idUser = :idUser AND description = 'mot de passe oublié'");
            $result->execute(array(
                ":idUser" => $user['id']
            ));

            $result = $this->db->prepare("INSERT INTO Activation (idUser, code, date, description) VALUES (:idUser, :code, :date, :description)");
            $result->execute(array(
                ":idUser" => $user['id'],
                ":code" => $code,
                ":date" => date("Y-m-d H:i:s"),
                ":description" => "mot de passe oublié"
            ));

            $message = "Bonjour, <br><br>
						Vous avez demandé à réinitialiser votre mot de passe.<br><br>
						Cliquez sur le lien suivant pour finaliser la procédure : <a href='http://91.121.151.137/TFE/bpho/admin/passwordreset_final?code=".$code."'>http://91.121.151.137/TFE/bpho/admin/passwordreset_final?code=".$code."</a>";

            $to = $mail;
            $from = "thomaspicke2@gmail.com";
            $sujet = "BPHO : Réinitialisation de votre mot de passe";
            $entete = "From:" . $from . "\r\n";
            $entete .= "Content-Type: text/html; charset=utf-8\r\n";
            mail($to, $sujet, $message, $entete);

            return true;
        } else
            return false;
    }*/

    function updateUser($newUser) {
        $newUser['name'][0] = strtoupper($newUser['name'][0]);
        $newUser['firstName'][0] = strtoupper($newUser['firstName'][0]);
        $retour['error'] = "ok";
        $retour['name'] = $newUser['name'];
        $retour['firstName'] = $newUser['firstName'];

        if($newUser['mail'] != $newUser['oldMail']) {
            $emailChange = true;
        } else {
            $emailChange = false;
        }

        if($this->champsEmailValable($newUser['mail'])) {
            if($this->getUserByMail($newUser['mail']) != null && $emailChange) {
                $retour['error'] = "email taken";
            } else {
                $user = $this->getUserConnect($newUser['oldMail'], $newUser['oldPassword']);
                if (isset($user['id']) && $user['error'] == false) {
                    $retour['error'] = "ok";
                    if($newUser['mail'] != $newUser['oldMail']) {
                        $this->updateEmail($newUser['id'], $newUser['mail']);
                    }
                    $result = $this->db->prepare("UPDATE Users SET name = :name, firstName = :firstName, phone = :phone  WHERE id = :id");
                    $result->execute(array(
                        ":name" => $newUser['name'],
                        ":firstName" => $newUser['firstName'],
                        ":phone" => $newUser['phone'],
                        ":id" => $newUser['id']
                    ));

                    if ($newUser['newPassword'] != "") {
                        $password = hash("sha256", $newUser['newPassword'] . $user['salt']);
                        $result = $this->db->prepare("UPDATE Users SET password = :password WHERE id = :id");
                        $result->execute(array(
                            ":password" => $password,
                            ":id" => $newUser['id']
                        ));
                    }

                } else {
                    $retour['error'] = "wrong user";
                }
            }
        } else {
            $retour['error'] = "wrong email";
        }

        return $retour;
    }

    function updateEmail($idUser, $mail) {
        $result = $this->db->prepare("UPDATE Users SET email = :email  WHERE id = :id");
        $result->execute(array(
            ":email" => $mail,
            ":id" => $idUser
        ));
    }

    function validEmail($email) {
        if(preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
            return true;
        }
        else {
            return false;
        }
    }
}
?>