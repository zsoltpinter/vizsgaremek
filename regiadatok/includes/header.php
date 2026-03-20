<?php
require_once __DIR__ . '/auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlapSzerviz.hu - Autószervizek Keresése</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-dark: #1a2332;
            --secondary-gray: #6c757d;
            --accent-yellow: #ffc107;
            --light-bg: #f8f9fa;
            --text-color: #212529;
            --card-bg: #ffffff;
        }
        
        [data-theme="dark"] {
            --primary-dark: #0d1117;
            --light-bg: #0d1117;
            --text-color: #c9d1d9;
            --card-bg: #161b22;
            --secondary-gray: #8b949e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }
        
        .card {
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        
        [data-theme="dark"] .card {
            border-color: #30363d;
        }
        
        [data-theme="dark"] .text-muted {
            color: var(--secondary-gray) !important;
        }
        
        [data-theme="dark"] .bg-secondary {
            background-color: #21262d !important;
        }
        
        .theme-toggle {
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .theme-toggle:hover {
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .navbar {
            background-color: var(--primary-dark) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: var(--accent-yellow) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .navbar-brand i {
            margin-right: 8px;
        }
        
        .nav-link {
            color: #fff !important;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--accent-yellow) !important;
        }
        
        .nav-link.active {
            color: var(--accent-yellow) !important;
        }
        
        .btn-primary {
            background-color: var(--accent-yellow);
            border-color: var(--accent-yellow);
            color: var(--primary-dark);
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: #e0a800;
            border-color: #e0a800;
            color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: var(--secondary-gray);
            border-color: var(--secondary-gray);
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .badge-pending {
            background-color: #ff9800;
        }
        
        .badge-approved {
            background-color: #4caf50;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2c3e50 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .hero-section .accent {
            color: var(--accent-yellow);
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: var(--accent-yellow);
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-dark);
            margin: 10px 0;
        }
        
        .stat-card p {
            color: var(--secondary-gray);
            margin: 0;
        }
        
        footer {
            background-color: var(--primary-dark);
            color: white;
            padding: 30px 0;
            margin-top: 60px;
        }
        
        footer a {
            color: var(--accent-yellow);
            text-decoration: none;
        }
        
        footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .form-control:focus {
            border-color: var(--accent-yellow);
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        
        .service-card-title {
            color: var(--text-color);
            font-weight: 600;
        }
        
        .service-card-text {
            color: var(--secondary-gray);
        }
        
        [data-theme="dark"] .service-card-title {
            color: var(--text-color);
        }
        
        [data-theme="dark"] .service-card-text {
            color: var(--secondary-gray);
        }
        
        [data-theme="dark"] .stat-card {
            background: var(--card-bg);
            color: var(--text-color);
        }
        
        [data-theme="dark"] .stat-card h3 {
            color: var(--text-color);
        }
        
        [data-theme="dark"] .form-control {
            background-color: var(--card-bg);
            border-color: #30363d;
            color: var(--text-color);
        }
        
        [data-theme="dark"] .form-select {
            background-color: var(--card-bg);
            border-color: #30363d;
            color: var(--text-color);
        }
        
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
        }
        
        /* Animációk */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(6) { animation-delay: 0.6s; }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Hover effektek */
        .btn {
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,193,7,.3);
            border-radius: 50%;
            border-top-color: var(--accent-yellow);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Prémium card kiemelés */
        .premium-card {
            border: 2px solid var(--accent-yellow) !important;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
        }
        
        .premium-card:hover {
            box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4) !important;
        }
    </style>
</head>
<body>
    <!-- Navigáció -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
                <i class="bi bi-car-front-fill"></i>
                AlapSzerviz.hu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Főbb menüpontok középen -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="/index.php">
                            <i class="bi bi-house-door"></i> Főoldal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'services.php' ? 'active' : ''; ?>" href="/services.php">
                            <i class="bi bi-search"></i> Szervizek
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'favorites.php' ? 'active' : ''; ?>" href="/favorites.php">
                                <i class="bi bi-heart-fill"></i> Kedvencek
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'service_add.php' ? 'active' : ''; ?>" href="/service_add.php">
                                <i class="bi bi-plus-circle"></i> Szerviz Hozzáadása
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'support.php' ? 'active' : ''; ?>" href="/support.php">
                                <i class="bi bi-chat-dots"></i> Support
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- User menük jobbra -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link theme-toggle" onclick="toggleTheme()" title="Dark/Light Mode">
                            <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                        </span>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="/profile.php">
                                <i class="bi bi-person-circle"></i> Profil
                            </a>
                        </li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/index.php">
                                    <i class="bi bi-shield-lock"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Kijelentkezés
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" href="/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Bejelentkezés
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'register.php' ? 'active' : ''; ?>" href="/register.php">
                                <i class="bi bi-person-plus"></i> Regisztráció
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Tartalom kezdete -->
    <main>
