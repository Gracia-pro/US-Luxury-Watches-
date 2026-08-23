<?php
require_once __DIR__ . '/db.php';
require_admin();

$pdo = db();
$error = '';
$success = '';

function admin_post_value(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_post') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $brand = admin_post_value('brand');
            $name = admin_post_value('name');
            $description = admin_post_value('description');
            $year = admin_post_value('year');
            $condition = admin_post_value('condition_label');
            $price = admin_post_value('price');
            $imageUrl = admin_post_value('image_url');

            if ($brand === '' || $name === '' || $description === '' || $condition === '' || $imageUrl === '') {
                throw new RuntimeException('Watch, description, condition, and image are required.');
            }

            if ($productId > 0) {
                $statement = $pdo->prepare('UPDATE products SET brand = ?, name = ?, description = ?, price = ?, image_url = ?, condition_label = ?, year = ? WHERE id = ?');
                $statement->execute([$brand, $name, $description, $price === '' ? null : $price, $imageUrl, $condition, $year === '' ? null : (int)$year, $productId]);
                $success = 'Watch updated.';
            } else {
                $statement = $pdo->prepare('INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status) VALUES (?, ?, ?, ?, ?, ?, ?, \'available\')');
                $statement->execute([$brand, $name, $description, $price === '' ? null : $price, $imageUrl, $condition, $year === '' ? null : (int)$year]);
                $success = 'Watch added.';
            }
        } elseif ($action === 'delete_post') {
            $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $statement->execute([(int)($_POST['product_id'] ?? 0)]);
            $success = 'Watch deleted.';
        } elseif ($action === 'update_inquiry') {
            $status = $_POST['status'] ?? '';
            if (!in_array($status, ['new', 'in_progress', 'closed'], true)) {
                throw new RuntimeException('Invalid inquiry status.');
            }
            $statement = $pdo->prepare('UPDATE inquiries SET status = ? WHERE id = ?');
            $statement->execute([$status, (int)($_POST['inquiry_id'] ?? 0)]);
            $success = 'Inquiry status updated.';
        } elseif ($action === 'update_order') {
            $status = $_POST['status'] ?? '';
            if (!in_array($status, ['pending', 'contacted', 'paid', 'completed', 'cancelled'], true)) {
                throw new RuntimeException('Invalid order status.');
            }
            $statement = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $statement->execute([$status, (int)($_POST['order_id'] ?? 0)]);
            $success = 'Order status updated.';
        }
    } catch (Throwable $exception) {
        $error = $exception instanceof PDOException && $exception->errorInfo[1] === 1062
            ? 'That post title already exists. Choose a different title.'
            : $exception->getMessage();
    }
}

$editPost = null;
if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $statement->execute([(int)$_GET['edit']]);
    $editPost = $statement->fetch() ?: null;
}

$stats = [
    'products' => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'available'")->fetchColumn(),
    'inquiries' => (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn(),
    'orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','contacted')")->fetchColumn(),
    'posts' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
];
$inquiries = $pdo->query('SELECT id, type, name, email, subject, status, created_at FROM inquiries ORDER BY created_at DESC')->fetchAll();
$orders = $pdo->query('SELECT id, customer_name, customer_email, total, status, created_at FROM orders ORDER BY created_at DESC')->fetchAll();
$products = $pdo->query('SELECT id, brand, name, price, condition_label, status, updated_at FROM products ORDER BY updated_at DESC')->fetchAll();
$posts = $pdo->query('SELECT id, brand, name, condition_label, price, year, updated_at FROM products ORDER BY updated_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard | <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <h2>LW USA</h2>
        <nav>
            <a class="active" href="index.php">Overview</a>
            <a href="#inventory">Inventory</a>
            <a href="#inquiries">Inquiries</a>
            <a href="#orders">Orders</a>
            <a href="#posts">Posts</a>
        </nav>
        <a class="logout" href="logout.php">Sign out</a>
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            <div><p class="eyebrow">Operations dashboard</p><h1>Good morning, <?= htmlspecialchars($_SESSION['admin_username']) ?>.</h1></div>
            <a class="button" href="../shop.html">View storefront</a>
        </header>
        <?php if ($error): ?><p class="notice error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="notice success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <section class="stats">
            <div class="stat"><small>Available inventory</small><strong><?= $stats['products'] ?></strong></div>
            <div class="stat"><small>New inquiries</small><strong><?= $stats['inquiries'] ?></strong></div>
            <div class="stat"><small>Open orders</small><strong><?= $stats['orders'] ?></strong></div>
            <div class="stat"><small>Total watches</small><strong><?= $stats['posts'] ?></strong></div>
        </section>

        <section class="table-panel" id="inventory">
            <h2>Inventory</h2>
            <table><thead><tr><th>Watch</th><th>Condition</th><th>Price</th><th>Status</th><th>Updated</th></tr></thead><tbody>
            <?php foreach ($products as $item): ?><tr><td><?= htmlspecialchars($item['brand'] . ' ' . $item['name']) ?></td><td><?= htmlspecialchars($item['condition_label']) ?></td><td><?= $item['price'] === null ? 'On request' : '$' . number_format((float)$item['price'], 2) ?></td><td><span class="status <?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td><td><?= htmlspecialchars($item['updated_at']) ?></td></tr><?php endforeach; ?>
            <?php if (!$products): ?><tr><td colspan="5">No inventory found.</td></tr><?php endif; ?></tbody></table>
        </section>

        <section class="table-panel" id="inquiries">
            <h2>Inquiries</h2>
            <table><thead><tr><th>Type</th><th>Customer</th><th>Email</th><th>Subject</th><th>Status</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($inquiries as $item): ?><tr><td><?= htmlspecialchars(ucfirst($item['type'])) ?></td><td><?= htmlspecialchars($item['name']) ?></td><td><?= htmlspecialchars($item['email']) ?></td><td><?= htmlspecialchars($item['subject'] ?: '-') ?></td><td><span class="status <?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="update_inquiry"><input type="hidden" name="inquiry_id" value="<?= (int)$item['id'] ?>"><select name="status" onchange="this.form.submit()"><option value="new" <?= $item['status'] === 'new' ? 'selected' : '' ?>>New</option><option value="in_progress" <?= $item['status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="closed" <?= $item['status'] === 'closed' ? 'selected' : '' ?>>Closed</option></select></form></td></tr><?php endforeach; ?>
            <?php if (!$inquiries): ?><tr><td colspan="6">No inquiries yet.</td></tr><?php endif; ?></tbody></table>
        </section>

        <section class="table-panel" id="orders">
            <h2>Orders</h2>
            <table><thead><tr><th>Order</th><th>Customer</th><th>Email</th><th>Total</th><th>Status</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($orders as $item): ?><tr><td>#<?= (int)$item['id'] ?><br><small><?= htmlspecialchars($item['created_at']) ?></small></td><td><?= htmlspecialchars($item['customer_name']) ?></td><td><?= htmlspecialchars($item['customer_email']) ?></td><td>$<?= number_format((float)$item['total'], 2) ?></td><td><span class="status <?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="update_order"><input type="hidden" name="order_id" value="<?= (int)$item['id'] ?>"><select name="status" onchange="this.form.submit()"><?php foreach (['pending','contacted','paid','completed','cancelled'] as $status): ?><option value="<?= $status ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></form></td></tr><?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="6">No orders yet.</td></tr><?php endif; ?></tbody></table>
        </section>

        <section class="table-panel" id="posts">
            <div class="panel-heading"><h2><?= $editPost ? 'Edit watch' : 'Add watch' ?></h2><?php if ($editPost): ?><a class="muted" href="index.php#posts">Cancel edit</a><?php endif; ?></div>
            <form method="post" class="post-form"><input type="hidden" name="action" value="save_post"><input type="hidden" name="product_id" value="<?= (int)($editPost['id'] ?? 0) ?>">
                <label>Watch brand<input name="brand" required value="<?= htmlspecialchars($editPost['brand'] ?? '') ?>"></label>
                <label>Watch name<input name="name" required value="<?= htmlspecialchars($editPost['name'] ?? '') ?>"></label>
                <label>Description<textarea name="description" rows="3" required><?= htmlspecialchars($editPost['description'] ?? '') ?></textarea></label>
                <label>Year<input name="year" type="number" min="1" max="9999" value="<?= htmlspecialchars((string)($editPost['year'] ?? '')) ?>"></label>
                <label>Condition<input name="condition_label" required value="<?= htmlspecialchars($editPost['condition_label'] ?? '') ?>"></label>
                <label>Price<input name="price" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string)($editPost['price'] ?? '')) ?>"></label>
                <label>Image URL<input type="url" name="image_url" required value="<?= htmlspecialchars($editPost['image_url'] ?? '') ?>"></label>
                <button class="button" type="submit"><?= $editPost ? 'Update watch' : 'Add watch' ?></button>
            </form>
            <h3>Existing watches</h3>
            <table><thead><tr><th>Watch</th><th>Condition</th><th>Price</th><th>Year</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($posts as $item): ?><tr><td><?= htmlspecialchars($item['brand'] . ' ' . $item['name']) ?></td><td><?= htmlspecialchars($item['condition_label']) ?></td><td><?= $item['price'] === null ? 'On request' : '$' . number_format((float)$item['price'], 2) ?></td><td><?= htmlspecialchars((string)($item['year'] ?? '-')) ?></td><td><?= htmlspecialchars($item['updated_at']) ?></td><td><a class="muted" href="?edit=<?= (int)$item['id'] ?>#posts">Edit</a> <form method="post" class="inline-form" onsubmit="return confirm('Delete this watch?')"><input type="hidden" name="action" value="delete_post"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><button class="link-button danger-text" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
            <?php if (!$posts): ?><tr><td colspan="6">No watches yet.</td></tr><?php endif; ?></tbody></table>
        </section>
    </main>
</div>
</body>
</html>
