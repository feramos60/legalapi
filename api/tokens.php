<?php

$payload = [
    "sub" => $user["id"],
    "name" => $user["first_name"],
    "exp" => time() + 300
];

$access_token = $codec->encode($payload);

$refresh_token_expiry = time() + 432000;

$refresh_token = $codec->encode([
    "sub" => $user["id"],
    "exp" => $refresh_token_expiry
]);

echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token,
    "first_name" => $user["first_name"],
    "last_name" => $user["last_name"],    
]);