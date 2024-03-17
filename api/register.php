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

$requiredFields = ['username', 'firstname', 'lastname', 'email', 'phone', 'password'];
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

$id = intval($user_id['id']);

if ($id > 0) {

    $send = $user_gateway->sendActivationEmail($data, $user_id['api_key']);
    
    if ($send) {
        echo json_encode(['message' => 'user created and activation email sent ID: '. $id]);
        exit;
    }   

    http_response_code(400);
    echo json_encode(['message' => 'incomplete registration no se envio nada '. $send]);
    exit;
} else {
    http_response_code(400);
    echo json_encode(['message' => 'user exist']);
    exit;
}
