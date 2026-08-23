<?php

const DB_HOST = '127.0.0.1';
const DB_NAME = 'luxury_watches';
const DB_USER = 'root';
const DB_PASS = '';
const APP_NAME = 'Luxury Watches USA';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function request_body(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['error' => 'Method not allowed'], 405);
    }
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
