<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

$pageTitle = 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Research Repository - Discover Academic Research</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --primary-color: #0D47A1;
            --secondary-color: #00897B;
            --light-gray: #F5F5F5;
            --dark-color: #212529;
        }

        body {
            background-color: var(--light-gray);
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }

        .hero-section p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.95;
            color: white;
        }

        /* Search Section */
        .search-container {
            max-width: 800px;
            margin: -40px auto 50px;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            position: relative;
            z-index: 10;
        }

        .search-container h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
        }

        .search-form-group {
            margin-bottom: 15px;
        }

        .search-form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 13px;
        }

        .search-form-group input,
        .search-form-group select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-form-group input:focus,
        .search-form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 71, 161, 0.1);
        }

        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }

        .search-btn:hover {
            background-color: #0A3B8F;
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
            text-align: center;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-card .label {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        /* Info Section */
        .info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .info-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .info-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .info-card .icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .info-card h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 12px;
        }

        .info-card p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 50px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 50px;
        }

        .cta-section h2 {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .cta-btn-primary {
            background-color: white;
            color: var(--primary-color);
        }

        .cta-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .cta-btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .cta-btn-secondary:hover {
            background-color: white;
            color: var(--primary-color);
        }

        /* Container */
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 32px;
            }

            .hero-section p {
                font-size: 16px;
            }

            .search-container {
                margin: -30px 20px 30px;
                padding: 20px;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .cta-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book"></i> ICT Repository
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Browse</a></li>
                    <?php if (isset($_SESSION['student'])): ?>
                        <li class="nav-item"><a class="nav-link" href="student/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="student/logout.php">Logout</a></li>
                    <?php elseif (isset($_SESSION['admin'])): ?>
                        <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin Panel</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="student/login.php">Student</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="content-container">
            <h1>ICT Research Repository</h1>
            <p>Discover and share cutting-edge research in Information and Communication Technology</p>
        </div>
    </section>

    <!-- Search Container -->
    <div class="content-container">
        <div class="search-container">
            <h3><i class="bi bi-search"></i> Find Research</h3>
            <form method="GET" action="search.php">
                <div class="row">
                    <div class="col-md-6">
                        <div class="search-form-group">
                            <label for="keyword">Keyword</label>
                            <input type="text" id="keyword" class="form-control" name="keyword" placeholder="Search by title or author...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="search-form-group">
                            <label for="category">Category</label>
                            <select id="category" class="form-control" name="category">
                                <option value="">All Categories</option>
                                <option value="Programming">Programming</option>
                                <option value="Networking">Networking</option>
                                <option value="Cybersecurity">Cybersecurity</option>
                                <option value="Database">Database</option>
                                <option value="AI/Machine Learning">AI/Machine Learning</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Mobile Development">Mobile Development</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="search-btn"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM research WHERE status='Approved'");
                    $row = $result->fetch_assoc();
                    echo isset($row['total']) ? $row['total'] : '0';
                    ?>
                </div>
                <div class="label">Approved Research Papers</div>
            </div>
            <div class="stat-card">
                <div class="number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM research");
                    $row = $result->fetch_assoc();
                    echo isset($row['total']) ? $row['total'] : '0';
                    ?>
                </div>
                <div class="label">Total Submissions</div>
            </div>
            <div class="stat-card">
                <div class="number">
                    <?php
                    $result = $conn->query("SELECT COUNT(DISTINCT category) as total FROM research");
                    $row = $result->fetch_assoc();
                    echo isset($row['total']) ? $row['total'] : '0';
                    ?>
                </div>
                <div class="label">Research Categories</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-card">
                <div class="icon"><i class="bi bi-cloud-upload"></i></div>
                <h4>Easy Upload</h4>
                <p>Submit your research in a few simple steps. We accept PDF, Word, and text documents.</p>
            </div>
            <div class="info-card">
                <div class="icon"><i class="bi bi-search"></i></div>
                <h4>Discover Research</h4>
                <p>Search through thousands of approved research papers across multiple categories.</p>
            </div>
            <div class="info-card">
                <div class="icon"><i class="bi bi-shield-check"></i></div>
                <h4>Quality Assured</h4>
                <p>All submissions are reviewed by experts to ensure quality and relevance.</p>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h2>Ready to Share Your Research?</h2>
            <div class="cta-buttons">
                <a href="student/login.php" class="cta-btn cta-btn-primary">Student Login</a>
                <a href="student/login.php" class="cta-btn cta-btn-secondary">Create Account</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 pt-5 pb-3" style="background-color: var(--dark-color); color: white;">
        <div class="content-container">
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <h5>About</h5>
                    <p>ICT Research Repository is a platform dedicated to academic excellence and knowledge sharing.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" style="color: #ccc;">Browse Research</a></li>
                        <li><a href="student/login.php" style="color: #ccc;">Student Login</a></li>
                        <li><a href="admin/login.php" style="color: #ccc;">Admin Portal</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Contact</h5>
                    <p style="color: #ccc; margin: 0;">
                        Email: <a href="mailto:info@ictresearch.edu" style="color: #ccc;">info@ictresearch.edu</a>
                    </p>
                </div>
            </div>
            <hr style="border-top: 1px solid rgba(255,255,255,0.1);">
            <div class="row">
                <div class="col-md-6">
                    <p style="margin: 0; color: #ccc;">&copy; 2026 ICT Research Repository. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p style="margin: 0; color: #ccc;">
                        <a href="#" style="color: #ccc;">Privacy</a> | 
                        <a href="#" style="color: #ccc;">Terms</a> | 
                        <a href="#" style="color: #ccc;">Contact</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
