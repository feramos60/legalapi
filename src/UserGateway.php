<?php

class UserGateway
{
    private PDO $conn;
    private $token;
    private $api_key;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function getByAPIKey(string $key): array | false
    {
        $sql = "SELECT *
                FROM users
                WHERE api_key = :api_key";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":api_key", $key, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByUsername(string $username): array | false
    {
        $sql = "SELECT *
                FROM users
                WHERE user_name = :user_name";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":user_name", $username, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByID(int $id): array | false
    {
        $sql = "SELECT *
                FROM users
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data, string $secret): string
    {
        $password_hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $this->token = bin2hex(random_bytes(16)); 
        $para_token = strval($this->token);
        $this->api_key = hash_hmac('sha256', $para_token, $secret);

        $sql = 'INSERT INTO users (user_name, first_name, last_name, email, phone, role_id, api_key, password_hash, activation_hash)
                    VALUES (:user_name, :first_name, :last_name, :email, :phone, :role_id, :api_key, :password_hash, :activation_hash)';
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user_name", $data["user_name"], PDO::PARAM_STR);
        $stmt->bindValue(":first_name", $data["first_name"], PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $data["last_name"], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $data["phone"], PDO::PARAM_STR);
        $stmt->bindValue(":role_id", 3, PDO::PARAM_INT);
        $stmt->bindValue(":api_key", $this->api_key, PDO::PARAM_STR);
        $stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
        $stmt->bindValue(':activation_hash', $this->api_key, PDO::PARAM_STR);
        $stmt->execute();        
        return $this->conn->lastInsertId();
    }

    function sendActivationEmail(array $data) : bool
    {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . '/legaltech/compa/signup/activate/' . $this->api_key;
        // Leer el contenido del archivo activation_email.html
        $html = file_get_contents('./activation_email.html');

        // Reemplazar las variables en el HTML con los valores correspondientes
        $html = str_replace('{{url}}', $url, $html);
        $html = str_replace('{{nombre}}', $data['first_name'], $html);
       
        $send = Mail::send($data["email"], $data['first_name'], 'Activación de la cuenta - Ecoapplet', $html);
        return $send;
    }
}









