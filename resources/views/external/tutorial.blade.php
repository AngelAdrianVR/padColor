<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial Sistema Pre-Pedidos PadColor · v6.0</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --pad-blue: #001AA6;
            --pad-magenta: #E016FF;
            --pad-dark: #0A0F2C;
            --pad-light: #F5F6FA;
            --pad-gray: #6B7280;
            --pad-border: #E5E7EB;
            --pad-success: #10B981;
            --pad-warn: #F59E0B;
            --pad-danger: #EF4444;
            --pad-yellow-bg: #FEF7E0;
            --pad-yellow-border: #F4D35E;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: #0A0F2C;
            color: #111;
            overflow: hidden;
        }

        /* ============ APP SCENE (mockup of real interface) ============ */
        .app-scene {
            position: fixed;
            inset: 0;
            background: #FFFFFF;
            overflow: hidden;
        }

        /* ---- Top header bar ---- */
        .app-header {
            background: white;
            padding: 16px 32px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--pad-border);
        }

        .app-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .app-logo-mark {
            width: 46px;
            height: 46px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 3px;
        }

        .app-logo-mark span {
            background: var(--pad-blue);
            border-radius: 3px;
        }

        .app-logo-mark span:nth-child(2) {
            background: var(--pad-magenta);
        }

        .app-logo-mark span:nth-child(3) {
            background: var(--pad-magenta);
        }

        .app-logo-mark span:nth-child(4) {
            background: var(--pad-blue);
        }

        .app-title h1 {
            font-family: 'Archivo Black', sans-serif;
            font-size: 22px;
            color: var(--pad-dark);
            line-height: 1.1;
        }

        .app-title .subtitle {
            font-size: 12px;
            color: var(--pad-gray);
            margin-top: 2px;
        }

        .app-title .subtitle b {
            color: var(--pad-magenta);
            font-weight: 700;
        }

        .app-header-right {
            display: flex;
            align-items: center;
            gap: 36px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--pad-gray);
        }

        .header-link {
            text-align: center;
        }

        .header-link .label {
            font-weight: 700;
            color: var(--pad-dark);
        }

        .header-link .desc {
            color: var(--pad-gray);
            font-size: 10px;
            margin-top: 2px;
            text-transform: none;
            letter-spacing: 0;
        }

        .header-link .bar {
            width: 30px;
            height: 2px;
            background: var(--pad-border);
            margin: 6px auto 0;
        }

        /* ---- Main grid: catalog (left) + cart (right) ---- */
        .app-body {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 22px;
            padding: 22px 32px;
            height: calc(100% - 78px);
            overflow: hidden;
        }

        /* ============ LEFT: catalog area ============ */
        .catalog-area {
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: 0;
        }

        .search-bar {
            background: white;
            border: 1.5px solid var(--pad-border);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .search-bar svg {
            width: 18px;
            height: 18px;
            color: var(--pad-gray);
            flex-shrink: 0;
        }

        .search-bar input {
            border: none;
            flex: 1;
            font-size: 14px;
            color: var(--pad-gray);
            font-family: 'Inter', sans-serif;
            outline: none;
            background: transparent;
        }

        .filter-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            border: 1.5px solid var(--pad-border);
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            color: var(--pad-dark);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .filter-chip .chip-label {
            font-size: 11px;
            color: var(--pad-gray);
            font-weight: 600;
        }

        .filter-chip .chip-value {
            font-weight: 700;
            color: var(--pad-dark);
        }

        .filter-chip .count-pill {
            background: var(--pad-blue);
            color: white;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 11px;
            font-weight: 700;
            min-width: 18px;
            text-align: center;
        }

        .filter-chip .count-pill.magenta {
            background: var(--pad-magenta);
        }

        .filter-chip .arrow-down {
            color: var(--pad-gray);
            font-size: 10px;
        }

        .showing-text {
            font-size: 13px;
            color: var(--pad-gray);
            margin-left: 4px;
        }

        .showing-text b {
            color: var(--pad-dark);
            font-weight: 700;
        }

        /* yellow note banner */
        .info-banner {
            background: var(--pad-yellow-bg);
            border: 1.5px solid var(--pad-yellow-border);
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #5C4810;
        }

        .info-banner .info-icon {
            width: 18px;
            height: 18px;
            background: #D4A23A;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 11px;
            flex-shrink: 0;
        }

        .info-banner b {
            font-weight: 700;
        }

        /* product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            overflow-y: auto;
            padding-bottom: 8px;
            flex: 1;
            min-height: 0;
        }

        .product-card {
            background: white;
            border: 1.5px solid var(--pad-border);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
        }

        .product-img {
            height: 190px;
            background: linear-gradient(135deg, #FFD7E1, #C5E3FF);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-img.alphabet {
            background: linear-gradient(135deg, #FFE0B5, #FFB3D9, #B5D4FF);
        }

        .product-img.labels {
            background: linear-gradient(135deg, #FFE5F0, #E5F0FF);
        }

        .product-img.notebook {
            background: linear-gradient(135deg, #B5FFD9, #FFEAA5);
        }

        .product-img.bag {
            background: linear-gradient(135deg, #D5B5FF, #FFC5D5);
        }

        .product-img.paper {
            background: linear-gradient(135deg, #FFD5B5, #B5E5FF);
        }

        .product-img.box {
            background: linear-gradient(135deg, #C5FFB5, #FFE5C5);
        }

        .pag-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.95);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--pad-dark);
        }

        .nuevo-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--pad-magenta);
            color: white;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            transform: rotate(-3deg);
        }

        .product-info {
            padding: 12px;
            font-size: 12px;
            color: var(--pad-gray);
        }

        .product-code {
            font-family: 'Inter', monospace;
            color: var(--pad-dark);
            font-weight: 600;
            font-size: 12px;
        }

        .product-controls {
            padding: 10px 12px 12px;
            display: flex;
            gap: 8px;
            align-items: center;
            border-top: 1px solid var(--pad-border);
        }

        .qty-stepper {
            display: flex;
            align-items: center;
            border: 1.5px solid var(--pad-border);
            border-radius: 8px;
            overflow: hidden;
            flex: 1;
        }

        .qty-stepper button {
            background: var(--pad-light);
            border: none;
            width: 26px;
            height: 30px;
            color: var(--pad-blue);
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .qty-stepper input {
            width: 40px;
            border: none;
            text-align: center;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            outline: none;
            color: var(--pad-dark);
        }

        .agregar-btn {
            background: linear-gradient(135deg, var(--pad-magenta), #B400D6);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* highlighted card (paso 3) */
        #productCardFeatured {
            /* placeholder hook for spotlight */
        }

        /* ============ RIGHT: Cart panel ============ */
        .cart-panel {
            background: white;
            border: 1.5px solid var(--pad-border);
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
        }

        .cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--pad-border);
        }

        .cart-header h3 {
            font-family: 'Archivo Black', sans-serif;
            font-size: 18px;
            color: var(--pad-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-header .cart-icon {
            color: var(--pad-blue);
            width: 22px;
            height: 22px;
        }

        .cart-header .product-count {
            font-size: 12px;
            color: var(--pad-gray);
        }

        .cart-section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--pad-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .cart-section-title .req {
            color: var(--pad-magenta);
        }

        .cart-field input {
            width: 100%;
            padding: 11px 13px;
            border: 1.5px solid var(--pad-border);
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--pad-dark);
            outline: none;
            transition: border-color 0.2s;
        }

        .cart-field input::placeholder {
            color: #B0B5BD;
        }

        .cart-field input:focus {
            border-color: var(--pad-blue);
        }

        .cart-field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .pedido-minimo {
            background: var(--pad-yellow-bg);
            border: 1.5px solid var(--pad-yellow-border);
            border-radius: 10px;
            padding: 13px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #5C4810;
        }

        .total-box {
            background: linear-gradient(135deg, #FAFAFC, #F5F6FA);
            border: 1.5px solid var(--pad-border);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }

        .total-box .total-label {
            font-size: 11px;
            color: var(--pad-gray);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
        }

        .total-box .total-amount {
            font-family: 'Archivo Black', sans-serif;
            font-size: 34px;
            color: var(--pad-dark);
            margin-top: 4px;
        }

        .finalize-btn {
            background: var(--pad-light);
            color: #B0B5BD;
            border: 1.5px solid var(--pad-border);
            padding: 16px;
            border-radius: 12px;
            font-family: 'Archivo Black', sans-serif;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: not-allowed;
            width: 100%;
        }

        /* ============ SPOTLIGHT OVERLAY ============ */
        .spotlight-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 100;
            transition: opacity 0.4s ease;
        }

        .spotlight-mask {
            position: absolute;
            inset: 0;
            background: rgba(10, 15, 44, 0.82);
            -webkit-mask: radial-gradient(circle var(--r, 120px) at var(--x, 50%) var(--y, 50%), transparent 70%, black 72%);
            mask: radial-gradient(circle var(--r, 120px) at var(--x, 50%) var(--y, 50%), transparent 70%, black 72%);
            transition: -webkit-mask 0.6s cubic-bezier(0.65, 0, 0.35, 1),
                mask 0.6s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .spotlight-ring {
            position: absolute;
            border: 3px solid var(--pad-magenta);
            border-radius: 50%;
            box-shadow:
                0 0 0 6px rgba(224, 22, 255, 0.25),
                0 0 50px rgba(224, 22, 255, 0.6),
                inset 0 0 30px rgba(224, 22, 255, 0.2);
            pointer-events: none;
            transition: all 0.6s cubic-bezier(0.65, 0, 0.35, 1);
            animation: pulse-ring 2s ease-in-out infinite;
        }

        @keyframes pulse-ring {

            0%,
            100% {
                box-shadow:
                    0 0 0 6px rgba(224, 22, 255, 0.25),
                    0 0 50px rgba(224, 22, 255, 0.6),
                    inset 0 0 30px rgba(224, 22, 255, 0.2);
            }

            50% {
                box-shadow:
                    0 0 0 12px rgba(224, 22, 255, 0.1),
                    0 0 70px rgba(224, 22, 255, 0.8),
                    inset 0 0 40px rgba(224, 22, 255, 0.3);
            }
        }

        .spotlight-cursor {
            position: absolute;
            width: 32px;
            height: 32px;
            pointer-events: none;
            transition: all 0.6s cubic-bezier(0.65, 0, 0.35, 1);
            filter: drop-shadow(0 4px 12px rgba(224, 22, 255, 0.6));
            transform: translate(-4px, -4px);
        }

        /* ============ STEP BUBBLE ============ */
        .step-bubble {
            position: absolute;
            background: white;
            border-radius: 18px;
            padding: 24px 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            width: 380px;
            pointer-events: auto;
            transition: all 0.6s cubic-bezier(0.65, 0, 0.35, 1);
            border: 3px solid transparent;
            background-image: linear-gradient(white, white), linear-gradient(135deg, var(--pad-blue), var(--pad-magenta));
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }

        .step-number-badge {
            position: absolute;
            top: -22px;
            left: 24px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--pad-blue), var(--pad-magenta));
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Archivo Black', sans-serif;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(0, 26, 166, 0.4);
            transform: rotate(-4deg);
        }

        .step-bubble h3 {
            font-family: 'Archivo Black', sans-serif;
            font-size: 22px;
            color: var(--pad-blue);
            margin-top: 18px;
            margin-bottom: 10px;
            line-height: 1.15;
        }

        .step-bubble p {
            font-size: 14px;
            line-height: 1.55;
            color: #374151;
            margin-bottom: 18px;
        }

        .step-bubble p strong {
            color: var(--pad-magenta);
            font-weight: 700;
        }

        /* ============ NAV CONTROLS ============ */
        .step-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 6px;
        }

        .step-dots {
            display: flex;
            gap: 6px;
        }

        .step-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #D1D5DB;
            transition: all 0.3s;
        }

        .step-dot.active {
            background: var(--pad-magenta);
            width: 26px;
            border-radius: 999px;
        }

        .step-dot.done {
            background: var(--pad-blue);
        }

        .nav-btn {
            background: var(--pad-blue);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 26, 166, 0.3);
        }

        .nav-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .nav-btn.primary {
            background: linear-gradient(90deg, var(--pad-blue), var(--pad-magenta));
        }

        .nav-btn.ghost {
            background: transparent;
            color: var(--pad-blue);
            border: 2px solid var(--pad-border);
        }

        /* ============ START SCREEN ============ */
        .start-screen {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at top left, #001AA6 0%, #0A0F2C 70%);
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.6s, visibility 0.6s;
        }

        .start-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .start-content {
            text-align: center;
            color: white;
            max-width: 640px;
            padding: 40px;
        }

        .start-content .logo-mark {
            width: 80px;
            height: 80px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 4px;
            margin: 0 auto 24px;
            transform: rotate(-6deg);
            filter: drop-shadow(0 20px 40px rgba(224, 22, 255, 0.5));
        }

        .start-content .logo-mark span {
            background: white;
            border-radius: 4px;
        }

        .start-content .logo-mark span:nth-child(2) {
            background: var(--pad-magenta);
        }

        .start-content .logo-mark span:nth-child(3) {
            background: var(--pad-magenta);
        }

        .start-content .logo-mark span:nth-child(4) {
            background: white;
        }

        .start-content h1 {
            font-family: 'Archivo Black', sans-serif;
            font-size: 60px;
            line-height: 0.95;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .start-content h1 span {
            background: linear-gradient(90deg, #E016FF, #FF6FFF);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .start-content .tagline {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        .start-content .tagline::before,
        .start-content .tagline::after {
            content: '';
            height: 1px;
            width: 60px;
            background: rgba(255, 255, 255, 0.3);
        }

        .start-content p.lead {
            font-size: 17px;
            opacity: 0.85;
            margin-bottom: 36px;
            line-height: 1.6;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .start-btn {
            background: linear-gradient(90deg, #E016FF, #B400D6);
            color: white;
            border: none;
            padding: 18px 44px;
            border-radius: 12px;
            font-family: 'Archivo Black', sans-serif;
            font-size: 16px;
            letter-spacing: 2px;
            cursor: pointer;
            text-transform: uppercase;
            box-shadow: 0 15px 40px rgba(224, 22, 255, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .start-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(224, 22, 255, 0.6);
        }

        .version-tag {
            margin-top: 32px;
            font-size: 11px;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
        }

        .version-tag b {
            color: rgba(255, 255, 255, 0.7);
        }

        /* ============ END SCREEN ============ */
        .end-screen {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at bottom right, #E016FF 0%, #001AA6 50%, #0A0F2C 100%);
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.6s, visibility 0.6s;
        }

        .end-screen.visible {
            visibility: visible;
            opacity: 1;
        }

        .end-content {
            text-align: center;
            color: white;
            padding: 40px;
            max-width: 600px;
        }

        .check-circle {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(255, 255, 255, 0.3);
            animation: bounce-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .check-circle svg {
            width: 56px;
            height: 56px;
            color: var(--pad-magenta);
        }

        @keyframes bounce-in {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }

        .end-content h1 {
            font-family: 'Archivo Black', sans-serif;
            font-size: 52px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .end-content p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .end-contact {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 18px 24px;
            margin-bottom: 28px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .end-contact .row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .end-contact b {
            color: white;
        }

        /* ============ PROGRESS BAR ============ */
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--pad-magenta), #FF6FFF);
            z-index: 150;
            transition: width 0.4s ease;
            box-shadow: 0 0 12px rgba(224, 22, 255, 0.8);
        }

        /* ============ KEYBOARD HINT ============ */
        .keyboard-hint {
            position: fixed;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.55);
            font-size: 11px;
            z-index: 110;
            letter-spacing: 1px;
            text-transform: uppercase;
            pointer-events: none;
        }

        .keyboard-hint kbd {
            background: rgba(255, 255, 255, 0.15);
            padding: 3px 8px;
            border-radius: 5px;
            margin: 0 3px;
            font-family: 'Inter', sans-serif;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 1000px) {
            .app-body {
                grid-template-columns: 1fr;
            }

            .cart-panel {
                display: none;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .start-content h1 {
                font-size: 40px;
            }

            .step-bubble {
                width: calc(100vw - 40px);
                max-width: 340px;
            }

            .app-header-right {
                gap: 16px;
            }

            .app-header-right .header-link .desc {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- =================== APP MOCKUP SCENE (real PadColor interface) =================== -->
    <div class="app-scene">
        <!-- Header -->
        <div class="app-header">
            <div class="app-header-left">
                <div class="app-logo-mark">
                    <span></span><span></span><span></span><span></span>
                </div>
                <div class="app-title">
                    <h1>Padcolor · Pre-Pedidos</h1>
                    <div class="subtitle">Catálogo Verano 2026 · <b>v6</b></div>
                </div>
            </div>
            <div class="app-header-right">
                <div class="header-link">
                    <div class="label">TU AGENTE ES</div>
                    <div class="desc">Captura tu núm. de cliente</div>
                    <div class="bar"></div>
                </div>
                <div class="header-link">
                    <div class="label">WHATSAPP</div>
                    <div class="bar"></div>
                </div>
                <div class="header-link">
                    <div class="label">CORREO</div>
                    <div class="bar"></div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="app-body">
            <!-- LEFT: catalog -->
            <div class="catalog-area">
                <!-- Search bar -->
                <div class="search-bar" id="searchBar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3-3" />
                    </svg>
                    <input type="text"
                        placeholder="Buscar por código UPC, modelo (ej. 412), descripción, serie o tamaño…">
                </div>

                <!-- Filter chips -->
                <div class="filter-row" id="filterRow">
                    <div class="filter-chip">
                        <span class="chip-label">Página</span>
                        <span class="chip-value">2-110</span>
                    </div>
                    <div class="filter-chip">
                        <span class="chip-label">Grupo de temporada o productos</span>
                        <span class="arrow-down">▼</span>
                    </div>
                    <div class="filter-chip">
                        <span class="chip-value">$E Precio Especial</span>
                        <span class="count-pill">41</span>
                    </div>
                    <div class="filter-chip">
                        <span class="chip-value">✨ Nuevos</span>
                        <span class="count-pill magenta">70</span>
                    </div>
                    <div class="showing-text">Mostrando <b>632</b> de <b>632</b> productos</div>
                </div>

                <!-- Info banner -->
                <div class="info-banner">
                    <div class="info-icon">i</div>
                    <div>Mostrando precios <b>Lista</b>. Captura tu número de cliente y RFC para ver tus precios y
                        habilitar el carrito.</div>
                </div>

                <!-- Product grid -->
                <div class="product-grid">
                    <div class="product-card" id="productCardFeatured">
                        <div class="product-img alphabet">
                            <span class="pag-badge">Pág 2</span>
                            <div
                                style="font-family: 'Archivo Black', sans-serif; font-size: 28px; color: #C2185B; text-shadow: 1px 1px 0 white; transform: rotate(-3deg);">
                                Alfabeto<br>Móvil</div>
                        </div>
                        <div class="product-info">
                            <div class="product-code">682158013738</div>
                        </div>
                        <div class="product-controls">
                            <div class="qty-stepper" id="qtyStepper">
                                <button>−</button>
                                <input type="text" value="0">
                                <button>+</button>
                            </div>
                            <button class="agregar-btn" id="agregarBtn">Agregar</button>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-img alphabet">
                            <span class="pag-badge">Pág 2</span>
                            <div
                                style="font-family: 'Archivo Black', sans-serif; font-size: 26px; color: #1565C0; text-shadow: 1px 1px 0 white; transform: rotate(-2deg);">
                                Alfabeto<br>Móvil Mini</div>
                        </div>
                        <div class="product-info">
                            <div class="product-code">682158014490</div>
                        </div>
                        <div class="product-controls">
                            <div class="qty-stepper">
                                <button>−</button>
                                <input type="text" value="0">
                                <button>+</button>
                            </div>
                            <button class="agregar-btn">Agregar</button>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-img labels">
                            <span class="pag-badge">Pág 3</span>
                            <span class="nuevo-badge">¡Nuevo!</span>
                            <div
                                style="font-family: 'Archivo Black', sans-serif; font-size: 18px; color: #6A1B9A; text-align: center; line-height: 1.1;">
                                Etiquetas<br>Escolares</div>
                        </div>
                        <div class="product-info">
                            <div class="product-code">682158033699</div>
                        </div>
                        <div class="product-controls">
                            <div class="qty-stepper">
                                <button>−</button>
                                <input type="text" value="0">
                                <button>+</button>
                            </div>
                            <button class="agregar-btn">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: cart -->
            <div class="cart-panel" id="cartPanel">
                <div class="cart-header">
                    <h3>
                        <svg class="cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2">
                            <circle cx="9" cy="21" r="1.5" />
                            <circle cx="18" cy="21" r="1.5" />
                            <path d="M3 3h2l2.5 13h12L22 7H6" />
                        </svg>
                        Carrito
                    </h3>
                    <div class="product-count">0 productos</div>
                </div>

                <div>
                    <div class="cart-section-title">Núm. cliente <span class="req">*</span></div>
                    <div class="cart-field">
                        <input type="text" placeholder="Ej: 102-01-00331 o XXXXX">
                    </div>
                </div>

                <div class="cart-field-row">
                    <div>
                        <div class="cart-section-title">Nombre Comercial</div>
                        <div class="cart-field"><input type="text" placeholder=""></div>
                    </div>
                    <div>
                        <div class="cart-section-title">RFC <span class="req">*</span></div>
                        <div class="cart-field"><input type="text" placeholder="RFC del cliente"></div>
                    </div>
                </div>

                <div class="cart-field-row">
                    <div>
                        <div class="cart-section-title">Correo <span class="req">*</span></div>
                        <div class="cart-field"><input type="text" placeholder="cliente@correo.com"></div>
                    </div>
                    <div>
                        <div class="cart-section-title">Celular 10 dígitos <span class="req">*</span></div>
                        <div class="cart-field"><input type="text" placeholder="(XX) XXXX-XXXX"></div>
                    </div>
                </div>

                <div class="pedido-minimo">Pedido mínimo $7,000.00</div>

                <div class="total-box" id="totalBox">
                    <div class="total-label">Total con IVA</div>
                    <div class="total-amount">$0.00</div>
                </div>

                <button class="finalize-btn" id="finalizeBtn">Finalizar pre-pedido</button>
            </div>
        </div>
    </div>

    <!-- =================== SPOTLIGHT OVERLAY =================== -->
    <div class="spotlight-overlay" id="overlay">
        <div class="spotlight-mask" id="mask"></div>
        <div class="spotlight-ring" id="ring"></div>
        <svg class="spotlight-cursor" id="cursor" viewBox="0 0 24 24" fill="none">
            <path d="M5 3 L19 12 L13 13 L17 21 L14 22 L10 14 L5 18 Z" fill="white" stroke="#E016FF"
                stroke-width="2" stroke-linejoin="round" />
        </svg>
        <div class="step-bubble" id="bubble">
            <div class="step-number-badge" id="numberBadge">1</div>
            <h3 id="bubbleTitle">Ingresa tus Datos</h3>
            <p id="bubbleText">Número de cliente y RFC · Tu lista de precios se carga automáticamente.</p>
            <div class="step-controls">
                <button class="nav-btn ghost" id="prevBtn">← Anterior</button>
                <div class="step-dots" id="stepDots"></div>
                <button class="nav-btn primary" id="nextBtn">Siguiente →</button>
            </div>
        </div>
    </div>

    <div class="progress-bar" id="progressBar" style="width: 0;"></div>

    <!-- =================== START SCREEN =================== -->
    <div class="start-screen" id="startScreen">
        <div class="start-content">
            <div class="logo-mark">
                <span></span><span></span><span></span><span></span>
            </div>
            <h1>Tu Pedido,<br><span>Simplificado</span></h1>
            <div class="tagline">Sistema Pre-Pedidos PadColor</div>
            <p class="lead">Te mostraré los <b>5 pasos</b> que necesitas dominar para usar el catálogo en línea.
                Cuando quieras, desde donde quieras.</p>
            <button class="start-btn" id="startBtn">Iniciar Tutorial</button>
            <div class="version-tag">Catálogo Verano 2026 · <b>v6.0</b></div>
        </div>
    </div>

    <!-- =================== END SCREEN =================== -->
    <div class="end-screen" id="endScreen">
        <div class="end-content">
            <div class="check-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <h1>¡Listo!</h1>
            <p>Ya conoces los 5 pasos del Sistema Pre-Pedidos PadColor.<br>Si tu pedido sobrepasa tu límite de crédito,
                llena la aplicación para el trámite express y mándala con tu pedido.</p>
            <div class="end-contact">
                <div class="row">📱 WhatsApp Business · <b>(33) 2679 1930</b></div>
                <div class="row">✉️ <b>auxventas@padcolor.com</b> · 🌐 <b>padcolor.com</b></div>
            </div>
            <button class="start-btn" id="restartBtn">Reiniciar Tutorial</button>
        </div>
    </div>

    <div class="keyboard-hint">
        Usa <kbd>←</kbd> <kbd>→</kbd> para navegar · <kbd>Esc</kbd> para reiniciar
    </div>

    <script>
        // ============ STEP DATA (5 pasos del flyer) ============
        const steps = [{
                target: '#cartPanel',
                title: 'Ingresa tus Datos',
                text: 'Captura tu <strong>número de cliente</strong> y <strong>RFC</strong> en el panel del carrito. Tu lista de precios se carga <strong>automáticamente</strong> y se habilita el botón para finalizar.',
                bubblePos: 'left',
                radius: 230
            },
            {
                target: '#searchBar',
                title: 'Explora el Catálogo',
                text: 'Ingresa cualquier <strong>palabra</strong>, código UPC, modelo, descripción, serie o <strong>página del catálogo</strong>. Combínalo con los filtros: $E Precio Especial, ✨ Nuevos, o Grupo de temporada.',
                bubblePos: 'bottom',
                radius: 180
            },
            {
                target: '#productCardFeatured',
                title: 'Selecciona Cantidad',
                text: 'Usa los botones <strong>− / +</strong> o escribe la cantidad de piezas o paquetes que necesites, y haz click en <strong>Agregar</strong>. Tus precios se calculan en tiempo real.',
                bubblePos: 'right',
                radius: 175
            },
            {
                target: '#totalBox',
                title: 'Revisa tu Carrito',
                text: '<strong>Total con IVA</strong>, cantidad de líneas y disponibilidad de inventario. Verifica que cumples el <strong>pedido mínimo de $7,000</strong> y selecciona tu forma de pago.',
                bubblePos: 'left',
                radius: 150
            },
            {
                target: '#finalizeBtn',
                title: 'Envíalo por correo',
                text: 'El botón <strong>Finalizar pre-pedido</strong> genera el archivo Excel validado y lo envía por correo directamente a tu <strong>agente de ventas</strong> de PadColor.',
                bubblePos: 'left',
                radius: 130
            }
        ];

        // ============ ELEMENTS ============
        const overlay = document.getElementById('overlay');
        const mask = document.getElementById('mask');
        const ring = document.getElementById('ring');
        const cursor = document.getElementById('cursor');
        const bubble = document.getElementById('bubble');
        const numberBadge = document.getElementById('numberBadge');
        const bubbleTitle = document.getElementById('bubbleTitle');
        const bubbleText = document.getElementById('bubbleText');
        const stepDots = document.getElementById('stepDots');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const progressBar = document.getElementById('progressBar');
        const startScreen = document.getElementById('startScreen');
        const endScreen = document.getElementById('endScreen');

        let currentStep = 0;
        let tutorialActive = false;

        // ============ BUILD DOTS ============
        function buildDots() {
            stepDots.innerHTML = '';
            steps.forEach((_, i) => {
                const dot = document.createElement('div');
                dot.className = 'step-dot';
                if (i < currentStep) dot.classList.add('done');
                if (i === currentStep) dot.classList.add('active');
                stepDots.appendChild(dot);
            });
        }

        // ============ POSITION SPOTLIGHT ============
        function positionSpotlight() {
            const step = steps[currentStep];

            requestAnimationFrame(() => {
                const target = document.querySelector(step.target);
                if (!target) return;

                const rect = target.getBoundingClientRect();
                const cx = rect.left + rect.width / 2;
                const cy = rect.top + rect.height / 2;
                const r = step.radius;

                // Mask spotlight
                mask.style.setProperty('--x', cx + 'px');
                mask.style.setProperty('--y', cy + 'px');
                mask.style.setProperty('--r', r + 'px');

                // Ring
                const ringSize = r * 2;
                ring.style.left = (cx - r) + 'px';
                ring.style.top = (cy - r) + 'px';
                ring.style.width = ringSize + 'px';
                ring.style.height = ringSize + 'px';

                // Cursor (positioned at bottom-right of ring)
                const cursorAngle = Math.PI / 4; // 45° (bottom-right)
                const cursorX = cx + Math.cos(cursorAngle) * r * 0.78;
                const cursorY = cy + Math.sin(cursorAngle) * r * 0.78;
                cursor.style.left = cursorX + 'px';
                cursor.style.top = cursorY + 'px';

                // Bubble positioning
                positionBubble(cx, cy, r, step.bubblePos);

                // Update content
                numberBadge.textContent = currentStep + 1;
                bubbleTitle.textContent = step.title;
                bubbleText.innerHTML = step.text;

                // Buttons
                prevBtn.disabled = currentStep === 0;
                nextBtn.textContent = currentStep === steps.length - 1 ? '✓ Finalizar' : 'Siguiente →';

                // Progress
                progressBar.style.width = ((currentStep + 1) / steps.length * 100) + '%';

                buildDots();
            });
        }

        function positionBubble(cx, cy, r, preferredPos) {
            const bubbleRect = bubble.getBoundingClientRect();
            const bubbleW = Math.min(380, window.innerWidth - 40);
            const bubbleH = bubbleRect.height || 250;
            const gap = 36;
            const padding = 20;

            let left, top;
            let pos = preferredPos;

            // Calcular espacio disponible
            const spaceRight = window.innerWidth - (cx + r);
            const spaceLeft = cx - r;
            const spaceBottom = window.innerHeight - (cy + r);
            const spaceTop = cy - r;

            // Fallback automático
            if (pos === 'right' && spaceRight < bubbleW + gap) {
                pos = spaceLeft >= bubbleW + gap ? 'left' : (spaceBottom >= bubbleH + gap ? 'bottom' : 'top');
            } else if (pos === 'left' && spaceLeft < bubbleW + gap) {
                pos = spaceRight >= bubbleW + gap ? 'right' : (spaceBottom >= bubbleH + gap ? 'bottom' : 'top');
            } else if (pos === 'bottom' && spaceBottom < bubbleH + gap) {
                pos = spaceTop >= bubbleH + gap ? 'top' : (spaceRight >= bubbleW + gap ? 'right' : 'left');
            } else if (pos === 'top' && spaceTop < bubbleH + gap) {
                pos = spaceBottom >= bubbleH + gap ? 'bottom' : (spaceRight >= bubbleW + gap ? 'right' : 'left');
            }

            switch (pos) {
                case 'right':
                    left = cx + r + gap;
                    top = cy - bubbleH / 2;
                    break;
                case 'left':
                    left = cx - r - gap - bubbleW;
                    top = cy - bubbleH / 2;
                    break;
                case 'bottom':
                    left = cx - bubbleW / 2;
                    top = cy + r + gap;
                    break;
                case 'top':
                    left = cx - bubbleW / 2;
                    top = cy - r - gap - bubbleH;
                    break;
            }

            // Clamp dentro del viewport
            left = Math.max(padding, Math.min(left, window.innerWidth - bubbleW - padding));
            top = Math.max(padding, Math.min(top, window.innerHeight - bubbleH - padding));

            bubble.style.left = left + 'px';
            bubble.style.top = top + 'px';
        }

        // ============ NAVIGATION ============
        function goNext() {
            if (currentStep < steps.length - 1) {
                currentStep++;
                positionSpotlight();
            } else {
                finishTutorial();
            }
        }

        function goPrev() {
            if (currentStep > 0) {
                currentStep--;
                positionSpotlight();
            }
        }

        function startTutorial() {
            startScreen.classList.add('hidden');
            endScreen.classList.remove('visible');
            tutorialActive = true;
            currentStep = 0;
            setTimeout(positionSpotlight, 250);
        }

        function finishTutorial() {
            tutorialActive = false;
            endScreen.classList.add('visible');
        }

        function restart() {
            endScreen.classList.remove('visible');
            startScreen.classList.remove('hidden');
            progressBar.style.width = '0';
            currentStep = 0;
        }

        // ============ EVENT LISTENERS ============
        document.getElementById('startBtn').addEventListener('click', startTutorial);
        document.getElementById('restartBtn').addEventListener('click', () => {
            restart();
            setTimeout(startTutorial, 400);
        });
        nextBtn.addEventListener('click', goNext);
        prevBtn.addEventListener('click', goPrev);

        document.addEventListener('keydown', (e) => {
            if (!tutorialActive) {
                if (e.key === 'Enter' && !startScreen.classList.contains('hidden')) startTutorial();
                return;
            }
            if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                goNext();
            }
            if (e.key === 'ArrowLeft') goPrev();
            if (e.key === 'Escape') restart();
        });

        window.addEventListener('resize', () => {
            if (tutorialActive) positionSpotlight();
        });
    </script>

</body>

</html>
