<?php
// about.php - Comprehensive About Way2Green, Philosophy & Inclusivity Manifesto
require_once 'user_auth.php';
$user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Our Mission — Way2Green: Sustainable & Barrier-Free Hospitality</title>
    <meta name="description" content="Discover the philosophy, architecture, and core ethics behind Way2Green: uniting low-carbon transit with universally accessible eco-hospitality.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* ==========================================================================
           Bespoke Mobile-First Styles for About Page
           ========================================================================== */
        .about-hero {
            padding: 3rem 1rem 2rem;
            text-align: center;
            position: relative;
        }

        @media (min-width: 768px) {
            .about-hero {
                padding: 4.5rem 1.5rem 3rem;
            }
        }

        .about-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(16, 185, 129, 0.35);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 7px 16px;
            border-radius: var(--radius-full);
            color: #065f46;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.12);
        }

        .about-hero-title {
            font-size: clamp(1.9rem, 5.5vw, 3.4rem);
            font-weight: 800;
            color: #0b2e1b;
            line-height: 1.18;
            letter-spacing: -0.8px;
            max-width: 860px;
            margin: 0 auto 1.2rem;
        }

        .about-hero-title .highlight {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .about-hero-lead {
            font-size: clamp(0.95rem, 2.2vw, 1.15rem);
            color: #3f5547;
            max-width: 740px;
            margin: 0 auto 2.2rem;
            line-height: 1.65;
        }

        /* Responsive Stat Counter Grid */
        .impact-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            max-width: 960px;
            margin: 0 auto 2.8rem;
        }

        @media (min-width: 768px) {
            .impact-stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
                margin-bottom: 3.5rem;
            }
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 16px;
            padding: 1.3rem 0.8rem;
            text-align: center;
            box-shadow: 0 8px 24px rgba(15, 61, 36, 0.05);
            transition: transform 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-num {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.8rem, 4vw, 2.3rem);
            font-weight: 800;
            color: #064e3b;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #4b6354;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        /* Story Split Layout */
        .story-split {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.8rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 3rem;
            box-shadow: 0 14px 36px rgba(15, 61, 36, 0.06);
        }

        @media (min-width: 860px) {
            .story-split {
                grid-template-columns: 1.1fr 0.9fr;
                padding: 2.5rem;
                gap: 2.5rem;
                border-radius: 24px;
            }
        }

        .story-img-wrap {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(15, 61, 36, 0.12);
        }

        .story-img-wrap img {
            width: 100%;
            height: auto;
            max-height: 260px;
            object-fit: cover;
            display: block;
        }

        @media (min-width: 860px) {
            .story-img-wrap img {
                max-height: 380px;
            }
        }

        .story-floating-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            right: 12px;
            background: rgba(11, 46, 27, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #ffffff;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Pillars Section & Beautiful Feature Cards */
        .pillar-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.6rem;
            margin-bottom: 3.2rem;
        }

        @media (min-width: 820px) {
            .pillar-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1.8rem;
            }
        }

        .pillar-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 12px 32px rgba(15, 61, 36, 0.05);
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 768px) {
            .pillar-card {
                padding: 2.2rem;
            }
        }

        .pillar-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
        }

        .pillar-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            color: #064e3b;
            margin-bottom: 0.6rem;
            line-height: 1.25;
        }

        .pillar-desc {
            font-size: 0.92rem;
            color: #4b6354;
            line-height: 1.6;
            margin-bottom: 1.4rem;
        }

        /* Clean, Uncongested Feature Card Items (Fixing Image 2) */
        .pillar-feature-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pillar-feature-item {
            background: #f8faf9;
            border: 1px solid #d1e7dd;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .pillar-feature-icon {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.16);
            color: #065f46;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 800;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .pillar-feature-body {
            flex: 1;
        }

        .pillar-feature-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 0.92rem;
            color: #064e3b;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .pillar-feature-desc {
            font-size: 0.84rem;
            color: #4b6354;
            line-height: 1.5;
            margin: 0;
        }

        /* 3-Step Journey Architecture */
        .step-flow-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.2rem;
            margin-bottom: 3.2rem;
        }

        @media (min-width: 768px) {
            .step-flow-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.4rem;
            }
        }

        .flow-step-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(34, 197, 94, 0.22);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 24px rgba(15, 61, 36, 0.04);
        }

        .flow-step-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #065f46;
            font-weight: 800;
            font-size: 0.74rem;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        /* Ethics Banner (Fixing Image 1 Visibility) */
        .ethics-banner {
            background: linear-gradient(135deg, #092616 0%, #064e3b 100%) !important;
            color: #ffffff !important;
            border-radius: 20px;
            padding: 2rem 1.4rem;
            margin-bottom: 3.2rem;
            box-shadow: 0 18px 45px rgba(11, 46, 27, 0.25);
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .ethics-banner {
                padding: 3rem 2.4rem;
                border-radius: 24px;
            }
        }

        .ethics-banner .ethics-badge {
            display: inline-block;
            font-size: 0.78rem;
            font-weight: 800;
            color: #6ee7b7 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .ethics-banner h3.ethics-main-title {
            color: #ffffff !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: clamp(1.6rem, 4vw, 2.3rem) !important;
            margin: 4px 0 10px;
            line-height: 1.2;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .ethics-banner p.ethics-subtitle {
            font-size: 0.95rem;
            color: #e2fce9 !important;
            max-width: 680px;
            line-height: 1.65;
            margin: 0 0 1.5rem;
        }

        .ethics-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width: 640px) {
            .ethics-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }
        }

        @media (min-width: 960px) {
            .ethics-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }
        }

        .ethics-tile {
            background: rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 14px;
            padding: 1.2rem;
        }

        .ethics-tile h4 {
            color: #6ee7b7 !important;
            font-size: 1.05rem;
            margin-bottom: 6px;
            font-weight: 800;
        }

        .ethics-tile p {
            font-size: 0.85rem;
            color: #d1fae5 !important;
            line-height: 1.55;
            margin: 0;
        }

        /* FAQ Section */
        .faq-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-bottom: 3.2rem;
        }

        @media (min-width: 768px) {
            .faq-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
        }

        .faq-item {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 14px;
            padding: 1.3rem;
            box-shadow: 0 6px 20px rgba(15, 61, 36, 0.04);
        }

        .faq-q {
            font-weight: 800;
            font-size: 0.96rem;
            color: #064e3b;
            margin-bottom: 6px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.35;
        }

        .faq-a {
            font-size: 0.88rem;
            color: #4b6354;
            line-height: 1.6;
            margin: 0;
        }

        /* Bottom CTA */
        .about-cta-card {
            background: rgba(255, 255, 255, 0.95);
            text-align: center;
            padding: 2.2rem 1.4rem;
            border-radius: 20px;
            box-shadow: 0 14px 40px rgba(15, 61, 36, 0.08);
            margin-bottom: 3.5rem;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        @media (min-width: 768px) {
            .about-cta-card {
                padding: 3rem 2rem;
                border-radius: 24px;
            }
        }

        .about-cta-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        @media (min-width: 540px) {
            .about-cta-actions {
                flex-direction: row;
                gap: 14px;
            }
        }

        .about-cta-actions a {
            width: 100%;
            max-width: 280px;
            text-align: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing 3D Moving Scene Layer -->
    <div class="ambient-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Mobile Drawer Overlay & Menu -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.3rem; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.4rem;">🌱</span> Way2Green
        </div>
        <a href="index.php" class="drawer-link">Home</a>
        <a href="travel.php" class="drawer-link">Plan Transit</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="about.php" class="drawer-link" style="color: var(--primary);">Our Mission</a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="drawer-link">My Passports</a>
            <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="drawer-link">Traveler Sign In</a>
            <a href="register.php" class="drawer-link" style="color: var(--primary-light);">Create Account</a>
        <?php endif; ?>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin Portal</a>
    </div>

    <!-- Clean Header -->
    <header class="header-top">
        <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>
        <nav class="desktop-nav">
            <a href="index.php">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php">Plan Transit</a>
            <a href="about.php" class="active">About Us</a>
            <a href="my-trips.php">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a></nav>
        <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <!-- Master Hero Section -->
    <section class="about-hero">
        <div class="about-pill">
            <span>🌿</span> The Way2Green Charter • Hackathon 2026
        </div>
        <h1 class="about-hero-title">
            Travel Should <span class="highlight">Heal the Earth</span>, Never Leave Anyone Behind.
        </h1>
        <p class="about-hero-lead">
            Way2Green was created to dismantle a silent crisis in global tourism: that seeing the beauty of nature often comes at the cost of polluting it, while excluding travelers with accessibility needs. We bridge clean transit with verified, barrier-free eco-hospitality.
        </p>

        <!-- Live Impact Metric Tiles -->
        <div class="impact-stats-grid">
            <div class="stat-card">
                <div class="stat-num">8%</div>
                <div class="stat-label">Global Tourism Emissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">1.3B</div>
                <div class="stat-label">Accessible Travelers</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">100%</div>
                <div class="stat-label">Audited Zero-Greenwash</div>
            </div>
            <div class="stat-card">
                <div class="stat-num">₹0</div>
                <div class="stat-label">Access Surcharges</div>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <main class="page-container" style="max-width: 1040px;">

        <!-- The Origin Story Split -->
        <div class="story-split">
            <div>
                <span class="section-tag">Why We Started</span>
                <h2 style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: clamp(1.5rem, 3vw, 2.1rem); font-weight: 800; color: #0b2e1b; margin: 6px 0 1rem; line-height: 1.25;">
                    A Crisis of Over-Carbonization and Ignored Inclusivity
                </h2>
                <p style="color: #3f5547; font-size: 0.95rem; line-height: 1.65; margin-bottom: 1rem;">
                    For decades, modern travel platforms operated on a flawed formula: promote high-emission flights to fragile biomes, certify resorts using superficial "paper certificates", and treat wheelchair or neurodivergent travelers as an inconvenient afterthought.
                </p>
                <p style="color: #3f5547; font-size: 0.95rem; line-height: 1.65; margin-bottom: 1.4rem;">
                    We believed there had to be a better way. What if a platform calculated the real carbon avoided when taking scenic electric railways instead of diesel highways? What if every single listed lodge provided <strong>guaranteed step-free access, sensory-quiet accommodations, or tactile braille support</strong> without charging a single extra rupee?
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="travel.php" class="btn-nature-primary" style="padding: 10px 22px;">
                        Plan a Clean Journey ➔
                    </a>
                    <a href="hotels.php" class="btn-nature-outline" style="padding: 10px 20px;">
                        Explore Eco-Stays
                    </a>
                </div>
            </div>

            <div class="story-img-wrap">
                <img src="https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=800&q=80" alt="Lush Tea Hill Canopy in Kerala">
                <div class="story-floating-badge">
                    <span style="font-size: 1.4rem;">🍃</span>
                    <div>
                        <strong>Western Ghats Bio-Corridor</strong>
                        <div style="font-size: 0.74rem; color: #a7f3d0;">Verified Zero-Emission Rail Corridor</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- The Two Pillars of Way2Green -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <span class="section-tag">Our Dual Mandate</span>
            <h2 class="section-title">Two Inseparable Commitments</h2>
            <p class="section-desc" style="max-width: 620px; margin: 0 auto;">Sustainability and Accessibility are not separate checkboxes. At Way2Green, they are the exact same promise.</p>
        </div>

        <div class="pillar-grid">
            
            <!-- Pillar 1 -->
            <div class="pillar-card">
                <div>
                    <div class="pillar-icon-box" style="background: linear-gradient(135deg, #10b981 0%, #064e3b 100%); color: #ffffff;">
                        🚆
                    </div>
                    <h3 class="pillar-title">1. Transparent Environmental Regeneration</h3>
                    <p class="pillar-desc">
                        We reject vague corporate carbon offsets. Instead of asking you to plant hypothetical trees, we help you avoid emissions from the start through electrified railways, low-impact transit routing, and on-site verified resource preservation.
                    </p>
                    
                    <div class="pillar-feature-stack">
                        <div class="pillar-feature-item">
                            <div class="pillar-feature-icon">✓</div>
                            <div class="pillar-feature-body">
                                <div class="pillar-feature-title">Kilogram-Accurate CO₂ Tracking</div>
                                <div class="pillar-feature-desc">Real mathematical equations comparing electric rail vs flight emissions.</div>
                            </div>
                        </div>

                        <div class="pillar-feature-item">
                            <div class="pillar-feature-icon">✓</div>
                            <div class="pillar-feature-body">
                                <div class="pillar-feature-title">Rainwater & Solar Stewardship</div>
                                <div class="pillar-feature-desc">Verified annual metrics on liters of water preserved and clean solar kWh generated.</div>
                            </div>
                        </div>

                        <div class="pillar-feature-item">
                            <div class="pillar-feature-icon">✓</div>
                            <div class="pillar-feature-body">
                                <div class="pillar-feature-title">100% Digital Eco-Passport</div>
                                <div class="pillar-feature-desc">A cryptographic proof of net-zero footprint issued directly with every stay.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pillar 2 -->
            <div class="pillar-card">
                <div>
                    <div class="pillar-icon-box" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff;">
                        ♿
                    </div>
                    <h3 class="pillar-title">2. Dignified Universal Accessibility</h3>
                    <p class="pillar-desc">
                        True luxury is welcoming everyone. We audit every property for physical, auditory, sensory, and visual adaptations so travelers with disabilities can explore nature with complete peace of mind.
                    </p>

                    <div class="pillar-feature-stack">
                        <div class="pillar-feature-item">
                            <div class="pillar-feature-icon">✓</div>
                            <div class="pillar-feature-body">
                                <div class="pillar-feature-title">Step-Free & Wheelchair Ramps</div>
                                <div class="pillar-feature-desc">Zero-barrier roll-in suites, bathrooms, and paved nature dining pathways.</div>
                            </div>
                        </div>

                        <div class="pillar-feature-item">
                            <div class="pillar-feature-icon">✓</div>
                            <div class="pillar-feature-body">
                                <div class="pillar-feature-title">Sensory Low-Stimulation Suites</div>
                                <div class="pillar-feature-desc">Acoustic isolation and soothing lighting tailored for neurodivergent travelers.</div>
                            </div>
                        </div>

                        <div class="pillar-feature-item">
                            <div class="pillar-feature-icon">✓</div>
                            <div class="pillar-feature-body">
                                <div class="pillar-feature-title">Zero Accessibility Surcharges</div>
                                <div class="pillar-feature-desc">Ground-floor room access and registered service animal support at identical rates.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- The 3-Step Journey Architecture -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <span class="section-tag">How It Works</span>
            <h2 class="section-title">The Way2Green 3-Step Ecosystem</h2>
            <p class="section-desc" style="max-width: 640px; margin: 0 auto;">A seamless sequence taking you from clean origin routing to your verified digital passport.</p>
        </div>

        <div class="step-flow-grid">
            <div class="flow-step-card">
                <span class="flow-step-badge">Phase 1</span>
                <div style="font-size: 2rem; margin-bottom: 8px;">🚆</div>
                <h3 style="color: #064e3b; font-size: 1.15rem; font-weight: 800; margin-bottom: 6px;">Clean Transit Routing</h3>
                <p style="color: #4b6354; font-size: 0.88rem; line-height: 1.55; margin: 0;">
                    Select your origin and destination corridor. Our engine computes distance, compares train, bus, and EV options, and tallies the exact kilograms of carbon avoided.
                </p>
            </div>

            <div class="flow-step-card">
                <span class="flow-step-badge">Phase 2</span>
                <div style="font-size: 2rem; margin-bottom: 8px;">🏨</div>
                <h3 style="color: #064e3b; font-size: 1.15rem; font-weight: 800; margin-bottom: 6px;">Certified Eco-Sanctuary</h3>
                <p style="color: #4b6354; font-size: 0.88rem; line-height: 1.55; margin: 0;">
                    Filter through hand-audited eco-lodges across Munnar, Manali, Wayanad, Goa, Rishikesh, and Ooty. Check verified solar ratings and accessibility accommodations.
                </p>
            </div>

            <div class="flow-step-card">
                <span class="flow-step-badge">Phase 3</span>
                <div style="font-size: 2rem; margin-bottom: 8px;">📜</div>
                <h3 style="color: #064e3b; font-size: 1.15rem; font-weight: 800; margin-bottom: 6px;">Digital Eco-Passport</h3>
                <p style="color: #4b6354; font-size: 0.88rem; line-height: 1.55; margin: 0;">
                    Receive an official verified certificate documenting your avoided emissions, preserved water volume, and accessibility accommodations ready for 1-page A4 printing.
                </p>
            </div>
        </div>

        <!-- Ethics Banner (Fixing Text Contrast & Alignment) -->
        <div class="ethics-banner">
            <span class="ethics-badge">Our Code of Conduct</span>
            <h3 class="ethics-main-title">
                The Anti-Greenwashing Promise
            </h3>
            <p class="ethics-subtitle">
                Too many platforms profit off misleading labels. Here is how we ensure every property and route on Way2Green is genuinely responsible:
            </p>

            <div class="ethics-grid">
                <div class="ethics-tile">
                    <h4>1. Hard Audited Data</h4>
                    <p>We do not accept self-declared green claims. Properties must demonstrate actual rainwater collection and solar generation systems.</p>
                </div>
                <div class="ethics-tile">
                    <h4>2. Radical Inclusivity</h4>
                    <p>No property is certified unless at least 2 distinct accessibility standards (wheelchair, sensory, braille, or hearing) are actively supported.</p>
                </div>
                <div class="ethics-tile">
                    <h4>3. 70%+ Local Hiring</h4>
                    <p>True sustainability supports indigenous communities. Our partners source locally grown produce and guarantee fair living wages.</p>
                </div>
                <div class="ethics-tile">
                    <h4>4. Public Ledger Proof</h4>
                    <p>Every booking generates a unique verification hash so travelers, schools, or employers can independently verify carbon savings.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div style="text-align: center; margin-bottom: 1.8rem;">
            <span class="section-tag">Common Questions</span>
            <h2 class="section-title">Frequently Asked Questions</h2>
        </div>

        <div class="faq-grid">
            <div class="faq-item">
                <div class="faq-q">
                    <span>🌱</span> How do you calculate the CO₂ avoided?
                </div>
                <p class="faq-a">
                    We compare your selected electrified transit mode against average domestic flight or single-occupant petrol vehicle benchmarks (0.19 kg CO₂/km baseline). Choosing electric rail eliminates over 80% of travel emissions.
                </p>
            </div>

            <div class="faq-item">
                <div class="faq-q">
                    <span>♿</span> Are accessibility accommodations really free?
                </div>
                <p class="faq-a">
                    Yes, absolutely 100%. We contractually forbid our partnered lodges from adding extra fees for wheelchair access, ground-floor room assignments, sensory quiet preparation, or service animal stays.
                </p>
            </div>

            <div class="faq-item">
                <div class="faq-q">
                    <span>📜</span> What can I do with my Eco-Passport?
                </div>
                <p class="faq-a">
                    Your Eco-Passport is an authenticated sustainability certificate. You can print it as an official 1-page A4 document, save it as a PDF, or use it for academic and corporate ESG offset tracking.
                </p>
            </div>

            <div class="faq-item">
                <div class="faq-q">
                    <span>🤖</span> How does Gemini AI assist on the platform?
                </div>
                <p class="faq-a">
                    We are integrating Google Gemini API to analyze customized transit alternatives, suggest personalized accessibility accommodations based on specific traveler profiles, and offer live green travel tips.
                </p>
            </div>
        </div>

        <!-- Bottom CTA -->
        <div class="about-cta-card">
            <div style="font-size: 2.2rem; margin-bottom: 6px;">🌍</div>
            <h3 style="color: #064e3b; font-size: clamp(1.4rem, 3vw, 1.8rem); font-weight: 800; margin-bottom: 8px;">Ready to Experience Conscious Travel?</h3>
            <p style="color: #4b6354; font-size: 0.95rem; max-width: 560px; margin: 0 auto 1.6rem; line-height: 1.6;">
                Pick your route, choose an inclusive eco-resort, and hold your verified certificate of avoided carbon footprint today.
            </p>
            <div class="about-cta-actions">
                <a href="travel.php" class="btn-nature-primary" style="padding: 12px 24px; font-size: 0.95rem;">
                    🚆 Plan Your Route Now
                </a>
                <a href="hotels.php" class="btn-nature-outline" style="padding: 12px 22px; font-size: 0.95rem;">
                    🏨 Browse All Eco-Stays
                </a>
            </div>
        </div>

    </main>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">🌍 Way2Green</div>
                <p class="footer-text">
                    A conscious travel platform empowering people to discover nature gently, cleanly, and barrier-free.
                </p>
            </div>
            <div>
                <h4 class="footer-heading">Navigation</h4>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="travel.php">Phase 1: Transit</a>
                    <a href="hotels.php">Phase 2: Eco-Hotels</a>
                    <a href="about.php">About Mission</a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Access</h4>
                <div class="footer-links">
                    <?php if ($user): ?>
                        <a href="my-trips.php">My Passports</a>
                        <a href="logout.php">Sign Out</a>
                    <?php else: ?>
                        <a href="login.php">Sign In</a>
                        <a href="register.php">Create Account</a>
                    <?php endif; ?>
                    <a href="admin/login.php">Admin Login</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            © 2026 Way2Green • Built for Green & Inclusive Travel Hackathon.
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-bar">
        <a href="index.php" class="mobile-nav-item">
            <span class="icon">🏡</span>
            <span class="label">Home</span>
        </a>
        <a href="travel.php" class="mobile-nav-item">
            <span class="icon">🚆</span>
            <span class="label">Transit</span>
        </a>
        <a href="hotels.php" class="mobile-nav-item">
            <span class="icon">🏨</span>
            <span class="label">Stays</span>
        </a>
        <a href="about.php" class="mobile-nav-item active">
            <span class="icon">🌿</span>
            <span class="label">About</span>
        </a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="mobile-nav-item">
                <span class="icon">📜</span>
                <span class="label">Passport</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="mobile-nav-item">
                <span class="icon">👤</span>
                <span class="label">Sign In</span>
            </a>
        <?php endif; ?>
    </div>

    <script src="js/effects.js"></script>
    <script>
        function toggleDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            const overlay = document.getElementById('drawerOverlay');
            drawer.classList.toggle('open');
            overlay.classList.toggle('active');
        }
    </script>
</body>
</html>

