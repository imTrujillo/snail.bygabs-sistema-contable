<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caracol Studio</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --brown: #473919;
            --brown-mid: #6b5527;
            --brown-light: #9a7c46;
            --cream: #F5EFDB;
            --cream-dark: #ede3c4;
            --cream-deep: #d9ccaa;
            --white: #fefcf7;
            --text-dark: #2a1f0a;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--cream);
            color: var(--brown);
            font-family: 'Cormorant Garamond', serif;
            overflow-x: hidden;
            cursor: none;
        }

        /* ── Custom cursor ── */
        .cursor {
            position: fixed;
            top: 0;
            left: 0;
            width: 12px;
            height: 12px;
            background: var(--brown);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease, background 0.3s;
        }

        .cursor-ring {
            position: fixed;
            top: 0;
            left: 0;
            width: 36px;
            height: 36px;
            border: 1px solid var(--brown-light);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9998;
            transform: translate(-50%, -50%);
            transition: transform 0.18s ease, width 0.3s, height 0.3s, border-color 0.3s;
        }

        a:hover~.cursor-ring,
        button:hover~.cursor-ring {
            width: 56px;
            height: 56px;
        }

        /* ── Grain overlay ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 1000;
            opacity: 0.55;
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
            overflow: hidden;
        }

        .hero-left {
            background: var(--brown);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            padding: 4rem 5rem 4rem 4rem;
            position: relative;
            overflow: hidden;
        }

        /* Decorative arcs on left panel */
        .hero-left::before {
            content: '';
            position: absolute;
            bottom: -120px;
            right: -120px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            border: 1px solid rgba(245, 239, 219, 0.12);
        }

        .hero-left::after {
            content: '';
            position: absolute;
            top: -80px;
            left: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 1px solid rgba(245, 239, 219, 0.08);
        }

        .logo-wrapper {
            text-align: right;
            animation: fadeUp 1s ease both;
        }

        .logo-img {
            width: 160px;
            height: 160px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid rgba(245, 239, 219, 0.25);
            margin-bottom: 2rem;
            filter: brightness(1.05);
        }

        .brand-name {
            font-family: 'Cinzel', serif;
            font-size: clamp(2.8rem, 4vw, 4.2rem);
            font-weight: 400;
            color: var(--cream);
            letter-spacing: 0.15em;
            line-height: 1.1;
            text-transform: lowercase;
        }

        .brand-sub {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            font-weight: 300;
            font-style: italic;
            color: var(--cream-deep);
            letter-spacing: 0.4em;
            text-transform: uppercase;
            margin-top: 0.6rem;
        }

        .divider-line {
            width: 60px;
            height: 1px;
            background: var(--brown-light);
            margin: 2rem 0 2rem auto;
            animation: expand 1.2s 0.4s ease both;
        }

        @keyframes expand {
            from {
                width: 0;
                opacity: 0;
            }

            to {
                width: 60px;
                opacity: 1;
            }
        }

        .tagline {
            font-size: 1.05rem;
            font-weight: 300;
            color: rgba(245, 239, 219, 0.75);
            letter-spacing: 0.06em;
            text-align: right;
            max-width: 260px;
            line-height: 1.8;
            animation: fadeUp 1s 0.3s ease both;
        }

        /* ── RIGHT PANEL ── */
        .hero-right {
            background: var(--cream);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 4rem 4rem 5rem;
            position: relative;
            animation: fadeIn 1.2s 0.2s ease both;
        }

        /* Spiral/shell decorative SVG */
        .deco-shell {
            position: absolute;
            bottom: 2rem;
            right: 2rem;
            width: 200px;
            opacity: 0.07;
        }

        .eyebrow {
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.78rem;
            font-weight: 400;
            letter-spacing: 0.5em;
            text-transform: uppercase;
            color: var(--brown-light);
            margin-bottom: 2rem;
            animation: fadeUp 1s 0.5s ease both;
        }

        .hero-headline {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 3.5vw, 3.4rem);
            font-weight: 300;
            line-height: 1.25;
            color: var(--brown);
            margin-bottom: 2rem;
            animation: fadeUp 1s 0.65s ease both;
        }

        .hero-headline em {
            font-style: italic;
            color: var(--brown-light);
        }

        .hero-body {
            font-size: 1.05rem;
            font-weight: 300;
            line-height: 1.9;
            color: var(--brown-mid);
            max-width: 380px;
            margin-bottom: 3rem;
            animation: fadeUp 1s 0.8s ease both;
        }

        /* CTA button */
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            background: var(--brown);
            color: var(--cream);
            text-decoration: none;
            font-family: 'Cinzel', serif;
            font-size: 0.78rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            padding: 1.1rem 2.4rem;
            border: 1px solid var(--brown);
            transition: background 0.4s, color 0.4s, gap 0.3s;
            animation: fadeUp 1s 0.95s ease both;
            position: relative;
            overflow: hidden;
        }

        .cta-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--brown-light);
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.77, 0, 0.175, 1);
            z-index: 0;
        }

        .cta-btn:hover::before {
            transform: translateX(0);
        }

        .cta-btn span,
        .cta-btn svg {
            position: relative;
            z-index: 1;
        }

        .cta-btn:hover {
            gap: 1.5rem;
        }

        .cta-arrow {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.5;
            transition: transform 0.3s;
        }

        .cta-btn:hover .cta-arrow {
            transform: translateX(4px);
        }

        /* ── SERVICES STRIP ── */
        .services-strip {
            background: var(--cream-dark);
            border-top: 1px solid var(--cream-deep);
            border-bottom: 1px solid var(--cream-deep);
            padding: 2.4rem 4rem;
            display: flex;
            justify-content: center;
            gap: 4rem;
            flex-wrap: wrap;
        }

        .service-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            animation: fadeUp 0.7s ease forwards;
        }

        .service-item:nth-child(1) {
            animation-delay: 0.1s;
        }

        .service-item:nth-child(2) {
            animation-delay: 0.2s;
        }

        .service-item:nth-child(3) {
            animation-delay: 0.3s;
        }

        .service-item:nth-child(4) {
            animation-delay: 0.4s;
        }

        .service-item:nth-child(5) {
            animation-delay: 0.5s;
        }

        .service-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--brown-light);
        }

        .service-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.75rem;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: var(--brown-mid);
            white-space: nowrap;
        }

        /* ── FEATURES SECTION ── */
        .features {
            padding: 7rem 8rem;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 4rem;
        }

        .feature-card {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            padding: 2.5rem;
            border: 1px solid transparent;
            transition: border-color 0.3s, background 0.3s;
            position: relative;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 32px;
            height: 1px;
            background: var(--brown-light);
            transition: width 0.4s;
        }

        .feature-card:hover::before {
            width: 100%;
        }

        .feature-card:hover {
            border-color: var(--cream-deep);
            background: var(--white);
        }

        .feature-num {
            font-family: 'Cinzel', serif;
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            color: var(--brown-light);
        }

        .feature-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 400;
            color: var(--brown);
            line-height: 1.3;
        }

        .feature-desc {
            font-size: 0.95rem;
            font-weight: 300;
            color: var(--brown-mid);
            line-height: 1.85;
        }

        /* ── FOOTER ── */
        .footer {
            background: var(--brown);
            color: var(--cream);
            padding: 3rem 8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-brand {
            font-family: 'Cinzel', serif;
            font-size: 1.2rem;
            letter-spacing: 0.2em;
            text-transform: lowercase;
            color: var(--cream);
        }

        .footer-copy {
            font-size: 0.8rem;
            font-weight: 300;
            color: rgba(245, 239, 219, 0.5);
            letter-spacing: 0.1em;
        }

        .footer-cta {
            font-family: 'Cinzel', serif;
            font-size: 0.7rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--cream-deep);
            text-decoration: none;
            border-bottom: 1px solid rgba(245, 239, 219, 0.3);
            padding-bottom: 2px;
            transition: color 0.3s, border-color 0.3s;
        }

        .footer-cta:hover {
            color: var(--cream);
            border-color: var(--cream);
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-left {
                align-items: center;
                padding: 4rem 2rem;
                min-height: 50vh;
            }

            .logo-wrapper {
                text-align: center;
            }

            .brand-name {
                font-size: 2.8rem;
            }

            .divider-line {
                margin: 1.5rem auto;
            }

            .tagline {
                text-align: center;
            }

            .hero-right {
                padding: 4rem 2rem;
                align-items: center;
                text-align: center;
            }

            .hero-body {
                text-align: center;
            }

            .features {
                grid-template-columns: 1fr;
                padding: 4rem 2rem;
                gap: 2rem;
            }

            .footer {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
                padding: 3rem 2rem;
            }

            .services-strip {
                padding: 2rem 2rem;
                gap: 2.5rem;
            }
        }
    </style>
</head>

<body>

    <!-- Custom cursor -->
    <div class="cursor" id="cursor"></div>
    <div class="cursor-ring" id="cursorRing"></div>

    <!-- ── HERO ── -->
    <section class="hero">

        <!-- LEFT: Brand identity panel -->
        <div class="hero-left">
            <div class="logo-wrapper">
                <img src="/logo.png" alt="Caracol Studio" class="logo-img" onerror="this.style.display='none'">
                <div class="brand-name">caracol<br>studio</div>
                <div class="brand-sub">nail art & design</div>
                <div class="divider-line"></div>
                <p class="tagline">Belleza que se <em>siente</em> y se recuerda — en cada detalle.</p>
            </div>
        </div>

        <!-- RIGHT: CTA panel -->
        <div class="hero-right">

            <!-- Decorative shell SVG -->
            <svg class="deco-shell" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 100 C100 60 60 20 100 10 C140 0 180 40 170 80 C160 120 120 140 100 170
                         C80 140 40 120 30 80 C20 40 60 0 100 10" stroke="#473919" stroke-width="1" fill="none" />
                <path d="M100 100 C100 70 72 42 100 32 C128 22 158 52 150 80 C142 108 116 124 100 148
                         C84 124 58 108 50 80 C42 52 72 22 100 32" stroke="#473919" stroke-width="0.8"
                    fill="none" />
                <circle cx="100" cy="100" r="4" stroke="#473919" stroke-width="0.8" />
                <circle cx="100" cy="100" r="16" stroke="#473919" stroke-width="0.5" />
                <circle cx="100" cy="100" r="30" stroke="#473919" stroke-width="0.4" />
                <circle cx="100" cy="100" r="48" stroke="#473919" stroke-width="0.3" />
                <circle cx="100" cy="100" r="68" stroke="#473919" stroke-width="0.2" />
                <circle cx="100" cy="100" r="90" stroke="#473919" stroke-width="0.15" />
            </svg>

            <p class="eyebrow">Sistema de gestión &nbsp;·&nbsp; Registra</p>

            <h1 class="hero-headline">
                Control total de<br>
                tu <em>negocio</em>,<br>
                al alcance de tu mano.
            </h1>

            <p class="hero-body">
                Gestiona citas, ventas, inventario, planilla y reportes financieros desde un solo lugar.
                Diseñado especialmente para Caracol Studio.
            </p>

            <a href="/admin/login" class="cta-btn">
                <span>Acceder al sistema</span>
                <svg class="cta-arrow" viewBox="0 0 24 24">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>

        </div>
    </section>

    <!-- ── SERVICES STRIP ── -->
    <div class="services-strip">
        <div class="service-item">
            <div class="service-dot"></div>
            <span class="service-name">Uñas Acrílicas</span>
        </div>
        <div class="service-item">
            <div class="service-dot"></div>
            <span class="service-name">Nail Art</span>
        </div>
        <div class="service-item">
            <div class="service-dot"></div>
            <span class="service-name">Semipermanente</span>
        </div>
        <div class="service-item">
            <div class="service-dot"></div>
            <span class="service-name">Esculpidas</span>
        </div>
        <div class="service-item">
            <div class="service-dot"></div>
            <span class="service-name">Diseños Personalizados</span>
        </div>
    </div>

    <!-- ── FEATURES ── -->
    <section class="features">
        <div class="feature-card">
            <span class="feature-num">01</span>
            <h3 class="feature-title">Registro de Operaciones</h3>
            <p class="feature-desc">Lleva el control de cada venta, compra y pago con trazabilidad completa y
                documentación automática.</p>
        </div>
        <div class="feature-card">
            <span class="feature-num">02</span>
            <h3 class="feature-title">Libros de IVA</h3>
            <p class="feature-desc">Genera libros de compras y ventas (FCF/CCF) de forma automática, listos para
                cumplimiento fiscal.</p>
        </div>
        <div class="feature-card">
            <span class="feature-num">03</span>
            <h3 class="feature-title">Reportes Financieros</h3>
            <p class="feature-desc">Estado de Resultados y Balance General actualizados en tiempo real para tomar
                decisiones con confianza.</p>
        </div>
        <div class="feature-card">
            <span class="feature-num">04</span>
            <h3 class="feature-title">Control de Inventario</h3>
            <p class="feature-desc">Monitorea insumos, productos y materiales con alertas de stock para nunca quedarte
                sin lo necesario.</p>
        </div>
        <div class="feature-card">
            <span class="feature-num">05</span>
            <h3 class="feature-title">Planilla</h3>
            <p class="feature-desc">Administra salarios, descuentos y prestaciones de tus empleadas de manera simple y
                ordenada.</p>
        </div>
        <div class="feature-card">
            <span class="feature-num">06</span>
            <h3 class="feature-title">Citas & Clientes</h3>
            <p class="feature-desc">Gestiona tu agenda, historial de clientes y servicios desde el mismo sistema, sin
                herramientas externas.</p>
        </div>
    </section>

    <!-- ── FOOTER ── -->
    <footer class="footer">
        <span class="footer-brand">caracol studio</span>
        <span class="footer-copy">© {{ date('Y') }} · Sistema Registra · Todos los derechos reservados</span>
        <a href="/admin/login" class="footer-cta">Iniciar sesión</a>
    </footer>

    <script>
        // Custom cursor
        const cursor = document.getElementById('cursor');
        const ring = document.getElementById('cursorRing');
        let mx = 0,
            my = 0,
            rx = 0,
            ry = 0;

        document.addEventListener('mousemove', e => {
            mx = e.clientX;
            my = e.clientY;
            cursor.style.left = mx + 'px';
            cursor.style.top = my + 'px';
        });

        // Smooth ring follow
        function animRing() {
            rx += (mx - rx) * 0.12;
            ry += (my - ry) * 0.12;
            ring.style.left = rx + 'px';
            ring.style.top = ry + 'px';
            requestAnimationFrame(animRing);
        }
        animRing();

        // Hover expand ring
        document.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('mouseenter', () => {
                ring.style.width = '56px';
                ring.style.height = '56px';
                ring.style.borderColor = '#473919';
            });
            el.addEventListener('mouseleave', () => {
                ring.style.width = '36px';
                ring.style.height = '36px';
                ring.style.borderColor = '#9a7c46';
            });
        });

        // Intersection observer for feature cards
        const observer = new IntersectionObserver(entries => {
            entries.forEach((e, i) => {
                if (e.isIntersecting) {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.15
        });

        document.querySelectorAll('.feature-card').forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition =
                `opacity 0.6s ${i * 0.1}s ease, transform 0.6s ${i * 0.1}s ease, border-color 0.3s, background 0.3s`;
            observer.observe(el);
        });
    </script>
</body>

</html>
