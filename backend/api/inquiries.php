<?php
require_once __DIR__ . '/../db.php';
require_post();

$data = request_body();
$type = in_array($data['type'] ?? '', ['contact', 'valuation', 'rental'], true) ? $data['type'] : 'contact';
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'A valid name and email are required'], 422);
}

try {
    $statement = db()->prepare('INSERT INTO inquiries (type, name, email, phone, subject, details) VALUES (?, ?, ?, ?, ?, ?)');
    $statement->execute([$type, $name, $email, trim($data['phone'] ?? ''), trim($data['subject'] ?? ''), trim($data['details'] ?? '')]);
    json_response(['message' => 'Thank you. Your request has been received.', 'id' => db()->lastInsertId()], 201);
} catch (Throwable $exception) {
    json_response(['error' => 'Unable to save your request'], 500);
}
