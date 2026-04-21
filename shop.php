<?php
$pageTitle = 'Shop';
require_once 'includes/config.php';

// Get filters
$search   = sanitize($_GET['search'] ?? '');
$catId    = (int)($_GET['category'] ?? 0);
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 200000);
$sort     = sanitize($_GET['sort'] ?? 'newest');
$featured = isset($_GET['featured']);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

// Build query
$where = ['p.price BETWEEN ? AND ?'];
$params = [$minPrice, $maxPrice];

if ($search) { $where[] = '(p.product_name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)'; $params = array_merge([$minPrice, $maxPrice], ["%$search%", "%$search%", "%$search%"]); $params = [$minPrice, $maxPrice, "%$search%", "%$search%", "%$search%"]; }
if ($catId) { $where[] = 'p.category_id = ?'; $params[] = $catId; }
if ($featured) { $where[] = 'p.is_featured = 1'; }

$whereStr = 'WHERE ' . implode(' AND ', $where);
$orderBy = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'p.rating DESC',
    'name'       => 'p.product_name ASC',
    default      => 'p.created_at DESC',
};

// Total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.category_id $whereStr");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Products
$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Banner -->
<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title"><?= $search ? "Results for \"$search\"" : ($catId ? ($categories[array_search($catId, array_column($categories,'category_id'))]['category_name'] ?? 'Shop') : 'All Products') ?></h1>
        <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item active">Shop</li></ol></nav>
    </div>
</div>

<div class="container section-spacing" style="padding-top:40px;">
    <div class="row g-4">
        <!-- SIDEBAR FILTERS -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="shop-sidebar">
                <form id="filterForm" method="GET">
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>

                    <!-- Categories -->
                    <div class="mb-4">
                        <div class="filter-heading"><i class="bi bi-grid me-2"></i>Categories</div>
                        <div class="filter-option <?= !$catId ? 'active' : '' ?>">
                            <input type="radio" name="category" value="" id="cat_all" <?= !$catId ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="cat_all">All Categories</label>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option <?= $catId == $cat['category_id'] ? 'active' : '' ?>">
                            <input type="radio" name="category" value="<?= $cat['category_id'] ?>" id="cat_<?= $cat['category_id'] ?>" <?= $catId == $cat['category_id'] ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="cat_<?= $cat['category_id'] ?>"><i class="bi <?= $cat['icon'] ?>"></i> <?= $cat['category_name'] ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-4">
                        <div class="filter-heading"><i class="bi bi-cash-stack me-2"></i>Price Range</div>
                        <input type="range" id="priceRange" name="max_price" min="0" max="200000" value="<?= $maxPrice ?>" step="1000" class="form-range mb-2" style="accent-color:var(--blue-primary);" onchange="document.getElementById('priceDisplay').textContent='₱'+parseInt(this.value).toLocaleString()">
                        <div class="d-flex justify-content-between">
                            <small style="color:var(--text-secondary);">₱0</small>
                            <small style="color:var(--blue-primary);font-weight:600;" id="priceDisplay">₱<?= number_format($maxPrice) ?></small>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="mb-4">
                        <div class="filter-heading"><i class="bi bi-sort-down me-2"></i>Sort By</div>
                        <?php $sorts = ['newest'=>'Newest','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low','rating'=>'Best Rating','name'=>'Name A-Z']; ?>
                        <?php foreach ($sorts as $val => $label): ?>
                        <div class="filter-option <?= $sort === $val ? 'active' : '' ?>">
                            <input type="radio" name="sort" value="<?= $val ?>" id="sort_<?= $val ?>" <?= $sort === $val ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label for="sort_<?= $val ?>"><?= $label ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn-primary-custom w-100 justify-content-center">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    <a href="shop.php" class="btn-outline-custom w-100 justify-content-center mt-2">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                </form>
            </div>
        </div>

        <!-- PRODUCTS GRID -->
        <div class="col-lg-9">
            <!-- Top Bar -->
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div style="color:var(--text-secondary);font-size:.9rem;">
                    Showing <strong style="color:var(--white-pure);"><?= $totalProducts ?></strong> products
                    <?= $search ? " for \"<strong>$search</strong>\"" : '' ?>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <!-- Mobile filter -->
                    <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileFilters">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <!-- Sort (mobile) -->
                    <select class="form-select form-select-sm" style="width:auto;background:var(--black-card);border-color:var(--black-border);color:var(--text-primary);" onchange="window.location.href='?<?= http_build_query(array_merge($_GET, ['sort'=>''])) ?>sort='+this.value">
                        <?php foreach ($sorts as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-search fs-1 text-blue mb-3 d-block"></i>
                <h4 style="color:var(--text-primary);">No products found</h4>
                <p style="color:var(--text-secondary);">Try adjusting your filters or search terms</p>
                <a href="shop.php" class="btn-primary-custom">View All Products</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $p):
                    $discount = $p['original_price'] ? round(100 - ($p['price'] / $p['original_price'] * 100)) : 0;
                ?>
                <div class="col-6 col-md-4">
                    <div class="product-card h-100">
                        <div class="product-card-img-wrap">
                            <img src="<?= $p['product_image'] ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="product-card-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=500'">
                            <?php if ($discount > 0): ?><span class="product-badge badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                            <?php if ($p['stock'] <= 0): ?><span class="product-badge badge-hot" style="top:auto;bottom:12px;left:12px;">Out of Stock</span><?php endif; ?>
                            <div class="product-card-actions">
                                <button class="action-btn" onclick="toggleWishlist(<?= $p['product_id'] ?>, this)"><i class="bi bi-heart"></i></button>
                                <a href="product.php?id=<?= $p['product_id'] ?>" class="action-btn"><i class="bi bi-eye"></i></a>
                            </div>
                        </div>
                        <div class="product-card-body">
                            <div class="product-brand"><?= htmlspecialchars($p['brand']) ?></div>
                            <div class="product-title"><a href="product.php?id=<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></a></div>
                            <div class="product-rating">
                                <div class="stars"><?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=round($p['rating'])?'-fill':'').'"></i>'; ?></div>
                                <span class="rating-count">(<?= $p['review_count'] ?>)</span>
                            </div>
                            <div class="product-price-row">
                                <div>
                                    <span class="product-price"><?= formatPrice($p['price']) ?></span>
                                    <?php if ($p['original_price']): ?><span class="product-original-price"><?= formatPrice($p['original_price']) ?></span><?php endif; ?>
                                </div>
                                <button class="add-cart-btn" onclick="addToCart(<?= $p['product_id'] ?>)" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-center mt-5">
                <nav>
                    <ul class="pagination gap-1">
                        <?php for ($i = 1; $i <= $totalPages; $i++):
                            $queryParams = array_merge($_GET, ['page' => $i]);
                        ?>
                        <li class="page-item">
                            <a class="page-link <?= $i === $page ? 'active' : '' ?>"
                               href="?<?= http_build_query($queryParams) ?>"
                               style="background:<?= $i===$page ? 'var(--gradient-blue)' : 'var(--black-card)' ?>;border:1px solid var(--black-border);color:<?= $i===$page ? 'white' : 'var(--text-secondary)' ?>;border-radius:var(--radius-sm);">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
