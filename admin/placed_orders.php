<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

function format_payment_status_label(string $status): string
{
    $labels = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'canceled' => 'Canceled',
        'pendingCancellation' => 'Pending Cancellation',
        'payment approved' => 'Payment Approved',
        'to receive' => 'To Receive',
        'returned/refunded' => 'Returned/Refunded',
        'pendingreturn' => 'Pending Return/Refund',
    ];

    return $labels[$status] ?? ucwords(str_replace(['_', '-'], ' ', $status));
}

function payment_status_badge_class(string $status): string
{
    return match ($status) {
        'canceled' => 'is-canceled',
        'pendingCancellation', 'pendingreturn' => 'is-pending',
        'returned/refunded' => 'is-returned',
        'paid', 'payment approved', 'to receive' => 'is-success',
        default => 'is-active',
    };
}

if (isset($_GET['cancel'])) {
    $cancel_id = (int) $_GET['cancel'];

    $select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
    $select_order->execute([$cancel_id]);

    if ($select_order->rowCount() > 0) {
        $fetch_order = $select_order->fetch(PDO::FETCH_ASSOC);

        $insert_order_history = $conn->prepare(
            "INSERT INTO `delivery_reports` (order_id, delivery_status, delivered_on) VALUES (?, ?, NOW())"
        );
        $insert_order_history->execute([$fetch_order['id'], 'Cancelled']);

        $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
        $delete_order->execute([$cancel_id]);
    }

    header('location:placed_orders.php');
    exit;
}

if (isset($_POST['update_payment_status'])) {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $new_payment_status = trim($_POST['payment_status'] ?? 'pending');

    $update_payment_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
    $update_payment_status->execute([$new_payment_status, $order_id]);

    header('location:placed_orders.php');
    exit;
}

$status_options = [
    'pending',
    'paid',
    'canceled',
    'pendingCancellation',
    'payment approved',
    'to receive',
    'returned/refunded',
    'pendingreturn',
];

$select_orders = $conn->prepare("SELECT * FROM `orders` ORDER BY `placed_on` DESC");
$select_orders->execute();
$orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placed Orders</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">

    <style>
        .orders-page {
            max-width: 1600px;
            margin: 2.4rem auto 3.2rem;
            padding: 0 2rem;
        }

        .orders-shell {
            position: relative;
            border: 1px solid rgba(255, 62, 62, 0.28);
            border-radius: 2rem;
            background:
                linear-gradient(180deg, rgba(13, 13, 15, 0.97), rgba(8, 8, 10, 0.97)),
                #0b0b0d;
            box-shadow:
                0 2rem 5rem rgba(0, 0, 0, 0.38),
                inset 0 0 0 1px rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .orders-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(255, 44, 60, 0.08) 0%, transparent 18%),
                linear-gradient(145deg, transparent 72%, rgba(255, 44, 60, 0.06) 73%, transparent 74%);
        }

        .orders-header,
        .orders-table-wrap {
            position: relative;
            z-index: 1;
        }

        .orders-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1.6rem;
            padding: 2.4rem 2.6rem 1.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .orders-title {
            margin: 0;
            font-size: 3.6rem;
            font-weight: 700;
            color: #f3f3f3;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .orders-title span {
            color: #ff2c3c;
        }

        .orders-subtitle {
            margin-top: 0.7rem;
            font-size: 1.4rem;
            color: #9a9a9a;
        }

        .orders-summary {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            min-height: 4.6rem;
            padding: 0 1.4rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.2rem;
            background: rgba(18, 18, 21, 0.94);
            color: #d9d9d9;
            font-size: 1.35rem;
        }

        .orders-summary i {
            color: #ff2c3c;
        }

        .orders-table-area {
            padding: 2rem;
        }

        .orders-table-wrap {
            overflow-x: hidden;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 1.4rem;
            overflow: hidden;
            background: rgba(10, 10, 12, 0.78);
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
            text-align: left;
            vertical-align: top;
            font-size: 1.2rem;
            color: #e5e5e5;
            word-break: break-word;
        }

        .orders-table th {
            background: linear-gradient(180deg, #d7192f, #8f0916);
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.15;
        }

        .orders-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.025);
        }

        .orders-table tbody tr:hover {
            background: rgba(255, 44, 60, 0.05);
        }

        .orders-table td:nth-child(1),
        .orders-table td:nth-child(2),
        .orders-table td:nth-child(8),
        .orders-table td:nth-child(9) {
            white-space: nowrap;
        }

        .orders-address,
        .orders-products {
            max-width: 28rem;
            line-height: 1.5;
            color: #d0d0d0;
        }

        .orders-col-id,
        .orders-col-user-id,
        .orders-col-date,
        .orders-col-name,
        .orders-col-number,
        .orders-col-price,
        .orders-col-method {
            white-space: nowrap;
        }

        .orders-col-address {
            line-height: 1.4;
        }

        .orders-col-products {
            line-height: 1.4;
        }

        .orders-col-payment-status {
            min-width: 0;
        }

        .orders-col-cancel-status {
            min-width: 0;
        }

        .payment-status-form {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            width: 100%;
        }

        .payment-select {
            width: 100%;
            min-height: 3.8rem;
            padding: 0 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.04);
            color: #f4f4f4;
            font-size: 1.2rem;
        }

        .payment-select option {
            color: #111111;
        }

        .update-btn {
            min-height: 3.6rem;
            padding: 0 1rem;
            border: 1px solid rgba(255, 82, 82, 0.35);
            border-radius: 1rem;
            background: linear-gradient(180deg, #ff2b3f, #a20818);
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .update-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 1rem 1.8rem rgba(160, 8, 24, 0.28);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.2rem;
            padding: 0.4rem 0.8rem;
            border: 1px solid transparent;
            border-radius: 999px;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: normal;
            text-align: center;
            line-height: 1.2;
        }

        .status-badge.is-canceled {
            color: #ff7c7c;
            background: rgba(255, 74, 89, 0.1);
            border-color: rgba(255, 74, 89, 0.26);
        }

        .status-badge.is-pending {
            color: #ffb347;
            background: rgba(255, 179, 71, 0.12);
            border-color: rgba(255, 179, 71, 0.24);
        }

        .status-badge.is-returned {
            color: #8fc6ff;
            background: rgba(143, 198, 255, 0.12);
            border-color: rgba(143, 198, 255, 0.22);
        }

        .status-badge.is-success,
        .status-badge.is-active {
            color: #54da83;
            background: rgba(84, 218, 131, 0.1);
            border-color: rgba(84, 218, 131, 0.22);
        }

        .orders-empty {
            padding: 3rem 1.4rem !important;
            text-align: center !important;
            color: #a1a1a1 !important;
        }

        @media (max-width: 720px) {
            .orders-page {
                padding: 0 1.2rem;
            }

            .orders-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 2rem 1.6rem 1.4rem;
            }

            .orders-title {
                font-size: 3rem;
            }

            .orders-table-wrap {
                padding: 1.2rem;
            }
        }
    </style>

</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <section class="orders-page">
        <div class="orders-shell">
            <div class="orders-header">
                <div>
                    <h1 class="orders-title">Placed <span>Orders</span></h1>
                    <p class="orders-subtitle">Review customer orders and update their payment state.</p>
                </div>

                <div class="orders-summary">
                    <i class="fas fa-bag-shopping"></i>
                    <span><?= count($orders); ?> total order(s)</span>
                </div>
            </div>

            <div class="orders-table-area">
                <div class="orders-table-wrap">
                <table class="orders-table">
                    <colgroup>
                        <col style="width: 2.5%;">
                        <col style="width: 5.2%;">
                        <col style="width: 7%;">
                        <col style="width: 9.5%;">
                        <col style="width: 7.5%;">
                        <col style="width: 17%;">
                        <col style="width: 13%;">
                        <col style="width: 7.5%;">
                        <col style="width: 10.5%;">
                        <col style="width: 13.5%;">
                        <col style="width: 6.8%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="orders-col-id">ID</th>
                            <th class="orders-col-user-id">User ID</th>
                            <th class="orders-col-date">Placed On</th>
                            <th class="orders-col-name">Name</th>
                            <th class="orders-col-number">Number</th>
                            <th class="orders-col-address">Address</th>
                            <th class="orders-col-products">Total Products</th>
                            <th class="orders-col-price">Total Price</th>
                            <th class="orders-col-method">Payment Method</th>
                            <th class="orders-col-payment-status">Payment Status</th>
                            <th class="orders-col-cancel-status">Cancellation Return/Refund Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="orders-col-id"><?= htmlspecialchars((string) $order['id']); ?></td>
                                    <td class="orders-col-user-id"><?= htmlspecialchars((string) $order['user_id']); ?></td>
                                    <td class="orders-col-date"><?= htmlspecialchars($order['placed_on']); ?></td>
                                    <td class="orders-col-name"><?= htmlspecialchars($order['name']); ?></td>
                                    <td class="orders-col-number"><?= htmlspecialchars($order['number']); ?></td>
                                    <td class="orders-address orders-col-address"><?= htmlspecialchars($order['address']); ?></td>
                                    <td class="orders-products orders-col-products"><?= htmlspecialchars($order['total_products']); ?></td>
                                    <td class="orders-col-price">$<?= htmlspecialchars((string) $order['total_price']); ?>/-</td>
                                    <td class="orders-col-method"><?= htmlspecialchars($order['method']); ?></td>
                                    <td class="orders-col-payment-status">
                                        <form method="post" class="payment-status-form">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) $order['id']); ?>">
                                            <select name="payment_status" class="payment-select">
                                                <?php foreach ($status_options as $status_option): ?>
                                                    <option value="<?= htmlspecialchars($status_option); ?>" <?= $order['payment_status'] === $status_option ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars(format_payment_status_label($status_option)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_payment_status" class="update-btn">Update</button>
                                        </form>
                                    </td>
                                    <td class="orders-col-cancel-status">
                                        <span class="status-badge <?= payment_status_badge_class($order['payment_status']); ?>">
                                            <?= htmlspecialchars(format_payment_status_label(
                                                in_array($order['payment_status'], ['canceled', 'pendingCancellation', 'pendingreturn', 'returned/refunded'], true)
                                                    ? $order['payment_status']
                                                    : 'active'
                                            )); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="orders-empty">No orders placed yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </section>

    <script src="../js/admin_script.js"></script>

</body>

</html>
