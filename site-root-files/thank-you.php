<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - 12 Week Edge</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #1a1a2e;
            --gold-accent: #d4af37;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #1a1a2e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .thank-you-container {
            max-width: 700px;
            text-align: center;
            padding: 3rem;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: var(--gold-accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 4rem;
            color: var(--primary-color);
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .lead {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease-out 0.4s both;
        }

        .benefit-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            text-align: left;
            animation: fadeInUp 0.6s ease-out 0.6s both;
        }

        .benefit-card h4 {
            color: var(--gold-accent);
            margin-bottom: 0.5rem;
        }

        .share-buttons {
            margin-top: 3rem;
            animation: fadeInUp 0.6s ease-out 0.8s both;
        }

        .btn-share {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-share:hover {
            background: rgba(255,255,255,0.2);
            border-color: var(--gold-accent);
            color: white;
            transform: translateY(-2px);
        }

        .btn-home {
            background: var(--gold-accent);
            color: var(--primary-color);
            padding: 1rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
            color: var(--primary-color);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

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
    </style>
</head>
<body>
    <div class="container">
        <div class="thank-you-container">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1>You're In!</h1>

            <p class="lead">
                Welcome to the 12 Week Edge waitlist. We've sent a confirmation to <strong><?php echo htmlspecialchars($_GET['email'] ?? 'your email'); ?></strong>
            </p>

            <div class="row g-3 mt-4">
                <div class="col-md-6">
                    <div class="benefit-card">
                        <h4><i class="fas fa-bolt me-2"></i> Early Access</h4>
                        <p class="mb-0">You'll get access before the public launch</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="benefit-card">
                        <h4><i class="fas fa-tag me-2"></i> 50% Discount</h4>
                        <p class="mb-0">Exclusive pricing locked in for life</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="benefit-card">
                        <h4><i class="fas fa-star me-2"></i> Priority Support</h4>
                        <p class="mb-0">Lifetime priority customer support</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="benefit-card">
                        <h4><i class="fas fa-users me-2"></i> Exclusive Community</h4>
                        <p class="mb-0">Join our founding members group</p>
                    </div>
                </div>
            </div>

            <div class="share-buttons">
                <p style="opacity: 0.8; margin-bottom: 1rem;">Know someone who could benefit? Share the waitlist:</p>
                <a href="https://twitter.com/intent/tweet?text=I%20just%20joined%20the%20waitlist%20for%2012%20Week%20Edge%20-%20a%20system%20to%20achieve%20more%20in%2012%20weeks%20than%20most%20do%20in%2012%20months!&url=https://www.12weekedge.com" target="_blank" class="btn-share">
                    <i class="fab fa-twitter me-2"></i> Share on Twitter
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=https://www.12weekedge.com" target="_blank" class="btn-share">
                    <i class="fab fa-linkedin me-2"></i> Share on LinkedIn
                </a>
            </div>

            <a href="index.php" class="btn-home">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>

            <div class="mt-5" style="opacity: 0.6; font-size: 0.9rem;">
                <p>Check your email for next steps and exclusive updates.</p>
            </div>
        </div>
    </div>
</body>
</html>
