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

function adminRoleClass(string $role): string
{
    return match ($role) {
        'super_admin' => 'is-super',
        'order_admin' => 'is-order',
        'delivery_admin' => 'is-delivery',
        'reports_admin' => 'is-reports',
        default => 'is-default',
    };
}

$search = trim($_GET['search'] ?? '');
$role_filter = trim($_GET['role'] ?? '');

if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $delete_admins = $conn->prepare("DELETE FROM `admins` WHERE id = ?");
    $delete_admins->execute([$delete_id]);

    $redirect_params = [];
    if ($search !== '') {
        $redirect_params['search'] = $search;
    }
    if ($role_filter !== '') {
        $redirect_params['role'] = $role_filter;
    }

    $redirect = 'admin_accounts.php';
    if ($redirect_params) {
        $redirect .= '?' . http_build_query($redirect_params);
    }

    header('location:' . $redirect);
    exit;
}

$profile_stmt = $conn->prepare("SELECT name, admin_role FROM `admins` WHERE id = ?");
$profile_stmt->execute([$admin_id]);
$current_admin = $profile_stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Admin', 'admin_role' => 'super_admin'];

$role_options_stmt = $conn->prepare("SELECT DISTINCT admin_role FROM `admins` ORDER BY admin_role ASC");
$role_options_stmt->execute();
$role_options = $role_options_stmt->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT * FROM `admins` WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (`name` LIKE ? OR CAST(`id` AS CHAR) LIKE ? OR `admin_role` LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($role_filter !== '') {
    $query .= " AND `admin_role` = ?";
    $params[] = $role_filter;
}

$query .= " ORDER BY `id` ASC";

$select_accounts = $conn->prepare($query);
$select_accounts->execute($params);
$admin_accounts = $select_accounts->fetchAll(PDO::FETCH_ASSOC);

$display_date = date('M d, Y');
$current_admin_role = adminRoleLabel($current_admin['admin_role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap');

        .admin-accounts-page {
            max-width: 1600px;
            margin: 2.4rem auto 3.2rem;
            padding: 0 2rem;
        }

        .admin-accounts-shell {
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

        .admin-accounts-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(255, 44, 60, 0.08) 0%, transparent 18%),
                linear-gradient(145deg, transparent 70%, rgba(255, 44, 60, 0.06) 71%, transparent 72%);
        }

        .accounts-top,
        .accounts-toolbar,
        .accounts-grid {
            position: relative;
            z-index: 1;
        }

        .accounts-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.8rem;
            margin-bottom: 1.8rem;
        }

        .accounts-title {
            margin: 0;
            font-family: 'Rajdhani', sans-serif;
            font-size: 4rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #f3f3f3;
            text-transform: uppercase;
        }

        .accounts-title span {
            color: #ff2c3c;
        }

        .accounts-subtitle {
            margin-top: 0.6rem;
            font-size: 1.45rem;
            color: #a0a0a0;
        }

        .accounts-meta {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .accounts-chip {
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

        .accounts-chip i {
            color: #ff2c3c;
        }

        .accounts-chip--admin {
            padding-right: 1.8rem;
            border-color: rgba(255, 44, 60, 0.45);
        }

        .accounts-chip__avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border: 1px solid rgba(255, 44, 60, 0.4);
            border-radius: 999px;
            background: rgba(255, 44, 60, 0.08);
        }

        .accounts-chip__text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .accounts-chip__text strong {
            color: #f4f4f4;
            font-size: 1.35rem;
        }

        .accounts-chip__text small {
            color: #9c9c9c;
            font-size: 1.15rem;
        }

        .accounts-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.8rem;
        }

        .accounts-filter-form {
            display: flex;
            align-items: stretch;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .accounts-search,
        .accounts-role {
            display: flex;
            align-items: center;
            min-height: 4.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: rgba(22, 22, 24, 0.95);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .accounts-search {
            min-width: 0;
            width: 38rem;
            padding: 0 1.4rem;
            gap: 1rem;
        }

        .accounts-search i,
        .accounts-role i {
            color: #9d9d9d;
            font-size: 1.35rem;
        }

        .accounts-search input,
        .accounts-role select {
            width: 100%;
            background: transparent;
            color: #f3f3f3;
            font-size: 1.35rem;
        }

        .accounts-search input::placeholder {
            color: #8c8c8c;
        }

        .accounts-role {
            width: 18rem;
            padding: 0 1.3rem;
            gap: 0.9rem;
        }

        .accounts-role select option {
            color: #111111;
        }

        .accounts-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.6rem;
        }

        .admin-card,
        .admin-card--add {
            display: flex;
            flex-direction: column;
            min-height: 22rem;
            padding: 1.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, rgba(18, 18, 21, 0.97), rgba(12, 12, 15, 0.97));
            box-shadow:
                0 1.6rem 3rem rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .admin-card--add {
            justify-content: center;
            border-style: dashed;
            border-color: rgba(255, 44, 60, 0.45);
            background:
                radial-gradient(circle at top left, rgba(255, 44, 60, 0.08), transparent 20rem),
                linear-gradient(180deg, rgba(18, 18, 21, 0.97), rgba(12, 12, 15, 0.97));
        }

        .admin-card__inner {
            display: flex;
            gap: 1.6rem;
            align-items: flex-start;
        }

        .admin-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 5.4rem;
            height: 5.4rem;
            border: 1px solid rgba(255, 44, 60, 0.45);
            border-radius: 999px;
            background: rgba(255, 44, 60, 0.06);
            color: #ff3a4a;
            font-size: 2rem;
            box-shadow: 0 0 1.8rem rgba(255, 44, 60, 0.1);
        }

        .admin-card__content {
            flex: 1 1 auto;
        }

        .admin-card__heading {
            margin: 0;
            font-family: 'Rajdhani', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
        }

        .admin-card__subheading {
            margin-top: 0.5rem;
            font-size: 1.3rem;
            color: #9a9a9a;
        }

        .admin-card__register {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            min-height: 4.4rem;
            margin-top: 1.6rem;
            padding: 0 1.8rem;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ff2436 0%, #af0819 100%);
            color: #ffffff;
            font-size: 1.45rem;
            font-weight: 700;
            box-shadow: 0 1rem 2rem rgba(175, 8, 25, 0.3);
        }

        .admin-card__meta {
            margin-top: 0.2rem;
            font-size: 1.45rem;
            line-height: 1.55;
            color: #d4d4d4;
        }

        .admin-card__meta strong {
            color: #9c9c9c;
            font-weight: 500;
        }

        .admin-card__meta span {
            color: #ff303f;
            font-weight: 700;
        }

        .admin-card__role {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            min-height: 3.4rem;
            margin-top: 1rem;
            padding: 0 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.9rem;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: lowercase;
            width: fit-content;
        }

        .admin-card__role i {
            font-size: 1.1rem;
        }

        .admin-card__role.is-super {
            color: #ff707a;
            border-color: rgba(255, 44, 60, 0.32);
            background: rgba(255, 44, 60, 0.07);
        }

        .admin-card__role.is-order {
            color: #ffb85c;
            border-color: rgba(255, 184, 92, 0.26);
            background: rgba(255, 184, 92, 0.07);
        }

        .admin-card__role.is-delivery {
            color: #6fb1ff;
            border-color: rgba(111, 177, 255, 0.28);
            background: rgba(111, 177, 255, 0.08);
        }

        .admin-card__role.is-reports {
            color: #b77dff;
            border-color: rgba(183, 125, 255, 0.28);
            background: rgba(183, 125, 255, 0.08);
        }

        .admin-card__role.is-default {
            color: #c8c8c8;
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-card__actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: auto;
            padding-top: 1.6rem;
        }

        .admin-card__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            min-height: 4.3rem;
            border-radius: 0.9rem;
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
        }

        .admin-card__action--delete {
            background: linear-gradient(180deg, #e21d31 0%, #aa0818 100%);
        }

        .admin-card__action--update {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.03);
            color: #d8d8d8;
        }

        .accounts-empty {
            grid-column: 1 / -1;
            padding: 3.2rem 2rem;
            border: 1px dashed rgba(255, 255, 255, 0.14);
            border-radius: 1.4rem;
            text-align: center;
            color: #9b9b9b;
            font-size: 1.5rem;
        }

        @media (max-width: 1180px) {
            .accounts-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .admin-accounts-shell {
                padding: 1.8rem;
            }

            .accounts-top,
            .accounts-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .accounts-filter-form {
                width: 100%;
            }

            .accounts-search,
            .accounts-role {
                width: 100%;
            }

            .accounts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .admin-accounts-page {
                padding: 0 1.2rem;
            }

            .admin-accounts-shell {
                padding: 1.5rem;
                border-radius: 1.6rem;
            }

            .accounts-title {
                font-size: 3rem;
            }

            .admin-card,
            .admin-card--add {
                padding: 1.5rem;
            }

            .admin-card__actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<main class="admin-accounts-page">
    <section class="admin-accounts-shell">
        <div class="accounts-top">
            <div>
                <h1 class="accounts-title">Admin <span>Accounts</span></h1>
                <p class="accounts-subtitle">Manage store administrators, roles, and permissions</p>
            </div>

            <div class="accounts-meta">
                <div class="accounts-chip">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span><?= htmlspecialchars($display_date); ?></span>
                </div>

                <div class="accounts-chip accounts-chip--admin">
                    <span class="accounts-chip__avatar"><i class="fa-regular fa-user"></i></span>
                    <span class="accounts-chip__text">
                        <strong><?= htmlspecialchars($current_admin['name']); ?></strong>
                        <small><?= htmlspecialchars($current_admin_role); ?></small>
                    </span>
                </div>
            </div>
        </div>

        <div class="accounts-toolbar">
            <form action="admin_accounts.php" method="GET" class="accounts-filter-form">
                <label class="accounts-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search admins...">
                </label>

                <label class="accounts-role">
                    <i class="fas fa-shield-halved"></i>
                    <select name="role" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <?php foreach ($role_options as $role_option): ?>
                            <option value="<?= htmlspecialchars($role_option); ?>" <?= $role_filter === $role_option ? 'selected' : ''; ?>>
                                <?= htmlspecialchars(adminRoleLabel($role_option)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>
        </div>

        <div class="accounts-grid">
            <article class="admin-card--add">
                <div class="admin-card__inner">
                    <div class="admin-card__icon">
                        <i class="fas fa-plus"></i>
                    </div>

                    <div class="admin-card__content">
                        <h2 class="admin-card__heading">Add New Admin</h2>
                        <p class="admin-card__subheading">Create a new administrator account</p>
                        <a href="register_admin.php" class="admin-card__register">
                            <i class="fas fa-plus"></i>
                            <span>Register Admin</span>
                        </a>
                    </div>
                </div>
            </article>

            <?php if ($admin_accounts): ?>
                <?php foreach ($admin_accounts as $account): ?>
                    <?php
                    $query_params = [];
                    if ($search !== '') {
                        $query_params['search'] = $search;
                    }
                    if ($role_filter !== '') {
                        $query_params['role'] = $role_filter;
                    }
                    $query_params['delete'] = $account['id'];
                    $delete_link = 'admin_accounts.php?' . http_build_query($query_params);
                    ?>
                    <article class="admin-card">
                        <div class="admin-card__inner">
                            <div class="admin-card__icon">
                                <i class="far fa-user"></i>
                            </div>

                            <div class="admin-card__content">
                                <div class="admin-card__meta"><strong>Admin ID :</strong> <span><?= htmlspecialchars((string) $account['id']); ?></span></div>
                                <div class="admin-card__meta"><strong>Admin Name :</strong> <span><?= htmlspecialchars($account['name']); ?></span></div>
                                <div class="admin-card__role <?= adminRoleClass($account['admin_role']); ?>">
                                    <i class="fas fa-shield"></i>
                                    <span><?= htmlspecialchars($account['admin_role']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="admin-card__actions">
                            <a href="<?= htmlspecialchars($delete_link); ?>" onclick="return confirm('Delete this admin account?');" class="admin-card__action admin-card__action--delete">
                                <i class="fas fa-trash"></i>
                                <span>Delete</span>
                            </a>
                            <a href="update_profile.php?id=<?= htmlspecialchars((string) $account['id']); ?>" class="admin-card__action admin-card__action--update">
                                <i class="fas fa-pen-to-square"></i>
                                <span>Update</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="accounts-empty">No admin accounts matched your current filter.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="../js/admin_script.js"></script>

</body>
</html>
