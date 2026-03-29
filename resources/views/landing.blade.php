<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bolso Planejado — Caixa, dívidas e projeção num só painel | R$ 49,00 único</title>
    <meta name="description" content="Cadastre contas, lance entradas e saídas, acompanhe saldo e projeção, organize dívidas e gastos por categorias. Recomendações quando quiser ajustar o mês. R$ 49,00 uma vez, sem mensalidade.">
    @vite(['resources/css/app.css', 'resources/js/landing.js'])
    <style>
        html {
            scroll-behavior: smooth;
        }
        .dz-aurora {
            position: absolute;
            inset: auto;
            filter: blur(50px);
            opacity: 0.28;
            pointer-events: none;
        }
        .dz-aurora--a {
            width: 260px;
            height: 260px;
            top: -60px;
            right: -30px;
            background: radial-gradient(circle, #10b981 0%, transparent 70%);
            animation: dz-float 8s ease-in-out infinite;
        }
        .dz-aurora--b {
            width: 320px;
            height: 320px;
            bottom: -120px;
            left: -80px;
            background: radial-gradient(circle, #3b82f6 0%, transparent 70%);
            animation: dz-float 10s ease-in-out infinite reverse;
        }
        .dz-aurora--c {
            width: 240px;
            height: 240px;
            top: 36%;
            right: 36%;
            background: radial-gradient(circle, #8b5cf6 0%, transparent 68%);
            opacity: 0.2;
            animation: dz-float 12s ease-in-out infinite;
        }
        .dz-glass {
            background: linear-gradient(145deg, rgb(15 23 42 / 0.85), rgb(2 6 23 / 0.8));
            border: 1px solid rgb(51 65 85 / 0.7);
            box-shadow: 0 18px 40px rgb(0 0 0 / 0.35), inset 0 1px 0 rgb(148 163 184 / 0.08);
        }
        .dz-section-title {
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .dz-muted {
            color: #cbd5e1;
        }
        .dz-card {
            border-radius: 0.95rem;
            border: 1px solid rgb(51 65 85 / 0.75);
            background: rgb(15 23 42 / 0.62);
            box-shadow: 0 8px 22px rgb(2 6 23 / 0.2);
        }
        .dz-card-soft {
            border-radius: 0.9rem;
            border: 1px solid rgb(51 65 85 / 0.6);
            background: rgb(2 6 23 / 0.58);
        }
        .dz-cta-main {
            box-shadow: 0 10px 24px rgb(5 150 105 / 0.35);
        }
        .dz-cta-main:hover {
            box-shadow: 0 14px 28px rgb(5 150 105 / 0.45);
        }
        /* Ritmo vertical entre seções da landing */
        .dz-sec {
            padding-top: clamp(2.75rem, 6vw, 5rem);
            padding-bottom: clamp(2.75rem, 6vw, 5rem);
        }
        .dz-sec--hero {
            padding-top: clamp(2.5rem, 5vw, 3.75rem);
            padding-bottom: clamp(3.25rem, 8vw, 6.5rem);
        }
        .dz-sec--band {
            padding-top: clamp(3rem, 7vw, 5.5rem);
            padding-bottom: clamp(3rem, 7vw, 5.5rem);
        }
        .dz-sec-head {
            margin-bottom: clamp(1.5rem, 3vw, 2.5rem);
        }
        .dz-lift {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .dz-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 38px rgb(0 0 0 / 0.35);
        }
        .dz-chart-bars {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 0.4rem;
            align-items: end;
            height: 120px;
        }
        .dz-bar {
            border-radius: 0.35rem 0.35rem 0.2rem 0.2rem;
            background: linear-gradient(180deg, #34d399, #059669);
            transform-origin: bottom;
            animation: dz-bar-up 0.9s ease both;
            cursor: pointer;
            border: 1px solid transparent;
            transition: transform 0.2s ease, border-color 0.2s ease, filter 0.2s ease;
        }
        .dz-bar:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }
        .dz-bar.is-active {
            border-color: rgb(110 231 183 / 0.8);
            box-shadow: 0 0 0 2px rgb(16 185 129 / 0.2);
        }
        .dz-line {
            width: 100%;
            height: 120px;
        }
        .dz-line path {
            stroke-dasharray: 400;
            stroke-dashoffset: 400;
            animation: dz-draw 1.5s ease forwards;
        }
        .dz-hero-mock-chart {
            height: 140px;
        }
        .dz-hero-mock-chart .dz-grid-line {
            stroke: rgb(51 65 85 / 0.35);
            stroke-width: 1;
        }
        .dz-fade-up {
            animation: dz-fade-up 0.7s ease both;
        }
        .dz-reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .dz-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .dz-theme-a {
            background: linear-gradient(180deg, rgb(15 23 42 / 0.45), rgb(2 6 23 / 0));
        }
        .dz-theme-b {
            background: linear-gradient(180deg, rgb(6 78 59 / 0.16), rgb(2 6 23 / 0));
        }
        .dz-theme-c {
            background: linear-gradient(180deg, rgb(30 58 138 / 0.16), rgb(2 6 23 / 0));
        }
        .dz-grow {
            width: var(--w, 0%);
            animation: dz-grow 1.1s ease both;
        }
        .dz-spin-slow {
            animation: dz-spin 9s linear infinite;
        }
        .dz-spin-slow-rev {
            animation: dz-spin-rev 11s linear infinite;
        }
        .dz-story-step {
            position: sticky;
            top: 90px;
        }
        .dz-tilt {
            transform-style: preserve-3d;
            transition: transform 0.18s ease, box-shadow 0.25s ease;
            will-change: transform;
        }
        .dz-tilt-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
            border-radius: inherit;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgb(16 185 129 / 0.18), transparent 44%);
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .dz-tilt:hover .dz-tilt-glow {
            opacity: 1;
        }
        .dz-counter {
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }
        .dz-counter-pct {
            font-variant-numeric: tabular-nums;
        }
        @keyframes dz-float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-14px) translateX(8px); }
        }
        @keyframes dz-grow {
            from { width: 0; }
            to { width: var(--w, 0%); }
        }
        @keyframes dz-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes dz-spin-rev {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        @keyframes dz-bar-up {
            from { transform: scaleY(0.2); opacity: 0.5; }
            to { transform: scaleY(1); opacity: 1; }
        }
        @keyframes dz-draw {
            to { stroke-dashoffset: 0; }
        }
        @keyframes dz-fade-up {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .dz-aurora--a,
            .dz-aurora--b,
            .dz-aurora--c,
            .dz-bar,
            .dz-line path,
            .dz-fade-up,
            .dz-grow,
            .dz-spin-slow,
            .dz-spin-slow-rev,
            .dz-reveal {
                animation: none !important;
                transition: none !important;
            }
            .dz-tilt {
                transform: none !important;
            }
        }
        @media (max-width: 768px) and (hover: none) and (pointer: coarse) {
            html, body {
                scroll-snap-type: y proximity;
            }
            .dz-section {
                scroll-snap-align: start;
                scroll-snap-stop: normal;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <main>
        <section class="dz-section dz-sec--hero relative mx-auto max-w-6xl overflow-hidden px-4 sm:px-6 lg:px-8">
            <div class="dz-aurora dz-aurora--a dz-parallax" data-depth="0.18"></div>
            <div class="dz-aurora dz-aurora--b dz-parallax" data-depth="0.1"></div>
            <div class="dz-aurora dz-aurora--c dz-parallax" data-depth="0.24"></div>
            <header class="flex items-center justify-between">
                <p class="text-sm font-semibold tracking-wide text-emerald-400">Bolso Planejado</p>
                <a href="/app/login" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 transition hover:bg-slate-800">Entrar</a>
            </header>

            <div class="mt-14 grid gap-10 lg:grid-cols-2 lg:items-center">
                <div class="dz-fade-up">
                    <p class="inline-flex rounded-full border border-emerald-900/60 bg-emerald-950/30 px-3 py-1 text-xs font-medium text-emerald-300">
                        Acesso completo · R$ 49,00 · pague uma vez
                    </p>
                    <h1 class="dz-section-title mt-4 max-w-3xl text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                        Antecipe seu futuro financeiro com uma visão clara do seu caixa dia a dia.
                    </h1>
                    <p class="dz-muted mt-4 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                        Você lança o que entra e o que sai (e as dívidas, se tiver); a <strong class="font-medium text-slate-200">Previsão de caixa</strong> mostra como o saldo tende a ficar <strong class="font-medium text-slate-200">dia a dia</strong>, para enxergar o aperto antes de ele aparecer no extrato.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="/app/cadastro" class="dz-cta-main rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500">
                            Começar agora
                        </a>
                        <a href="#beneficios" class="rounded-lg border border-slate-700 px-5 py-3 text-sm text-slate-300 transition hover:bg-slate-800">
                            Ver benefícios
                        </a>
                        <a href="#demo-dashboard" class="rounded-lg border border-slate-700 px-5 py-3 text-sm text-slate-300 transition hover:bg-slate-800">
                            Ver mini dashboard
                        </a>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-500">
                        <span>Previsão de caixa</span>
                        <span class="text-slate-600">·</span>
                        <span>Dívidas no mesmo fluxo</span>
                        <span class="text-slate-600">·</span>
                        <span>Categorias suas</span>
                    </div>
                </div>

                <div class="dz-glass dz-fade-up dz-tilt relative overflow-hidden rounded-2xl border border-slate-800/80 p-0 shadow-2xl shadow-emerald-950/20" data-tilt-card>
                    <div class="dz-tilt-glow"></div>
                    <div class="flex items-center justify-between border-b border-slate-800/80 bg-slate-950/50 px-4 py-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Visão geral</p>
                            <p class="text-[11px] text-slate-500">Exemplo ilustrativo</p>
                        </div>
                        <span class="rounded-md border border-slate-700 bg-slate-900/80 px-2 py-1 text-[10px] text-slate-400">30 dias</span>
                    </div>
                    <div class="grid grid-cols-3 gap-px border-b border-slate-800/80 bg-slate-800/80">
                        <div class="bg-slate-950/90 px-3 py-3 text-center sm:px-4">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Saldo proj.</p>
                            <p class="mt-1 text-sm font-semibold tabular-nums text-sky-300">R$ 1.230</p>
                        </div>
                        <div class="bg-slate-950/90 px-3 py-3 text-center sm:px-4">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Entradas</p>
                            <p class="mt-1 text-sm font-semibold tabular-nums text-emerald-300">R$ 7.420</p>
                        </div>
                        <div class="bg-slate-950/90 px-3 py-3 text-center sm:px-4">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Saídas</p>
                            <p class="mt-1 text-sm font-semibold tabular-nums text-rose-300">R$ 6.190</p>
                        </div>
                    </div>
                    <div class="bg-slate-950/90 px-4 pb-4 pt-3">
                        <div class="flex items-end justify-between gap-2">
                            <p class="text-xs font-medium text-slate-400">Previsão de caixa</p>
                            <p class="text-[10px] text-slate-600">linha · demonstração</p>
                        </div>
                        <svg class="dz-hero-mock-chart mt-2 w-full" viewBox="0 0 320 140" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <defs>
                                <linearGradient id="dzHeroArea" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#34d399" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#34d399" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <line class="dz-grid-line" x1="0" y1="28" x2="320" y2="28" />
                            <line class="dz-grid-line" x1="0" y1="70" x2="320" y2="70" />
                            <line class="dz-grid-line" x1="0" y1="112" x2="320" y2="112" />
                            <path
                                d="M0 128 L0 95 C22 82 36 88 56 76 C72 66 88 68 108 58 C126 48 138 46 156 50 C178 54 194 36 212 34 C232 30 250 40 272 26 C288 16 302 20 320 12 L320 128 Z"
                                fill="url(#dzHeroArea)"
                            />
                            <path
                                d="M0 95C22 82 36 88 56 76C72 66 88 68 108 58C126 48 138 46 156 50C178 54 194 36 212 34C232 30 250 40 272 26C288 16 302 20 320 12"
                                stroke="#34d399"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <div class="mt-1 flex justify-between text-[10px] text-slate-600">
                            <span>Hoje</span>
                            <span>+15d</span>
                            <span>+30d</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dz-section dz-sec dz-theme-a mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-emerald-900/50 bg-emerald-950/20 p-5 sm:p-7">
                <h2 class="text-xl font-bold text-white sm:text-2xl">Três passos no app</h2>
                <p class="mt-2 text-sm text-slate-400">Cada etapa é uma ação sua; o app só organiza o resultado.</p>
                <div class="mt-6 grid gap-4 sm:grid-cols-3 sm:gap-5">
                    <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">1. Anotar</p>
                        <p class="mt-1 text-sm text-slate-200"><strong class="text-slate-100">Você:</strong> toca em <strong class="font-medium text-slate-200">Nova transação</strong> (na tela de transações), escolhe conta, valor e se foi entrada ou saída.<br><strong class="text-slate-100">O app:</strong> atualiza o saldo na hora.</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">2. Conferir</p>
                        <p class="mt-1 text-sm text-slate-200"><strong class="text-slate-100">Você:</strong> abre <strong class="font-medium text-slate-200">Início</strong> ou <strong class="font-medium text-slate-200">Previsão de caixa</strong> no menu.<br><strong class="text-slate-100">O app:</strong> mostra totais por categoria e como o saldo caminha nos próximos dias.</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">3. Ajustar</p>
                        <p class="mt-1 text-sm text-slate-200"><strong class="text-slate-100">Você:</strong> decide se quer cortar em algum gasto.<br><strong class="text-slate-100">O app:</strong> oferece sugestões por categoria; você escolhe o que faz sentido.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="dz-section dz-sec dz-theme-b mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <article class="dz-lift dz-card p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Entradas do mês</p>
                    <p class="dz-counter mt-2 text-2xl font-bold text-emerald-300" data-counter-value="7420" data-counter-prefix="R$ " data-counter-suffix=",00">R$ 7.420,00</p>
                    <p class="mt-2 text-xs text-slate-500">Exemplo: ritmo acima do mês passado</p>
                </article>
                <article class="dz-lift dz-card p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Saídas do mês</p>
                    <p class="dz-counter mt-2 text-2xl font-bold text-rose-300" data-counter-value="6190" data-counter-prefix="R$ " data-counter-suffix=",00">R$ 6.190,00</p>
                    <p class="mt-2 text-xs text-slate-500">Exemplo: saídas sob controle</p>
                </article>
                <article class="dz-lift dz-card p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Saldo projetado D+30</p>
                    <p class="dz-counter mt-2 text-2xl font-bold text-sky-300" data-counter-value="1230" data-counter-prefix="R$ " data-counter-suffix=",00">R$ 1.230,00</p>
                    <p class="mt-2 text-xs text-slate-500">Exemplo: caixa no azul no horizonte</p>
                </article>
                <article class="dz-lift dz-card p-5">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Dívida ativa</p>
                    <p class="dz-counter mt-2 text-2xl font-bold text-amber-300" data-counter-value="18540" data-counter-prefix="R$ " data-counter-suffix=",00">R$ 18.540,00</p>
                    <p class="mt-2 text-xs text-slate-500">Exemplo: acordos e parcelas em um só lugar</p>
                </article>
            </div>
        </section>

        <section class="dz-section dz-sec dz-theme-c mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                <article class="dz-card p-6">
                    <h3 class="dz-section-title text-lg font-semibold text-white">Gastos por categoria</h3>
                    <p class="mt-1 text-sm text-slate-400">Um olhar e você sabe onde está sangrando — sem abrir dez apps diferentes.</p>
                    <p class="mt-2 text-xs leading-relaxed text-emerald-200/90">Categorias personalizadas: nomeie do seu jeito, crie as que faltam e deixe o retrato dos gastos fiel à sua vida — não a um modelo genérico.</p>
                    <div class="dz-chart-bars mt-5" id="category-bars">
                        <button type="button" class="dz-bar" style="height: 36%" data-category="Lazer" data-value="380"></button>
                        <button type="button" class="dz-bar" style="height: 74%" data-category="Moradia" data-value="1980"></button>
                        <button type="button" class="dz-bar" style="height: 46%" data-category="Saúde" data-value="620"></button>
                        <button type="button" class="dz-bar" style="height: 89%" data-category="Mercado" data-value="1420"></button>
                        <button type="button" class="dz-bar" style="height: 58%" data-category="Educação" data-value="760"></button>
                        <button type="button" class="dz-bar" style="height: 67%" data-category="Transporte" data-value="620"></button>
                        <button type="button" class="dz-bar" style="height: 42%" data-category="Assinaturas" data-value="290"></button>
                        <button type="button" class="dz-bar" style="height: 61%" data-category="Contas da casa" data-value="910"></button>
                    </div>
                    <div class="mt-3 rounded-lg border border-emerald-900/40 bg-emerald-950/20 px-3 py-2">
                        <p class="text-xs text-slate-400">Categoria selecionada</p>
                        <p class="text-sm text-slate-200">
                            <span id="category-selected-name" class="font-semibold text-emerald-300">Mercado</span>
                            <span class="mx-1 text-slate-500">·</span>
                            <span id="category-selected-value" class="font-semibold text-white">R$ 1.420,00</span>
                        </p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-400">
                        <span class="rounded-md border border-slate-700 px-2 py-1">Mercado</span>
                        <span class="rounded-md border border-slate-700 px-2 py-1">Transporte</span>
                        <span class="rounded-md border border-slate-700 px-2 py-1">Casa</span>
                        <span class="rounded-md border border-slate-700 px-2 py-1">Lazer</span>
                    </div>
                </article>
                <article class="dz-card p-6">
                    <h3 class="dz-section-title text-lg font-semibold text-white">Progresso de recuperação</h3>
                    <p class="mt-1 text-sm text-slate-400">Do aperto ao controle — e você <em class="not-italic text-slate-300">vê</em> a diferença subir nos números.</p>
                    <div class="mt-4 space-y-3">
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Boletos pagos no prazo</span><span class="dz-counter-pct" data-counter-pct="82">82%</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-emerald-500" style="width: 82%"></div></div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Redução de atraso</span><span class="dz-counter-pct" data-counter-pct="64">64%</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-sky-500" style="width: 64%"></div></div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Reserva de segurança</span><span class="dz-counter-pct" data-counter-pct="47">47%</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-violet-500" style="width: 47%"></div></div>
                        </div>
                    </div>
                </article>
            </div>
            <div class="dz-card mt-4 p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="dz-section-title text-lg font-semibold text-white">Indicadores por categoria</h3>
                    <span class="text-xs text-slate-400">Demonstração ilustrativa</span>
                </div>
                <div class="grid gap-4">
                    <div class="space-y-3">
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Mercado</span><span>R$ 1.420</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-emerald-500 dz-grow" style="--w:78%"></div></div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Transporte</span><span>R$ 620</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-sky-500 dz-grow" style="--w:44%"></div></div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Moradia</span><span>R$ 1.980</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-violet-500 dz-grow" style="--w:90%"></div></div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-400"><span>Lazer</span><span>R$ 380</span></div>
                            <div class="h-2 rounded-full bg-slate-800"><div class="h-2 rounded-full bg-amber-500 dz-grow" style="--w:30%"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dz-section dz-sec--band border-y border-slate-800 bg-slate-900/30">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:gap-10 lg:px-8">
                <div class="space-y-10 lg:space-y-12">
                    <article class="rounded-xl border border-slate-800 bg-slate-950/60 p-6">
                        <p class="text-xs uppercase tracking-wide text-rose-300">Passo 1</p>
                        <h3 class="mt-2 text-xl font-bold text-white">Registrar o que aconteceu</h3>
                        <p class="mt-2 text-sm text-slate-300">Abriu <strong class="font-medium text-slate-200">Transações</strong> → <strong class="font-medium text-slate-200">Nova transação</strong> → preencheu valor e conta → salvou. O dia some da cabeça e fica no painel.</p>
                    </article>
                    <article class="rounded-xl border border-slate-800 bg-slate-950/60 p-6">
                        <p class="text-xs uppercase tracking-wide text-amber-300">Passo 2</p>
                        <h3 class="mt-2 text-xl font-bold text-white">Olhar a linha do tempo do saldo</h3>
                        <p class="mt-2 text-sm text-slate-300">No menu: <strong class="font-medium text-slate-200">Previsão de caixa</strong> → escolheu conta e datas → viu o saldo dia a dia. Sem adivinhar: é o efeito das transações que você já registrou.</p>
                    </article>
                    <article class="rounded-xl border border-slate-800 bg-slate-950/60 p-6">
                        <p class="text-xs uppercase tracking-wide text-emerald-300">Passo 3</p>
                        <h3 class="mt-2 text-xl font-bold text-white">Decidir com sugestão na mão</h3>
                        <p class="mt-2 text-sm text-slate-300">Abriu <strong class="font-medium text-slate-200">recomendações</strong> → leu o corte sugerido por categoria → aplicou só o que fizer sentido naquele mês.</p>
                    </article>
                </div>
                <aside class="dz-story-step rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Rotina em três cliques</p>
                    <h3 class="mt-2 text-2xl font-bold text-white">Lançar → conferir → ajustar se precisar</h3>
                    <p class="mt-3 text-sm text-slate-300">Não há “modo avançado” escondido: o fluxo é sempre registrar o movimento, ver o efeito no saldo e, quando quiser, usar a dica do app para cortar gasto.</p>
                    <div class="mt-4 space-y-2 text-sm text-slate-300">
                        <div class="rounded-md border border-slate-800 bg-slate-950/60 px-3 py-2">1. <strong class="text-slate-200">Lançar</strong> — entrada ou saída na conta certa</div>
                        <div class="rounded-md border border-slate-800 bg-slate-950/60 px-3 py-2">2. <strong class="text-slate-200">Conferir</strong> — em Início ou Previsão de caixa</div>
                        <div class="rounded-md border border-slate-800 bg-slate-950/60 px-3 py-2">3. <strong class="text-slate-200">Ajustar</strong> — só se você quiser, com sugestão por categoria</div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="dz-section dz-sec--band border-y border-slate-800 bg-slate-900/30">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="dz-sec-head">
                    <h2 class="dz-section-title text-3xl font-bold text-white">Antes e depois: a virada que você sente no bolso</h2>
                    <p class="mt-3 text-slate-300">Do mesmo salário — mas com clareza. Não é mágica: é visão.</p>
                </div>
                <div class="mt-2 grid gap-5 lg:grid-cols-2 lg:gap-6">
                    <article class="rounded-xl border border-rose-900/60 bg-rose-950/22 p-6">
                        <h3 class="text-lg font-semibold text-rose-200">Antes — o modo “susto”</h3>
                        <ul class="mt-4 space-y-2 text-sm text-rose-100/90">
                            <li>- Saldo da semana? Só um palpite nervoso.</li>
                            <li>- Boletos e PIX no susto — e o coração acelerado.</li>
                            <li>- Não sabe qual gasto está drenando o mês.</li>
                            <li>- Acordo de dívida sem visão do que a parcela faz no resto.</li>
                        </ul>
                    </article>
                    <article class="rounded-xl border border-emerald-900/60 bg-emerald-950/22 p-6">
                        <h3 class="text-lg font-semibold text-emerald-200">Depois — com Bolso Planejado</h3>
                        <ul class="mt-4 space-y-2 text-sm text-emerald-100/90">
                            <li>- Saldo de hoje e dos próximos dias em poucos toques.</li>
                            <li>- Dias críticos marcados antes de virar problema.</li>
                            <li>- Sugestões de corte onde dói menos — e rende mais.</li>
                            <li>- Negociação e parcelas com previsibilidade, não com aposta.</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <section id="beneficios" class="dz-section dz-sec--band border-y border-slate-800 bg-slate-900/30">
            <div class="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 lg:grid-cols-3 lg:gap-6 lg:px-8">
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-5">
                    <h3 class="font-semibold text-white">Dívidas sob controle</h3>
                    <p class="mt-2 text-sm text-slate-300">Saldo, acordos e parcelas em um só lugar — para negociar com a cabeça fria, não no impulso.</p>
                </article>
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-5">
                    <h3 class="font-semibold text-white">Previsão que acalma</h3>
                    <p class="mt-2 text-sm text-slate-300">Seu caixa dia a dia no radar: menos atraso, menos surpresa, menos noite em claro.</p>
                </article>
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-5">
                    <h3 class="font-semibold text-white">Hábito sem sofrimento</h3>
                    <p class="mt-2 text-sm text-slate-300">Onboarding leve e check-in diário em minutos — consistência que não depende de você ser “disciplinado o tempo todo”.</p>
                </article>
            </div>
        </section>

        <section class="dz-section dz-sec mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="dz-sec-head">
                <h2 class="dz-section-title text-3xl font-bold text-white">Da primeira vez ao uso de rotina</h2>
                <p class="dz-muted mt-3 max-w-3xl">
                    Ordem sugerida: <strong class="font-medium text-slate-300">Contas</strong> (cadastro) → <strong class="font-medium text-slate-300">Transações</strong> (o que entrou e saiu) → <strong class="font-medium text-slate-300">Previsão de caixa</strong> (linha do tempo) → <strong class="font-medium text-slate-300">Recomendações</strong> (se quiser cortar gasto). Sem configurar dezenas de telas.
                </p>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <article class="dz-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Passo 1</p>
                    <h3 class="mt-2 font-semibold text-white">Criar suas contas</h3>
                    <p class="mt-2 text-sm text-slate-300">Dê um nome (ex.: Nubank, carteira). Isso é só para agrupar o que é seu — pode editar depois.</p>
                </article>
                <article class="dz-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Passo 2</p>
                    <h3 class="mt-2 font-semibold text-white">Lançar o que moveu</h3>
                    <p class="mt-2 text-sm text-slate-300">Cada PIX, boleto ou dinheiro: valor, data, conta e categoria. Um lançamento de cada vez; o saldo soma sozinho.</p>
                </article>
                <article class="dz-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Passo 3</p>
                    <h3 class="mt-2 font-semibold text-white">Abrir Previsão de caixa</h3>
                    <p class="mt-2 text-sm text-slate-300">É a tela em que o saldo aparece dia a dia. Se algo apertar, você vê na linha do tempo antes de virar problema na conta.</p>
                </article>
                <article class="dz-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-300">Passo 4</p>
                    <h3 class="mt-2 font-semibold text-white">Ler as recomendações</h3>
                    <p class="mt-2 text-sm text-slate-300">Quando quiser, abra as sugestões por categoria e aplique só o que couber no seu mês.</p>
                </article>
            </div>
        </section>

        <section class="dz-section dz-sec--band border-y border-slate-800 bg-slate-900/30">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">
                <div class="dz-sec-head">
                    <h2 class="dz-section-title text-3xl font-bold text-white">Perguntas que importam</h2>
                    <p class="dz-muted mt-3">Sem termos técnicos. Direto ao que destrava.</p>
                </div>
                <div class="mt-2 space-y-3">
                    <details class="dz-card-soft p-4">
                        <summary class="cursor-pointer font-semibold text-white">Isso é difícil de usar?</summary>
                        <p class="mt-2 text-sm text-slate-300">Não. Se você consegue mandar mensagem no celular, consegue usar: conta, lançamento, aviso. O app faz o “pesado” por você.</p>
                    </details>
                    <details class="dz-card-soft p-4">
                        <summary class="cursor-pointer font-semibold text-white">Preciso entender de planilha?</summary>
                        <p class="mt-2 text-sm text-slate-300">Não precisa. Os dados viram tela clara: onde está o dinheiro, para onde foi e o que fazer em seguida.</p>
                    </details>
                    <details class="dz-card-soft p-4">
                        <summary class="cursor-pointer font-semibold text-white">É pagamento mensal?</summary>
                        <p class="mt-2 text-sm text-slate-300">Não. É um valor único de R$ 49,00 — acesso completo, sem mensalidade fantasma.</p>
                    </details>
                    <details class="dz-card-soft p-4">
                        <summary class="cursor-pointer font-semibold text-white">Consigo ver se vai faltar dinheiro?</summary>
                        <p class="mt-2 text-sm text-slate-300">Sim. Em <strong class="font-medium text-slate-200">Previsão de caixa</strong> o saldo aparece dia a dia; dá para ver o aperto chegando antes de estourar na conta.</p>
                    </details>
                </div>
            </div>
        </section>

        <section id="demo-dashboard" class="dz-section dz-sec--band border-y border-slate-800 bg-slate-900/30">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="dz-sec-head">
                    <h2 class="dz-section-title text-3xl font-bold text-white">Mini dashboard</h2>
                    <p class="dz-muted mt-3 max-w-3xl">
                        Abaixo, o mesmo tipo de tela que você encontra no app: <strong class="font-medium text-slate-300">Início</strong> (resumo), <strong class="font-medium text-slate-300">Transações</strong>, <strong class="font-medium text-slate-300">Dívidas</strong>, <strong class="font-medium text-slate-300">Previsão de caixa</strong> e <strong class="font-medium text-slate-300">Recomendações</strong>. Clique nas abas para ver cada uma.
                    </p>
                </div>

                <div class="mt-8 rounded-2xl border border-slate-800 bg-slate-950/70 p-4 sm:p-6">
                    <div class="mb-5 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap" id="demo-tabs">
                        <button class="demo-tab rounded-lg border border-emerald-700 bg-emerald-900/40 px-4 py-2 text-sm text-emerald-200" data-tab="tab-dashboard">Início</button>
                        <button class="demo-tab rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-300" data-tab="tab-transacoes">Transações</button>
                        <button class="demo-tab rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-300" data-tab="tab-dividas">Dívidas</button>
                        <button class="demo-tab rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-300" data-tab="tab-projecao">Previsão de caixa</button>
                        <button class="demo-tab rounded-lg border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-300" data-tab="tab-recomendacoes">Recomendações</button>
                    </div>

                    <div class="demo-panel" id="tab-dashboard">
                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-xs text-slate-400">Saldo consolidado</p>
                                <p class="mt-1 text-xl font-bold text-emerald-300">R$ 3.870,00</p>
                            </div>
                            <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-xs text-slate-400">Meta de gasto (mês)</p>
                                <p class="mt-1 text-xl font-bold text-white">76%</p>
                            </div>
                            <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-xs text-slate-400">Próximo vencimento</p>
                                <p class="mt-1 text-xl font-bold text-amber-300">Dia 12</p>
                            </div>
                        </div>
                        <div class="mt-4 rounded-lg border border-slate-800 bg-slate-900/70 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Resumo semanal</p>
                            <p class="mt-2 text-sm text-slate-200">Exemplo: semana fechada no azul e corte de gastos que você consegue repetir.</p>
                        </div>
                    </div>

                    <div class="demo-panel hidden" id="tab-transacoes">
                        <div class="overflow-x-auto rounded-lg border border-slate-800">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-slate-900 text-slate-400">
                                    <tr>
                                        <th class="px-4 py-3">Data</th>
                                        <th class="px-4 py-3">Descrição</th>
                                        <th class="px-4 py-3">Conta</th>
                                        <th class="px-4 py-3">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 text-slate-200">
                                    <tr><td class="px-4 py-3">08/03</td><td class="px-4 py-3">Salário</td><td class="px-4 py-3">Conta Principal</td><td class="px-4 py-3 text-emerald-300">+ R$ 4.800,00</td></tr>
                                    <tr><td class="px-4 py-3">09/03</td><td class="px-4 py-3">Mercado</td><td class="px-4 py-3">Conta Principal</td><td class="px-4 py-3 text-rose-300">- R$ 320,00</td></tr>
                                    <tr><td class="px-4 py-3">10/03</td><td class="px-4 py-3">Freela</td><td class="px-4 py-3">Carteira Digital</td><td class="px-4 py-3 text-emerald-300">+ R$ 650,00</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="demo-panel hidden" id="tab-dividas">
                        <div class="grid gap-3 md:grid-cols-2">
                            <article class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-sm font-semibold text-white">Cartão Banco XPTO</p>
                                <p class="mt-2 text-sm text-slate-300">Saldo: <span class="text-amber-300">R$ 6.400,00</span></p>
                                <p class="mt-1 text-sm text-slate-400">Parcela: R$ 540,00 até dia 15</p>
                            </article>
                            <article class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-sm font-semibold text-white">Acordo Loja Centro</p>
                                <p class="mt-2 text-sm text-slate-300">Saldo: <span class="text-amber-300">R$ 1.890,00</span></p>
                                <p class="mt-1 text-sm text-slate-400">Parcela: R$ 210,00 até dia 20</p>
                            </article>
                        </div>
                    </div>

                    <div class="demo-panel hidden" id="tab-projecao">
                        <div class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Próximos 7 dias</p>
                            <div class="mt-3 grid gap-2 text-sm">
                                <div class="flex items-center justify-between rounded-md bg-slate-900 px-3 py-2"><span class="text-slate-300">11/03</span><span class="text-emerald-300">R$ 3.640,00</span></div>
                                <div class="flex items-center justify-between rounded-md bg-slate-900 px-3 py-2"><span class="text-slate-300">12/03</span><span class="text-rose-300">R$ 2.970,00</span></div>
                                <div class="flex items-center justify-between rounded-md bg-slate-900 px-3 py-2"><span class="text-slate-300">13/03</span><span class="text-emerald-300">R$ 3.240,00</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="demo-panel hidden" id="tab-recomendacoes">
                        <div class="space-y-3">
                            <article class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-sm font-semibold text-white">Corte sugerido na categoria certa</p>
                                <p class="mt-1 text-sm text-slate-300">Reduzir 10% onde o app mostra peso — pode liberar <span class="text-emerald-300">R$ 180,00</span>/mês (exemplo ilustrativo).</p>
                            </article>
                            <article class="rounded-lg border border-slate-800 bg-slate-900/80 p-4">
                                <p class="text-sm font-semibold text-white">Plano semanal que você executa</p>
                                <p class="mt-1 text-sm text-slate-300">Exemplo: revisar gastos, atacar a dívida mais cara, fazer o aporte mínimo — sem teoria infinita.</p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dz-section dz-sec mx-auto max-w-4xl px-4 text-center sm:px-6">
            <h2 class="dz-section-title text-3xl font-bold text-white">R$ 49,00 uma vez · acesso completo</h2>
            <p class="dz-muted mt-4 max-w-2xl mx-auto">
                Um único pagamento para usar todas as funções do app, sem mensalidade. Ideal para quem quer organizar caixa e dívidas com calma e visão clara.
            </p>
            <a href="/app/cadastro" class="dz-cta-main mt-6 inline-block rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500">
                Criar minha conta
            </a>
        </section>
    </main>

    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-700 bg-slate-950/95 p-3 backdrop-blur sm:hidden">
        <div class="mx-auto flex max-w-6xl items-center gap-3">
            <p class="min-w-0 flex-1 text-xs text-slate-300">
                <span class="font-semibold text-emerald-300">R$ 49,00</span> único · caixa e dívidas em um só app
            </p>
            <a href="/app/cadastro" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">
                Começar
            </a>
        </div>
    </div>
    <div class="h-24 sm:hidden"></div>

    <script>
        (function () {
            const tabs = Array.from(document.querySelectorAll('.demo-tab'));
            const panels = Array.from(document.querySelectorAll('.demo-panel'));
            const sections = Array.from(document.querySelectorAll('.dz-section'));
            const parallaxItems = Array.from(document.querySelectorAll('.dz-parallax'));
            const counters = Array.from(document.querySelectorAll('.dz-counter'));
            const countersPct = Array.from(document.querySelectorAll('.dz-counter-pct'));
            const tiltCards = Array.from(document.querySelectorAll('[data-tilt-card]'));
            const categoryBars = Array.from(document.querySelectorAll('#category-bars .dz-bar'));
            const categoryName = document.getElementById('category-selected-name');
            const categoryValue = document.getElementById('category-selected-value');

            sections.forEach((section) => section.classList.add('dz-reveal'));

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.14 });

            sections.forEach((section) => observer.observe(section));

            const nf = new Intl.NumberFormat('pt-BR');
            function animateCounter(el) {
                if (el.dataset.animated === '1') return;
                el.dataset.animated = '1';
                const target = Number(el.dataset.counterValue || '0');
                const prefix = el.dataset.counterPrefix || '';
                const suffix = el.dataset.counterSuffix || '';
                const duration = 1100;
                const start = performance.now();

                function step(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const value = Math.round(target * eased);
                    el.textContent = `${prefix}${nf.format(value)}${suffix}`;
                    if (progress < 1) requestAnimationFrame(step);
                }

                requestAnimationFrame(step);
            }

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) animateCounter(entry.target);
                });
            }, { threshold: 0.35 });

            counters.forEach((counter) => counterObserver.observe(counter));

            function animatePct(el) {
                if (el.dataset.animated === '1') return;
                el.dataset.animated = '1';
                const target = Number(el.dataset.counterPct || '0');
                const duration = 900;
                const start = performance.now();

                function step(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const value = Math.round(target * eased);
                    el.textContent = `${value}%`;
                    if (progress < 1) requestAnimationFrame(step);
                }

                requestAnimationFrame(step);
            }

            const pctObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) animatePct(entry.target);
                });
            }, { threshold: 0.45 });

            countersPct.forEach((pct) => pctObserver.observe(pct));

            if (categoryBars.length && categoryName && categoryValue) {
                const moneyFmt = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
                function setCategory(bar) {
                    categoryBars.forEach((b) => b.classList.toggle('is-active', b === bar));
                    const name = bar.dataset.category || 'Categoria';
                    const value = Number(bar.dataset.value || '0');
                    categoryName.textContent = name;
                    categoryValue.textContent = moneyFmt.format(value);
                }
                categoryBars.forEach((bar) => {
                    bar.addEventListener('click', () => setCategory(bar));
                });
                setCategory(categoryBars[3] || categoryBars[0]);
            }

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!reducedMotion) {
                tiltCards.forEach((card) => {
                    card.addEventListener('mousemove', (event) => {
                        const rect = card.getBoundingClientRect();
                        const x = event.clientX - rect.left;
                        const y = event.clientY - rect.top;
                        const cx = rect.width / 2;
                        const cy = rect.height / 2;
                        const rx = ((y - cy) / cy) * -5;
                        const ry = ((x - cx) / cx) * 6;
                        card.style.transform = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-2px)`;
                        card.style.setProperty('--mx', `${(x / rect.width) * 100}%`);
                        card.style.setProperty('--my', `${(y / rect.height) * 100}%`);
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg) translateY(0)';
                    });
                });
            }

            function updateParallax() {
                const y = window.scrollY || 0;
                parallaxItems.forEach((el) => {
                    const depth = Number(el.getAttribute('data-depth') || 0.1);
                    const move = y * depth;
                    el.style.transform = `translate3d(0, ${move * -0.25}px, 0)`;
                });
            }

            updateParallax();
            window.addEventListener('scroll', updateParallax, { passive: true });

            if (!tabs.length || !panels.length) return;

            function activate(tabId) {
                tabs.forEach((tab) => {
                    const active = tab.dataset.tab === tabId;
                    tab.classList.toggle('border-emerald-700', active);
                    tab.classList.toggle('bg-emerald-900/40', active);
                    tab.classList.toggle('text-emerald-200', active);
                    tab.classList.toggle('border-slate-700', !active);
                    tab.classList.toggle('bg-slate-900', !active);
                    tab.classList.toggle('text-slate-300', !active);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.id !== tabId);
                });
            }

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => activate(tab.dataset.tab));
            });
        })();
    </script>
</body>
</html>
