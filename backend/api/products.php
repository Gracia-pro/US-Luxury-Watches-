<?php
require_once __DIR__ . '/../db.php';

try {
    $search = trim($_GET['search'] ?? '');
    $brand = trim($_GET['brand'] ?? '');
    $productId = (int)($_GET['product_id'] ?? 0);

    $statement = db()->prepare("SELECT id, brand, name, description, price, image_url, condition_label, year, status, featured FROM products 
     WHERE status = 'available' 
    AND (:product_id = 0 OR id = :product_id_value)
     AND (:search = '' OR CONCAT(brand, ' ', name, ' ', description) LIKE :like_search) 
     AND (:brand = '' OR LOWER(brand) = LOWER(:brand_value)) 
     ORDER BY featured DESC, created_at DESC");
     
    $statement->execute(['product_id' => $productId, 'product_id_value' => $productId, 'search' => $search, 'like_search' => '%' . $search . '%', 'brand' => $brand, 'brand_value' => $brand]);
    json_response(['products' => $statement->fetchAll()]);
} catch (Throwable $exception) {
    json_response(['error' => 'Unable to load products'], 500);
}
