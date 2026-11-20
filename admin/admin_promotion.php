<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

function adminRoleLabel(string $role): string
{
    return ucwords(str_replace('_', ' ', $role));
}

function promotionTypeLabel(string $type): string
{
    return $type === 'video' ? 'Video Promotion' : 'Image Promotion';
}

function promotionTypeClass(string $type): string
{
    return $type === 'video' ? 'is-video' : 'is-image';
}

function promotionMediaPath(string $file_name): string
{
    return '../images/' . ltrim($file_name, '/\\');
}

if (!isset($_SESSION['promotion_flash'])) {
    $_SESSION['promotion_flash'] = null;
}

$promotion_flash = $_SESSION['promotion_flash'];
$_SESSION['promotion_flash'] = null;

if (isset($_POST['submit'])) {
    $type = trim($_POST['type'] ?? 'image');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $allowed_types = ['image', 'video'];
    $upload_error = $_FILES['media']['error'] ?? UPLOAD_ERR_NO_FILE;

    if (!in_array($type, $allowed_types, true)) {
        $_SESSION['promotion_flash'] = ['type' => 'error', 'text' => 'Invalid promotion type selected.'];
        header('Location: admin_promotion.php');
        exit;
    }

    if ($upload_error !== UPLOAD_ERR_OK) {
        $_SESSION['promotion_flash'] = ['type' => 'error', 'text' => 'Please choose a media file to upload.'];
        header('Location: admin_promotion.php');
        exit;
    }

    $media_name = $_FILES['media']['name'] ?? '';
    $media_tmp_name = $_FILES['media']['tmp_name'] ?? '';
    $media_extension = strtolower(pathinfo($media_name, PATHINFO_EXTENSION));

    $allowed_extensions = $type === 'video'
        ? ['mp4', 'webm', 'ogg']
        : ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($media_extension, $allowed_extensions, true)) {
        $_SESSION['promotion_flash'] = ['type' => 'error', 'text' => 'The selected file type is not allowed for this promotion type.'];
        header('Location: admin_promotion.php');
        exit;
    }

    if (!is_dir('../images')) {
        mkdir('../images', 0777, true);
    }

    $safe_file_name = uniqid('promotion_', true) . '.' . $media_extension;
    $media_folder = '../images/' . $safe_file_name;

    if (!move_uploaded_file($media_tmp_name, $media_folder)) {
        $_SESSION['promotion_flash'] = ['type' => 'error', 'text' => 'Failed to upload media. Please try again.'];
        header('Location: admin_promotion.php');
        exit;
    }

    $insert_promotion = $conn->prepare(
        "INSERT INTO promotions (type, media_path, title, description) VALUES (?, ?, ?, ?)"
    );
    $insert_promotion->execute([$type, $safe_file_name, $title, $description]);

    $_SESSION['promotion_flash'] = ['type' => 'success', 'text' => 'Promotion added successfully.'];
    header('Location: admin_promotion.php');
    exit;
}

if (isset($_POST['delete']) && isset($_POST['promotion_id'])) {
    $promotion_id = (int) $_POST['promotion_id'];

    $promotion_stmt = $conn->prepare("SELECT media_path FROM promotions WHERE id = ?");
    $promotion_stmt->execute([$promotion_id]);
    $promotion = $promotion_stmt->fetch(PDO::FETCH_ASSOC);

    $delete_promotion = $conn->prepare("DELETE FROM promotions WHERE id = ?");
    $delete_promotion->execute([$promotion_id]);

    if ($promotion && !empty($promotion['media_path'])) {
        $media_file = promotionMediaPath($promotion['media_path']);
        if (is_file($media_file)) {
            @unlink($media_file);
        }
    }

    $_SESSION['promotion_flash'] = ['type' => 'success', 'text' => 'Promotion deleted successfully.'];
    header('Location: admin_promotion.php');
    exit;
}

$profile_stmt = $conn->prepare("SELECT name, admin_role FROM `admins` WHERE id = ?");
$profile_stmt->execute([$admin_id]);
$current_admin = $profile_stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Admin', 'admin_role' => 'super_admin'];

$select_promotions = $conn->prepare("SELECT * FROM promotions ORDER BY created_at DESC, id DESC");
$select_promotions->execute();
$promotions = $select_promotions->fetchAll(PDO::FETCH_ASSOC);

$promotion_count = count($promotions);
$image_count = count(array_filter($promotions, static fn ($promotion) => $promotion['type'] === 'image'));
$video_count = count(array_filter($promotions, static fn ($promotion) => $promotion['type'] === 'video'));
$display_date = date('M d, Y');
$current_admin_role = adminRoleLabel($current_admin['admin_role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Promotion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap');

        .promotion-page {
            max-width: 1600px;
            margin: 2.4rem auto 3.2rem;
            padding: 0 2rem;
        }

        .promotion-shell {
            position: relative;
            padding: 2.4rem;
            border: 1px solid rgba(255, 62, 62, 0.28);
            border-radius: 2.2rem;
            background:
                linear-gradient(180deg, rgba(13, 13, 15, 0.97), rgba(8, 8, 10, 0.97)),
                #0b0b0d;
            box-shadow:
                0 2rem 5rem rgba(0, 0, 0, 0.42),
                inset 0 0 0 1px rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .promotion-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(255, 44, 60, 0.08) 0%, transparent 18%),
                linear-gradient(145deg, transparent 70%, rgba(255, 44, 60, 0.06) 71%, transparent 72%);
        }

        .promotion-top,
        .promotion-flash,
        .promotion-layout,
        .promotion-gallery {
            position: relative;
            z-index: 1;
        }

        .promotion-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.8rem;
            margin-bottom: 1.8rem;
        }

        .promotion-title {
            margin: 0;
            font-family: 'Rajdhani', sans-serif;
            font-size: 4rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #f3f3f3;
            text-transform: uppercase;
        }

        .promotion-title span {
            color: #ff2c3c;
        }

        .promotion-subtitle {
            margin-top: 0.6rem;
            font-size: 1.45rem;
            color: #a0a0a0;
        }

        .promotion-meta {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .promotion-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            min-height: 4.8rem;
            padding: 0 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.2rem;
            background: rgba(18, 18, 21, 0.94);
            color: #d6d6d6;
            font-size: 1.35rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .promotion-chip i {
            color: #ff2c3c;
        }

        .promotion-chip--admin {
            padding-right: 1.8rem;
            border-color: rgba(255, 44, 60, 0.45);
        }

        .promotion-chip__avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border: 1px solid rgba(255, 44, 60, 0.4);
            border-radius: 999px;
            background: rgba(255, 44, 60, 0.08);
        }

        .promotion-chip__text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .promotion-chip__text strong {
            color: #f4f4f4;
            font-size: 1.35rem;
        }

        .promotion-chip__text small {
            color: #9c9c9c;
            font-size: 1.15rem;
        }

        .promotion-flash {
            margin-bottom: 1.8rem;
            padding: 1.4rem 1.6rem;
            border-radius: 1.2rem;
            font-size: 1.35rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .promotion-flash.is-success {
            color: #65d98d;
            background: rgba(53, 125, 74, 0.16);
            border-color: rgba(101, 217, 141, 0.22);
        }

        .promotion-flash.is-error {
            color: #ff8e95;
            background: rgba(159, 29, 43, 0.18);
            border-color: rgba(255, 77, 96, 0.24);
        }

        .promotion-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(30rem, 1fr);
            gap: 1.8rem;
            margin-bottom: 2rem;
        }

        .promotion-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, rgba(18, 18, 21, 0.97), rgba(12, 12, 15, 0.97));
            box-shadow:
                0 1.6rem 3rem rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .promotion-card__header {
            padding: 1.6rem 1.8rem 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .promotion-card__header h2 {
            margin: 0;
            font-family: 'Rajdhani', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #f5f5f5;
        }

        .promotion-card__header p {
            margin-top: 0.5rem;
            font-size: 1.3rem;
            color: #969696;
        }

        .promotion-form {
            padding: 1.8rem;
        }

        .promotion-form__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.4rem;
        }

        .promotion-field {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }

        .promotion-field--full {
            grid-column: 1 / -1;
        }

        .promotion-label {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #d1d1d1;
        }

        .promotion-input,
        .promotion-select,
        .promotion-textarea,
        .promotion-file {
            width: 100%;
            min-height: 4.8rem;
            padding: 0 1.4rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: rgba(22, 22, 24, 0.95);
            color: #f3f3f3;
            font-size: 1.4rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .promotion-input::placeholder,
        .promotion-textarea::placeholder {
            color: #8a8a8a;
        }

        .promotion-select option {
            color: #111111;
        }

        .promotion-textarea {
            min-height: 14rem;
            padding-top: 1.2rem;
            padding-bottom: 1.2rem;
            resize: vertical;
        }

        .promotion-file {
            display: flex;
            align-items: center;
            padding-top: 1.1rem;
            padding-bottom: 1.1rem;
        }

        .promotion-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            min-height: 4.8rem;
            margin-top: 1.6rem;
            padding: 0 2rem;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ff2436 0%, #af0819 100%);
            color: #ffffff;
            font-size: 1.45rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 1rem 2rem rgba(175, 8, 25, 0.3);
        }

        .promotion-stats {
            display: grid;
            gap: 1.2rem;
            padding: 1.8rem;
        }

        .promotion-stat {
            padding: 1.4rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.03);
        }

        .promotion-stat__label {
            font-size: 1.2rem;
            color: #9c9c9c;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .promotion-stat__value {
            margin-top: 0.6rem;
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        .promotion-stat__hint {
            margin-top: 0.4rem;
            font-size: 1.2rem;
            color: #8a8a8a;
            line-height: 1.5;
        }

        .promotion-gallery__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.4rem;
        }

        .promotion-gallery__header h2 {
            margin: 0;
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #f5f5f5;
        }

        .promotion-gallery__header p {
            font-size: 1.3rem;
            color: #979797;
        }

        .promotion-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.6rem;
        }

        .promotion-item {
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, rgba(18, 18, 21, 0.97), rgba(12, 12, 15, 0.97));
            box-shadow:
                0 1.6rem 3rem rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .promotion-item__media {
            position: relative;
            aspect-ratio: 16 / 10;
            background: rgba(255, 255, 255, 0.03);
            overflow: hidden;
        }

        .promotion-item__media img,
        .promotion-item__media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .promotion-item__badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            min-height: 3rem;
            padding: 0 0.9rem;
            border-radius: 999px;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            backdrop-filter: blur(8px);
        }

        .promotion-item__badge.is-image {
            background: rgba(255, 44, 60, 0.14);
            border: 1px solid rgba(255, 44, 60, 0.25);
            color: #ff8690;
        }

        .promotion-item__badge.is-video {
            background: rgba(111, 177, 255, 0.14);
            border: 1px solid rgba(111, 177, 255, 0.24);
            color: #8ec1ff;
        }

        .promotion-item__body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1.5rem;
            height: 100%;
        }

        .promotion-item__title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: #ffffff;
        }

        .promotion-item__description {
            font-size: 1.3rem;
            line-height: 1.6;
            color: #b1b1b1;
        }

        .promotion-item__meta {
            margin-top: auto;
            font-size: 1.15rem;
            color: #818181;
        }

        .promotion-item__delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            min-height: 4.2rem;
            margin-top: 1rem;
            border-radius: 0.9rem;
            background: linear-gradient(180deg, #e21d31 0%, #aa0818 100%);
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 700;
            cursor: pointer;
        }

        .promotion-empty {
            padding: 3.2rem 2rem;
            border: 1px dashed rgba(255, 255, 255, 0.14);
            border-radius: 1.4rem;
            text-align: center;
            color: #9b9b9b;
            font-size: 1.5rem;
        }

        @media (max-width: 1180px) {
            .promotion-layout,
            .promotion-grid {
                grid-template-columns: 1fr;
            }

            .promotion-form__grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 820px) {
            .promotion-shell {
                padding: 1.8rem;
            }

            .promotion-top {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 520px) {
            .promotion-page {
                padding: 0 1.2rem;
            }

            .promotion-shell {
                padding: 1.5rem;
                border-radius: 1.6rem;
            }

            .promotion-title {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<main class="promotion-page">
    <section class="promotion-shell">
        <div class="promotion-top">
            <div>
                <h1 class="promotion-title">Add New <span>Promotion</span></h1>
                <p class="promotion-subtitle">Create homepage promotions with polished media, titles, and supporting copy.</p>
            </div>

            <div class="promotion-meta">
                <div class="promotion-chip">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span><?= htmlspecialchars($display_date); ?></span>
                </div>

                <div class="promotion-chip promotion-chip--admin">
                    <span class="promotion-chip__avatar"><i class="fa-regular fa-user"></i></span>
                    <span class="promotion-chip__text">
                        <strong><?= htmlspecialchars($current_admin['name']); ?></strong>
                        <small><?= htmlspecialchars($current_admin_role); ?></small>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($promotion_flash): ?>
            <div class="promotion-flash is-<?= htmlspecialchars($promotion_flash['type']); ?>">
                <?= htmlspecialchars($promotion_flash['text']); ?>
            </div>
        <?php endif; ?>

        <div class="promotion-layout">
            <section class="promotion-card">
                <div class="promotion-card__header">
                    <h2>Promotion Builder</h2>
                    <p>Fill out the details below and upload the campaign media you want to publish.</p>
                </div>

                <form action="" method="post" enctype="multipart/form-data" class="promotion-form">
                    <div class="promotion-form__grid">
                        <label class="promotion-field">
                            <span class="promotion-label">Promotion Type</span>
                            <select name="type" class="promotion-select" required>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                            </select>
                        </label>

                        <label class="promotion-field">
                            <span class="promotion-label">Media Upload</span>
                            <input type="file" name="media" class="promotion-file" accept="image/*,video/*" required>
                        </label>

                        <label class="promotion-field promotion-field--full">
                            <span class="promotion-label">Promotion Title</span>
                            <input type="text" name="title" class="promotion-input" placeholder="Enter a promotion title" maxlength="100">
                        </label>

                        <label class="promotion-field promotion-field--full">
                            <span class="promotion-label">Description</span>
                            <textarea name="description" class="promotion-textarea" placeholder="Enter a short promotional description" maxlength="500"></textarea>
                        </label>
                    </div>

                    <button type="submit" name="submit" class="promotion-submit">
                        <i class="fas fa-plus"></i>
                        <span>Add Promotion</span>
                    </button>
                </form>
            </section>

            <aside class="promotion-card">
                <div class="promotion-card__header">
                    <h2>Promotion Overview</h2>
                    <p>Quick stats for the current promotion library.</p>
                </div>

                <div class="promotion-stats">
                    <div class="promotion-stat">
                        <div class="promotion-stat__label">Total Promotions</div>
                        <div class="promotion-stat__value"><?= htmlspecialchars((string) $promotion_count); ?></div>
                        <div class="promotion-stat__hint">All published promotions currently stored in the system.</div>
                    </div>

                    <div class="promotion-stat">
                        <div class="promotion-stat__label">Image Promotions</div>
                        <div class="promotion-stat__value"><?= htmlspecialchars((string) $image_count); ?></div>
                        <div class="promotion-stat__hint">Best for banners, featured product visuals, and quick announcements.</div>
                    </div>

                    <div class="promotion-stat">
                        <div class="promotion-stat__label">Video Promotions</div>
                        <div class="promotion-stat__value"><?= htmlspecialchars((string) $video_count); ?></div>
                        <div class="promotion-stat__hint">Great for animated campaigns, promos, and spotlight reels.</div>
                    </div>
                </div>
            </aside>
        </div>

        <section class="promotion-gallery">
            <div class="promotion-gallery__header">
                <div>
                    <h2>Promotion Library</h2>
                    <p>Review existing promotions and remove anything you no longer want published.</p>
                </div>
            </div>

            <?php if ($promotions): ?>
                <div class="promotion-grid">
                    <?php foreach ($promotions as $promotion): ?>
                        <?php $media_path = promotionMediaPath($promotion['media_path']); ?>
                        <article class="promotion-item">
                            <div class="promotion-item__media">
                                <span class="promotion-item__badge <?= promotionTypeClass($promotion['type']); ?>">
                                    <i class="fas <?= $promotion['type'] === 'video' ? 'fa-circle-play' : 'fa-image'; ?>"></i>
                                    <span><?= htmlspecialchars(promotionTypeLabel($promotion['type'])); ?></span>
                                </span>

                                <?php if ($promotion['type'] === 'video'): ?>
                                    <video controls preload="metadata">
                                        <source src="<?= htmlspecialchars($media_path); ?>" type="video/<?= htmlspecialchars(pathinfo($promotion['media_path'], PATHINFO_EXTENSION)); ?>">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($media_path); ?>" alt="<?= htmlspecialchars($promotion['title'] ?: 'Promotion image'); ?>">
                                <?php endif; ?>
                            </div>

                            <div class="promotion-item__body">
                                <div class="promotion-item__title"><?= htmlspecialchars($promotion['title'] ?: 'Untitled Promotion'); ?></div>
                                <div class="promotion-item__description"><?= nl2br(htmlspecialchars($promotion['description'] ?: 'No description provided.')); ?></div>
                                <div class="promotion-item__meta">
                                    <?= !empty($promotion['created_at']) ? htmlspecialchars($promotion['created_at']) : 'Recently added'; ?>
                                </div>

                                <form method="post" onsubmit="return confirm('Delete this promotion?');">
                                    <input type="hidden" name="promotion_id" value="<?= htmlspecialchars((string) $promotion['id']); ?>">
                                    <button type="submit" name="delete" class="promotion-item__delete">
                                        <i class="fas fa-trash"></i>
                                        <span>Delete Promotion</span>
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="promotion-empty">No promotions added yet. Create your first promotion using the form above.</div>
            <?php endif; ?>
        </section>
    </section>
</main>

<script src="../js/admin_script.js"></script>

</body>
</html>
