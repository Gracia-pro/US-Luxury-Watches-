<?php
require_once __DIR__ . '/../db.php';
require_post();

$data = request_body();
$name = trim($data['customer_name'] ?? '');
$email = trim($data['customer_email'] ?? '');
$items = is_array($data['items'] ?? null) ? $data['items'] : [];
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$items) {
    json_response(['error' => 'Customer details and cart items are required'], 422);
}

try {
    $pdo = db();
    $pdo->beginTransaction();
    $products = [];
    $total = 0;
    $lookup = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ? AND status = 'available'");
    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $quantity = max(1, min(10, (int)($item['quantity'] ?? 1)));
        $lookup->execute([$productId]);
        $product = $lookup->fetch();
        if (!$product || $product['price'] === null) {
            throw new RuntimeException('One of the selected products is unavailable.');
        }
        $products[] = [$product, $quantity];
        $total += (float)$product['price'] * $quantity;
    }
    $order = $pdo->prepare('INSERT INTO orders (customer_name, customer_email, total) VALUES (?, ?, ?)');
    $order->execute([$name, $email, $total]);
    $orderId = (int)$pdo->lastInsertId();
    $line = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity) VALUES (?, ?, ?, ?, ?)');
    foreach ($products as [$product, $quantity]) {
        $line->execute([$orderId, $product['id'], $product['name'], $product['price'], $quantity]);
    }
    $pdo->commit();
    json_response(['message' => 'Your order request has been received.', 'order_id' => $orderId], 201);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_response(['error' => $exception->getMessage()], 422);
}
