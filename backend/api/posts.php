<?php
require_once __DIR__ . '/../db.php';

try {
    $statement = db()->query("SELECT id, title, slug, excerpt, content, image_url, category, published, created_at, updated_at
        FROM posts WHERE published = 1 ORDER BY created_at DESC");
    json_response(['posts' => $statement->fetchAll()]);
} catch (Throwable $exception) {
    json_response(['error' => 'Unable to load posts'], 500);
}