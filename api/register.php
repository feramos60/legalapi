<?php
declare(strict_types=1);

require __DIR__ . "/bootstrap.php";
require __DIR__ . "/mail.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    
    http_response_code(405);
    header("Allow: POST");
    exit;
}

$data = (array) json_decode(file_get_contents("php://input"), true);

$requiredFields = ['user_name', 'password', 'first_name', 'last_name', 'email', 'phone'];
foreach ($requiredFields as $field) {
    if (! array_key_exists($field, $data)) {
        http_response_code(400);
        echo json_encode(['message' => 'incomplete registration']);
        exit;
    }
}

$database = new Database($_ENV["DB_HOST"],
                         $_ENV["DB_NAME"],
                         $_ENV["DB_USER"],
                         $_ENV["DB_PASS"]);

$user_gateway = new UserGateway($database);

$user_id= $user_gateway->create($data, $_ENV["SECRET_KEY_PASS"]);

if ($user_id > 0) {

    $send = $user_gateway->sendActivationEmail($data);
    
    if ($send) {
        echo json_encode(['message' => 'user created and activation email sent ID: '. $user_id]);
        exit;
    }   

    http_response_code(400);
    echo json_encode(['message' => 'incomplete registration no se envio nada '. $send]);
    exit;
}
