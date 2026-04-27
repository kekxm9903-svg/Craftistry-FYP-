<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Craftistry - Malaysian Art Marketplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body>

    <!-- HEADER -->
    <header id="header">
        <div class="container nav-wrap">
            <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">
            <div class="nav-btns">
                <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="container hero-inner">
            <h1>Discover Local Art.<br><span>Buy, Sell & Create.</span></h1>
            <p class="hero-desc">Connect with Malaysian artists. Browse original artworks, commission custom pieces, and join a growing creative community.</p>
            <div class="hero-btns">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get Started</a>
                <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Login</a>
            </div>
        </div>
    </section>

    <!-- WHAT WE OFFER -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">What We Offer</h2>
            <p class="section-sub">Everything you need to buy, sell, and learn art in one place.</p>
            <div class="cards">
                <div class="card">
                    <div class="card-icon">🎨</div>
                    <h3>Art Marketplace</h3>
                    <p>Browse and buy original artworks directly from verified local artists across Malaysia.</p>
                </div>
                <div class="card">
                    <div class="card-icon">✍️</div>
                    <h3>Custom Orders</h3>
                    <p>Request a personalised piece made just for you — tailored to your style and vision.</p>
                </div>
                <div class="card">
                    <div class="card-icon">📚</div>
                    <h3>Creative Classes</h3>
                    <p>Learn from experienced artists. Join workshops and grow your creative skills.</p>
                </div>
                <div class="card">
                    <div class="card-icon">📅</div>
                    <h3>Art Events</h3>
                    <p>Discover exhibitions and cultural events happening across Malaysia.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOR ARTISTS -->
    <section class="artist-section">
        <div class="container artist-inner">
            <div class="artist-text">
                <p class="label">For Artists</p>
                <h2>Turn your passion into income</h2>
                <p>Join hundreds of Malaysian artists already selling on Craftistry. Set up your shop for free, reach thousands of art lovers, and offer custom commissions.</p>
                <ul class="perks">
                    <li>✓ Free shop setup</li>
                    <li>✓ Sell originals & custom orders</li>
                    <li>✓ Secure payouts</li>
                    <li>✓ Grow your audience</li>
                </ul>
                <a href="{{ route('register') }}?role=artist" class="btn btn-white btn-lg">Start Selling Free</a>
            </div>
            <div class="artist-img-wrap">
                <div class="artist-card">
                    <div class="ac-avatar">🎨</div>
                    <p class="ac-name">Ahmad Faris</p>
                    <p class="ac-role">Digital Artist · Kuala Lumpur</p>
                    <div class="ac-stats">
                        <div><strong>48</strong><span>Works</span></div>
                        <div><strong>RM 6.2k</strong><span>Earned</span></div>
                        <div><strong>4.9★</strong><span>Rating</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to explore Malaysian art?</h2>
            <p>Join Craftistry today — it's free to sign up.</p>
            <div class="cta-btns">
                <a href="{{ route('register') }}" class="btn btn-white btn-lg">Create Free Account</a>
                <a href="{{ route('login') }}" class="btn btn-outline-white btn-lg">Login</a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container footer-inner">
            <p>&copy; 2025 Craftistry. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="{{ asset('js/welcome.js') }}"></script>
</body>
</html>