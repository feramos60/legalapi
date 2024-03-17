<?php



class UserGateway
{
    private PDO $conn;
    public $token;
    private $api_key;
    public $activation_token;
    private $hashed_token;
    
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

    public function create(array $data, string $secret): array | int
    {
        $array = [];
        $password_hash = password_hash($data["password"], PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));
        $hashed_token = strval($token);
        $hashed_token = hash_hmac('sha256', $hashed_token, $secret);
        $this->activation_token = $token;

        $sql = 'INSERT INTO users (user_name, first_name, last_name, email, phone, role_id, api_key, password_hash, activation_hash)
                    VALUES (:user_name, :first_name, :last_name, :email, :phone, :role_id, :api_key, :password_hash, :activation_hash)';
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user_name", $data["username"], PDO::PARAM_STR);
        $stmt->bindValue(":first_name", $data["firstname"], PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $data["lastname"], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
        $stmt->bindValue(":phone", $data["phone"], PDO::PARAM_STR);
        $stmt->bindValue(":role_id", 3, PDO::PARAM_INT);
        $stmt->bindValue(":api_key", $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
        $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);
        try {
            $stmt->execute();
            $array = [
                "id" => $this->conn->lastInsertId(),
                "api_key" => $this->activation_token
            ];
            return $array;
        } catch (\Throwable $th) {
            return 0;
        }
                
        
    }

    function sendActivationEmail(array $data, string $secret) : bool
    {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . '/legaltech/compa/signup/activate/' . $secret;
        // Leer el contenido del archivo activation_email.html
        $html = file_get_contents('./activation_email.html');

        // Reemplazar las variables en el HTML con los valores correspondientes
        $html = str_replace('{{url}}', $url, $html);
        $html = str_replace('{{nombre}}', $data['firstname'], $html);
       
        $send = Mail::send($data["email"], $data['firstname'], 'Activación de la cuenta - Ecoapplet', $html);
        return $send;
    }
}









