<?php
    session_start();

    require_once 'classes/Database.php';
    require_once 'classes/User.php';
    require_once 'classes/BlogPost.php';
    //require_once 'classes/Authentication.php';

    $db = new Database();
    $blogPost = new BlogPost($db);

    // To handle search and paginaion.....
    $search = $_GET['search'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 5;
    $offset = ($page - 1 ) * $limit;

    //Get posts with search and pagination.....
    $posts = $blogPost->getAllPosts($search, $limit, $offset);
    $totalPost = $blogPost->getTotalPosts($search);
    $totalPages = ceil($totalPost / $limit);

    $pageTitle = "Blog Home";
    include 'views/footer.php';
?>

<div class="hero">
    <h1>Welcome to Tactology Blog</h1>
    <p>Discover amazing stories and insights from our community</p>
</div>
<link rel="stylesheet" href="assets/styles.css">
<div class="search-section">
    <form action="" method="GET" class="search-form">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search posts....." class="search-input">
        <button type="submit" class="search-btn">Search</button>
        <?php if ($search): ?>
            <a href="index.php" class="clear-search">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="posts-container">
    <h2>Latest Posts</h2>
    <?php if (empty($posts)): ?>
        <p class="no-post"> No post found. <?= $search ? 'Try a different search term.' : '' ?></p>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <h3><a href="post.php=<?= ($post['id'])?> "><?= htmlspecialchars($post['title']) ?></a></h3>
                    <div class="post-meta">
                        <span>By <?= htmlspecialchars($post['username']) ?></span>
                        <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                    </div>
                    <p class="post-excerpt"><?= htmlspecialchars(substr($post['body'], 0, 150)) ?>....</p>
                    <a href="post.php?id=<?= $post['id'] ?>" class="read-more"></a>
                </article>
            <?php endforeach; ?>
        </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if( $page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $search ? '&search=' . urldecode($search) : '' ?>" class="<?= $i == $page ? 'active' : '' ?><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totlaPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $search ? '&search='  . urlencode($search) : '' ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<script src="assets/script.js"></script>
<?php include 'views/footer.php'; ?>