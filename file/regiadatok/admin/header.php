<?php
require_once __DIR__ . '/../includes/auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - AlapSzerviz.hu</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            /* Egységes színpaletta */
            --primary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #16a085;
            
            /* Light mode */
            --bg-main: #f5f6fa;
            --bg-card: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-color: #dfe6e9;
            --navbar-bg: #2c3e50;
        }
        
        [data-theme="dark"] {
            /* Dark mode */
            --bg-main: #0d1117;
            --bg-card: #161b22;
            --text-primary: #e6edf3;
            --text-secondary: #8b949e;
            --border-color: #30363d;
            --navbar-bg: #0d1117;
        }
        
        /* Dark mode - minden háttér sötét legyen */
        [data-theme="dark"] body {
            background-color: #0d1117 !important;
            color: #e6edf3 !important;
        }
        
        [data-theme="dark"] .card {
            background-color: #161b22 !important;
            border-color: #30363d !important;
        }
        
        [data-theme="dark"] .table tbody tr {
            background-color: #21262d !important;
        }
        
        [data-theme="dark"] .table tbody tr:hover {
            background-color: #161b22 !important;
        }
        
        /* Dark mode - táblázat cellák FEHÉR szöveggel */
        [data-theme="dark"] .table tbody td {
            background-color: #21262d !important;
            color: #ffffff !important;
        }
        
        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* Navbar */
        .navbar {
            background-color: var(--navbar-bg) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.25rem 0;
            min-height: 70px;
        }
        
        .navbar-brand {
            color: #fff !important;
            font-weight: 600;
            font-size: 1.4rem;
            margin-right: 3rem;
        }
        
        .navbar-brand i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s;
            padding: 0.75rem 1.25rem !important;
            border-radius: 6px;
            margin: 0 0.25rem;
        }
        
        .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-link.active {
            color: #fff !important;
            background-color: var(--primary);
        }
        
        /* Dark mode toggle */
        .theme-toggle {
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Card-ok */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background: var(--bg-card);
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            transition: background-color 0.3s, border-color 0.3s;
        }
        
        .card-header {
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .card-header.bg-primary {
            background-color: var(--primary) !important;
            color: white !important;
            border-bottom: none;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .card-footer {
            background-color: var(--bg-card);
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        
        /* Stat card-ok - Egységes színek */
        .stat-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s;
        }
        
        .stat-box:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .stat-box i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-box h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
            color: var(--text-primary);
        }
        
        .stat-box p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Egységes színek a stat-okhoz */
        .stat-box.warning i { color: var(--warning); }
        .stat-box.success i { color: var(--success); }
        .stat-box.info i { color: var(--info); }
        .stat-box.primary i { color: var(--primary); }
        
        /* Táblázatok */
        .table {
            margin-bottom: 0;
            color: var(--text-primary);
        }
        
        .table thead th {
            background-color: var(--primary) !important;
            border-bottom: 2px solid var(--primary);
            color: white !important;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem;
        }
        
        .table-dark {
            background-color: var(--primary) !important;
        }
        
        .table-dark th {
            background-color: var(--primary) !important;
            color: white !important;
        }
        
        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-card);
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: var(--bg-main);
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        /* Táblázatban lévő kis szövegek */
        .table tbody td small,
        .table tbody td .small {
            color: var(--text-primary);
            font-weight: 400;
        }
        
        .table tbody td .text-muted {
            color: var(--text-secondary);
        }
        
        /* Strong elemek a táblázatban */
        .table tbody td strong {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        /* Dark mode - MINDEN szöveg fehér/világos legyen! */
        [data-theme="dark"] .table tbody td,
        [data-theme="dark"] .table tbody td *,
        [data-theme="dark"] .table tbody td small,
        [data-theme="dark"] .table tbody td .small,
        [data-theme="dark"] .table tbody td strong,
        [data-theme="dark"] .table tbody td a {
            color: #ffffff !important;
        }
        
        [data-theme="dark"] .table tbody td .text-muted {
            color: #c9d1d9 !important;
        }
        
        [data-theme="dark"] .table tbody td .badge {
            color: #ffffff !important;
        }
        
        /* Gombok - Tiszta design */
        .btn {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        /* Badge-ek */
        .badge {
            padding: 0.4rem 0.6rem;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        /* List group */
        .list-group-item {
            border: 1px solid var(--border-color);
            padding: 1rem;
            background-color: var(--bg-card);
            color: var(--text-primary);
        }
        
        .list-group-item:hover {
            background-color: var(--bg-main);
        }
        
        /* Dark mode - list group */
        [data-theme="dark"] .list-group-item {
            background-color: #161b22 !important;
            border-color: #30363d !important;
            color: #e6edf3 !important;
        }
        
        [data-theme="dark"] .list-group-item:hover {
            background-color: #0d1117 !important;
        }
        
        [data-theme="dark"] .list-group-item.active {
            background-color: #3498db !important;
            border-color: #3498db !important;
        }
        
        /* Form elemek */
        .form-control, .form-select {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--bg-card);
            border-color: var(--primary);
            color: var(--text-primary);
        }
        
        /* Dark mode - form elemek */
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: #0d1117 !important;
            border-color: #30363d !important;
            color: #e6edf3 !important;
        }
        
        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            background-color: #0d1117 !important;
            border-color: #3498db !important;
            color: #e6edf3 !important;
        }
        
        /* Footer */
        footer {
            background-color: var(--navbar-bg);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }
        
        /* Utility */
        h1, h2, h3, h4, h5 {
            color: var(--text-primary);
        }
        
        .text-muted {
            color: var(--text-secondary) !important;
        }
        
        /* Stat card-ok alján lévő szövegek */
        .card.bg-primary .card-body h5,
        .card.bg-success .card-body h5,
        .card.bg-warning .card-body h5,
        .card.bg-danger .card-body h5,
        .card.bg-info .card-body h5 {
            color: white !important;
        }
        
        .card.bg-primary .card-body h2,
        .card.bg-success .card-body h2,
        .card.bg-warning .card-body h2,
        .card.bg-danger .card-body h2,
        .card.bg-info .card-body h2 {
            color: white !important;
        }
        
        .card.bg-warning .card-body h5,
        .card.bg-warning .card-body h2 {
            color: #212529 !important;
        }
        
        /* Dark mode - card szövegek */
        [data-theme="dark"] .card-body,
        [data-theme="dark"] .card-body *:not(.badge):not(.btn) {
            color: #ffffff !important;
        }
        
        [data-theme="dark"] .card-header {
            color: #ffffff !important;
        }
        
        /* Alert-ek egységes színekkel */
        .alert-success { background-color: rgba(39, 174, 96, 0.1); border-color: var(--success); color: var(--success); }
        .alert-warning { background-color: rgba(243, 156, 18, 0.1); border-color: var(--warning); color: var(--warning); }
        .alert-danger { background-color: rgba(231, 76, 60, 0.1); border-color: var(--danger); color: var(--danger); }
        .alert-info { background-color: rgba(22, 160, 133, 0.1); border-color: var(--info); color: var(--info); }
        
        /* Dark mode - alert-ek */
        [data-theme="dark"] .alert-success { background-color: rgba(39, 174, 96, 0.2); color: #4ade80; }
        [data-theme="dark"] .alert-warning { background-color: rgba(243, 156, 18, 0.2); color: #fbbf24; }
        [data-theme="dark"] .alert-danger { background-color: rgba(231, 76, 60, 0.2); color: #f87171; }
        [data-theme="dark"] .alert-info { background-color: rgba(22, 160, 133, 0.2); color: #5eead4; }
        
        [data-theme="dark"] .alert-secondary {
            background-color: rgba(139, 148, 158, 0.2) !important;
            border-color: #8b949e !important;
            color: #8b949e !important;
        }
        
        /* Support Chat Buborékok - Dark Mode Support */
        #messages-container {
            background-color: var(--bg-main) !important;
        }
        
        /* User üzenet buborék */
        .chat-bubble-user {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
            border-radius: 18px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .chat-bubble-user:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .chat-bubble-user strong {
            color: var(--primary) !important;
        }
        
        .chat-bubble-user p {
            color: var(--text-primary) !important;
        }
        
        /* Admin üzenet buborék */
        .chat-bubble-admin {
            background: linear-gradient(135deg, var(--primary) 0%, #2980b9 100%) !important;
            color: white !important;
            border: none !important;
            border-radius: 18px !important;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .chat-bubble-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }
        
        .chat-bubble-admin strong {
            color: white !important;
        }
        
        .chat-bubble-admin p {
            color: white !important;
        }
        
        /* Chat timestamp */
        .chat-timestamp {
            color: var(--text-secondary) !important;
            font-size: 0.75rem;
        }
        
        /* Animáció az üzenetekhez */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .chat-message-left {
            animation: slideInLeft 0.3s ease-out;
        }
        
        .chat-message-right {
            animation: slideInRight 0.3s ease-out;
        }
        
        /* Dropdown menu dark mode */
        .dropdown-menu {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }
        
        .dropdown-item {
            color: var(--text-primary);
        }
        
        .dropdown-item:hover {
            background-color: var(--bg-main);
            color: var(--text-primary);
        }
        
        [data-theme="dark"] .dropdown-menu {
            background-color: #161b22 !important;
            border-color: #30363d !important;
        }
        
        [data-theme="dark"] .dropdown-item {
            color: #e6edf3 !important;
        }
        
        [data-theme="dark"] .dropdown-item:hover {
            background-color: #0d1117 !important;
        }
        
        /* Navbar spacing */
        .navbar-nav .nav-item {
            margin: 0 0.5rem;
        }
        
        .navbar-nav.mx-auto .nav-item {
            margin: 0 0.75rem;
        }
        
        .navbar-nav.ms-auto .nav-item {
            margin-left: 0.5rem;
        }
        
        /* Dropdown toggle */
        .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-lock-fill"></i>
                Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menüpontok középen -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="services.php">
                            <i class="bi bi-list-check"></i> Szervizek
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'comments.php' ? 'active' : ''; ?>" href="comments.php">
                            <i class="bi bi-chat-left-text"></i> Kommentek
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                            <i class="bi bi-people"></i> Felhasználók
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'support.php' ? 'active' : ''; ?>" href="support.php">
                            <i class="bi bi-chat-dots"></i> Support
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                            <i class="bi bi-calendar-check"></i> Foglalások
                        </a>
                    </li>
                </ul>
                
                <!-- User info és beállítások jobbra -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank" title="Főoldal megnyitása">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link theme-toggle" onclick="toggleTheme()" title="Dark/Light Mode">
                            <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(get_current_user_name()); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Kijelentkezés</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <main>
