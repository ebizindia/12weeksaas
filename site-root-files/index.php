<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>12 Week Edge - Transform Your Execution</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --gold-accent: #d4af37;
            --text-dark: #1a1a2e;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        h1, h2, h3, h4 {
            font-weight: 700;
        }

        .display-1 {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 2px 30px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
            letter-spacing: -0.5px;
        }

        .navbar-brand span {
            color: var(--gold-accent);
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 1rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--gold-accent) !important;
        }

        .btn-join-waitlist {
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(15, 52, 96, 0.3);
        }

        .btn-join-waitlist:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(15, 52, 96, 0.4);
            color: white;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #1a1a2e 100%);
            color: white;
            padding: 120px 0 100px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 4rem;
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--gold-accent);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Waitlist Form */
        .waitlist-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.2);
            max-width: 500px;
        }

        .waitlist-form input {
            border: 2px solid #e9ecef;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .waitlist-form input:focus {
            border-color: var(--gold-accent);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.15);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--gold-accent), #b8941f);
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 700;
            border: none;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: var(--bg-light);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-align: center;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            text-align: center;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.05);
            height: 100%;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.1);
            border-color: var(--gold-accent);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon i {
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        /* Problem-Solution Section */
        .problem-section {
            padding: 100px 0;
            background: white;
        }

        .problem-card {
            background: linear-gradient(135deg, #fff5f5, #fff0f0);
            padding: 2rem;
            border-radius: 15px;
            border-left: 4px solid #dc3545;
            margin-bottom: 1.5rem;
        }

        .solution-card {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            padding: 2rem;
            border-radius: 15px;
            border-left: 4px solid var(--gold-accent);
            margin-bottom: 1.5rem;
        }

        /* Testimonials */
        .testimonials-section {
            padding: 100px 0;
            background: var(--primary-color);
            color: white;
        }

        .testimonial-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
            height: 100%;
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gold-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .author-info h5 {
            margin: 0;
            font-size: 1rem;
        }

        .author-info p {
            margin: 0;
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* How It Works */
        .how-it-works {
            padding: 100px 0;
            background: white;
        }

        .step-card {
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .step-number {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--gold-accent), #b8941f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            font-weight: 900;
            color: white;
        }

        .step-arrow {
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: var(--gold-accent);
            opacity: 0.3;
        }

        /* Pricing Preview */
        .pricing-section {
            padding: 100px 0;
            background: var(--bg-light);
        }

        .pricing-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }

        .pricing-card.featured {
            transform: scale(1.05);
            border: 3px solid var(--gold-accent);
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .pricing-badge {
            background: var(--gold-accent);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .pricing-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .pricing-amount {
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .pricing-period {
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        .pricing-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-features i {
            color: var(--gold-accent);
            margin-right: 0.5rem;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 1.5rem;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--gold-accent);
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .hero-stats {
                gap: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .cta-title {
                font-size: 2rem;
            }

            .step-arrow {
                display: none;
            }
        }
    </style>
    <meta name="description" content="Transform your annual goals into 12-week cycles. The proven system used by Fortune 500 executives to achieve more in 12 weeks than most do in 12 months.">
    <meta name="keywords" content="12 week year, goal setting, executive productivity, goal achievement, business goals">
    <meta property="og:title" content="12 Week Edge - Achieve More in 12 Weeks Than Others Do in 12 Months">
    <meta property="og:description" content="The proven system used by Fortune 500 executives to dramatically improve execution and results.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.12weekedge.com">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                12 Week<span>Edge</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Success Stories</a></li>
                    <li class="nav-item ms-3">
                        <a href="#waitlist" class="btn btn-join-waitlist">Join Waitlist</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-content" data-aos="fade-right">
                    <h1 class="hero-title">
                        Achieve More in<br>
                        <span style="color: var(--gold-accent);">12 Weeks</span><br>
                        Than Others Do<br>
                        in 12 Months
                    </h1>
                    <p class="hero-subtitle">
                        The proven system used by Fortune 500 executives to dramatically improve execution and results.
                    </p>
                    <a href="#waitlist" class="btn btn-primary-custom" style="display: inline-block; width: auto;">
                        Join the Waitlist <i class="fas fa-arrow-right ms-2"></i>
                    </a>

                    <div class="hero-stats">
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                            <span class="stat-number">4x</span>
                            <span class="stat-label">Faster Execution</span>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                            <span class="stat-number">87%</span>
                            <span class="stat-label">Goal Achievement</span>
                        </div>
                        <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                            <span class="stat-number">10k+</span>
                            <span class="stat-label">Executives Trust Us</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-left">
                    <div class="waitlist-form mt-5 mt-lg-0" id="waitlist">
                        <h3 class="text-center mb-4" style="color: var(--primary-color);">
                            <strong>Get Early Access</strong>
                        </h3>
                        <p class="text-center text-muted mb-4">
                            Join 5,000+ executives on the waitlist
                        </p>
                        <form id="waitlistForm" method="POST" action="waitlist-signup.php">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Full Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Work Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="company" placeholder="Company Name">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="title" placeholder="Job Title">
                            </div>
                            <button type="submit" class="btn btn-primary-custom">
                                Secure My Spot
                            </button>
                        </form>
                        <p class="text-center mt-3" style="font-size: 0.85rem; color: #999;">
                            <i class="fas fa-lock me-1"></i> We respect your privacy. Unsubscribe anytime.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="problem-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-title text-start">The Annual Planning Trap</h2>
                    <div class="problem-card">
                        <h5><i class="fas fa-times-circle text-danger me-2"></i> 12-month goals feel distant</h5>
                        <p class="mb-0 text-muted">Annual goals create a false sense of time, leading to procrastination and missed opportunities.</p>
                    </div>
                    <div class="problem-card">
                        <h5><i class="fas fa-times-circle text-danger me-2"></i> Execution drops after Q1</h5>
                        <p class="mb-0 text-muted">Research shows 92% of annual goals fail. Energy and focus dissipate as the year progresses.</p>
                    </div>
                    <div class="problem-card">
                        <h5><i class="fas fa-times-circle text-danger me-2"></i> Lack of urgency kills results</h5>
                        <p class="mb-0 text-muted">When you have 12 months to achieve something, you'll take 12 months. Without urgency, there's no action.</p>
                    </div>
                </div>

                <div class="col-lg-6 mt-5 mt-lg-0" data-aos="fade-left">
                    <h2 class="section-title text-start">The 12-Week Solution</h2>
                    <div class="solution-card">
                        <h5><i class="fas fa-check-circle me-2" style="color: var(--gold-accent);"></i> Create immediate urgency</h5>
                        <p class="mb-0 text-muted">12 weeks is long enough to achieve significant goals, yet short enough to maintain focus and urgency.</p>
                    </div>
                    <div class="solution-card">
                        <h5><i class="fas fa-check-circle me-2" style="color: var(--gold-accent);"></i> Increase execution by 4x</h5>
                        <p class="mb-0 text-muted">When every week counts, you execute at a higher level. No more "we have plenty of time" mentality.</p>
                    </div>
                    <div class="solution-card">
                        <h5><i class="fas fa-check-circle me-2" style="color: var(--gold-accent);"></i> Build momentum faster</h5>
                        <p class="mb-0 text-muted">Four cycles per year means four fresh starts, four celebrations, and continuous momentum.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Why Executives Choose 12 Week Edge</h2>
            <p class="section-subtitle" data-aos="fade-up">Built for leaders who demand results, not excuses</p>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h4 class="feature-title">Laser-Focused Goals</h4>
                        <p class="text-muted">
                            Set 3-5 critical goals per 12-week cycle. Focus on what truly moves the needle, not busywork.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="feature-title">Weekly Scorecards</h4>
                        <p class="text-muted">
                            Track execution rate, not just outcomes. Know exactly where you stand every single week.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="feature-title">Enterprise-Grade Privacy</h4>
                        <p class="text-muted">
                            Your strategic goals are encrypted end-to-end. What you're planning stays between you and your system.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h4 class="feature-title">Gamified Progress</h4>
                        <p class="text-muted">
                            Turn execution into a game. Earn achievements, climb leaderboards (optional), and build winning streaks.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h4 class="feature-title">AI-Powered Insights</h4>
                        <p class="text-muted">
                            Get intelligent recommendations on goal breakdown, time allocation, and execution patterns.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="feature-title">Mobile-First Design</h4>
                        <p class="text-muted">
                            Review progress, log wins, update tasks—anywhere, anytime. Your goals travel with you.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Simple. Powerful. Proven.</h2>
            <p class="section-subtitle" data-aos="fade-up">Get results in 4 simple steps</p>

            <div class="row mt-5">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4>Set Your Vision</h4>
                        <p class="text-muted">Define 3-5 critical goals for the next 12 weeks. What would make this quarter exceptional?</p>
                        <div class="step-arrow d-none d-lg-block">→</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4>Break It Down</h4>
                        <p class="text-muted">Turn each goal into specific weekly tactics. Make execution crystal clear.</p>
                        <div class="step-arrow d-none d-lg-block">→</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4>Execute Daily</h4>
                        <p class="text-muted">Take action on your tactics. Track progress in real-time with our mobile app.</p>
                        <div class="step-arrow d-none d-lg-block">→</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4>Score & Adjust</h4>
                        <p class="text-muted">Weekly scorecards show execution rate. Course-correct before it's too late.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section" id="testimonials">
        <div class="container">
            <h2 class="section-title text-white" data-aos="fade-up">Trusted by Top Executives</h2>
            <p class="section-subtitle text-white-50" data-aos="fade-up">See why leaders choose the 12-week approach</p>

            <div class="row g-4 mt-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "I've tried every productivity system. This is the only one that made me actually achieve my annual goals—in a quarter."
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">SC</div>
                            <div class="author-info">
                                <h5>Sarah Chen</h5>
                                <p>VP of Operations, Tech Startup</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Our executive team adopted the 12-week year. Revenue increased 40% in two quarters. It's not magic—it's focus."
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">MR</div>
                            <div class="author-info">
                                <h5>Michael Rodriguez</h5>
                                <p>CEO, Manufacturing Company</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "Finally, a system that aligns my team's efforts. Everyone knows what matters THIS week, not 'someday.'"
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">AP</div>
                            <div class="author-info">
                                <h5>Amanda Patel</h5>
                                <p>Director of Strategy, Fortune 500</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Preview -->
    <section class="pricing-section" id="pricing">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Early Bird Pricing</h2>
            <p class="section-subtitle" data-aos="fade-up">Lock in lifetime discount by joining the waitlist</p>

            <div class="row g-4 mt-4 justify-content-center">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="pricing-card">
                        <h3 class="pricing-title">Professional</h3>
                        <div class="pricing-amount">&#8377; 300</div>
                        <div class="pricing-period">per month</div>
                        <ul class="pricing-features text-start">
                            <li><i class="fas fa-check"></i> Unlimited 12-week cycles</li>
                            <li><i class="fas fa-check"></i> Up to 10 goals per cycle</li>
                            <li><i class="fas fa-check"></i> Weekly scorecards</li>
                            <li><i class="fas fa-check"></i> Mobile apps (iOS & Android)</li>
                            <li><i class="fas fa-check"></i> Progress analytics</li>
                            <li><i class="fas fa-check"></i> Email reminders</li>
                        </ul>
                        <a href="#waitlist" class="btn btn-join-waitlist w-100">Join Waitlist</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="pricing-card featured">
                        <div class="pricing-badge">Most Popular</div>
                        <h3 class="pricing-title">Executive</h3>
                        <div class="pricing-amount">&#8377; 500</div>
                        <div class="pricing-period">per month</div>
                        <ul class="pricing-features text-start">
                            <li><i class="fas fa-check"></i> Everything in Professional</li>
                            <li><i class="fas fa-check"></i> Unlimited goals</li>
                            <li><i class="fas fa-check"></i> AI-powered insights</li>
                            <li><i class="fas fa-check"></i> Team collaboration (5 users)</li>
                            <li><i class="fas fa-check"></i> Priority support</li>
                            <li><i class="fas fa-check"></i> Export & integrations</li>
                            <li><i class="fas fa-check"></i> Custom templates</li>
                        </ul>
                        <a href="#waitlist" class="btn btn-primary-custom w-100">Join Waitlist</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="pricing-card">
                        <h3 class="pricing-title">Enterprise</h3>
                        <div class="pricing-amount">Custom</div>
                        <div class="pricing-period">contact us</div>
                        <ul class="pricing-features text-start">
                            <li><i class="fas fa-check"></i> Everything in Executive</li>
                            <li><i class="fas fa-check"></i> Unlimited team members</li>
                            <li><i class="fas fa-check"></i> SSO & SAML authentication</li>
                            <li><i class="fas fa-check"></i> Dedicated account manager</li>
                            <li><i class="fas fa-check"></i> Custom onboarding</li>
                            <li><i class="fas fa-check"></i> SLA guarantee</li>
                            <li><i class="fas fa-check"></i> White-label options</li>
                        </ul>
                        <a href="#waitlist" class="btn btn-join-waitlist w-100">Join Waitlist</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5" data-aos="fade-up">
                <p class="text-muted">
                    <i class="fas fa-gift me-2" style="color: var(--gold-accent);"></i>
                    <strong>Waitlist Members Get 50% Off First Year + Lifetime Priority Support</strong>
                </p>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="cta-title" data-aos="fade-up">Ready to Transform Your Execution?</h2>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                Join thousands of executives who are achieving more in 12 weeks than they used to in 12 months.
            </p>
            <a href="#waitlist" class="btn btn-primary-custom" style="display: inline-block; width: auto; font-size: 1.2rem; padding: 1rem 3rem;" data-aos="fade-up" data-aos-delay="200">
                Get Early Access Now
            </a>
            <p class="mt-4" style="opacity: 0.8;" data-aos="fade-up" data-aos-delay="300">
                <i class="fas fa-users me-2"></i> Join 5,000+ executives on the waitlist
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">12 Week<span style="color: var(--gold-accent);">Edge</span></div>
                    <p style="opacity: 0.7;">
                        Helping executives achieve extraordinary results through the power of 12-week planning.
                    </p>
                </div>
                <div class="col-lg-2 col-6 mb-4 footer-links">
                    <h6 class="text-white mb-3">Product</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="#features">Features</a>
                        <a href="#pricing">Pricing</a>
                        <a href="#how-it-works">How It Works</a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4 footer-links">
                    <h6 class="text-white mb-3">Company</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="#about">About Us</a>
                        <a href="#testimonials">Success Stories</a>
                        <a href="#blog">Blog</a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4 footer-links">
                    <h6 class="text-white mb-3">Legal</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="/terms-of-service.php">Terms</a>
                        <a href="/privacy-policy.php">Privacy</a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4">
                    <h6 class="text-white mb-3">Connect</h6>
                    <div class="d-flex gap-3">
                        <a href="#" style="color: white; font-size: 1.5rem;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color: white; font-size: 1.5rem;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: white; font-size: 1.5rem;"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 2rem 0;">
            <div class="text-center" style="opacity: 0.7;">
                <p class="mb-0">&copy; 2025 12 Week Edge. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(window).scrollTop() > 50) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });

        // Smooth scroll
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });

        // Form submission
        $('#waitlistForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();

            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i> Joining...').prop('disabled', true);

            $.ajax({
                url: 'waitlist-signup.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'thank-you.php?email=' + encodeURIComponent(response.email);
                    } else {
                        alert(response.message || 'An error occurred. Please try again.');
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });
    </script>
</body>
</html>
