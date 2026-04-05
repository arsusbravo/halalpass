<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import logoLg from '@images/logo-lg.png';
import { useTrans } from '@/composables/useTrans';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

const { t } = useTrans();

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const features = [
    { icon: '🛡️', titleKey: 'Expiry Radar', descKey: 'Auto-track every certificate expiry date. Get alerts before they expire — never get caught off guard by an auditor.' },
    { icon: '📊', titleKey: 'Halal Health Score', descKey: 'Weakest-link scoring per product. One glance tells you which products pass and which need attention.' },
    { icon: '📋', titleKey: 'SJPH Wizard', descKey: 'Fill the 11-criteria SJPH document step by step with a progress bar. No more paper forms.' },
    { icon: '🔗', titleKey: 'Supplier Portal', descKey: 'Send suppliers a link — they upload certificates directly. No more chasing emails.' },
    { icon: '📦', titleKey: 'SIHALAL Export', descKey: 'One click generates the exact ZIP (Daftar Bahan + Matriks Bahan) that SIHALAL needs.' },
    { icon: '✅', titleKey: 'Certification Readiness', descKey: 'A checklist that tells you exactly what is blocking you from submitting — and how to fix it.' },
];

const steps = [
    { num: '01', titleKey: 'Register & Set Up', descKey: 'Create your company, add facilities, suppliers, and ingredients.' },
    { num: '02', titleKey: 'Upload Certificates', descKey: 'Collect halal certificates for medium/high-risk ingredients. Use the supplier portal.' },
    { num: '03', titleKey: 'Build Products', descKey: 'Link ingredients to products. See your Halal Health Score instantly.' },
    { num: '04', titleKey: 'Complete SJPH', descKey: 'Fill the 11-criteria SJPH document using the guided wizard.' },
    { num: '05', titleKey: 'Check Readiness', descKey: 'The readiness page shows exactly what needs fixing before submission.' },
    { num: '06', titleKey: 'Export & Submit', descKey: 'Download the ZIP and upload to sihalal.halal.go.id. Done.' },
];
</script>

<template>
    <Head title="HalalPass — Fast-Track Your Halal Certification">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    </Head>

    <div class="hp-page">
        <!-- NAV -->
        <nav class="hp-nav">
            <div class="hp-container hp-nav-inner">
                <div class="hp-nav-logo">
                    <img :src="logoLg" alt="HalalPass" />
                    <span>Halal<strong>Pass</strong></span>
                </div>
                <div class="hp-nav-right">
                    <LanguageSwitcher />
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard()" class="hp-btn hp-btn-solid">{{ t('Dashboard') }}</Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="hp-btn hp-btn-text">{{ t('Log in') }}</Link>
                        <Link v-if="canRegister" :href="register()" class="hp-btn hp-btn-solid">{{ t('Register') }}</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- HERO -->
        <section class="hp-hero">
            <div class="hp-container hp-hero-inner">
                <div class="hp-hero-content">
                    <div class="hp-badge">
                        <span class="hp-badge-dot"></span>
                        {{ t('Deadline: 17 October 2026') }}
                    </div>
                    <h1>{{ t('Fast-Track Your Halal Certification') }}</h1>
                    <p>{{ t('Prepare all your documents, track every certificate, and know exactly when you are ready to submit to SIHALAL.') }}</p>
                    <div class="hp-hero-buttons">
                        <Link v-if="!$page.props.auth.user" :href="register()" class="hp-btn hp-btn-solid hp-btn-lg">
                            {{ t('Start Free') }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </Link>
                        <a href="#how-it-works" class="hp-btn hp-btn-outline hp-btn-lg">{{ t('How It Works') }}</a>
                    </div>
                    <p class="hp-hero-legal">{{ t('Based on UU No. 33/2014 & PP No. 42/2024') }}</p>
                </div>
                <div class="hp-hero-visual">
                    <div class="hp-mockup">
                        <!-- Mini app window -->
                        <div class="hp-mock-bar">
                            <span class="hp-mock-dot" style="background:#ff5f57"></span>
                            <span class="hp-mock-dot" style="background:#febc2e"></span>
                            <span class="hp-mock-dot" style="background:#28c840"></span>
                            <span class="hp-mock-url">halalpass.id/certification</span>
                        </div>
                        <div class="hp-mock-body">
                            <!-- Readiness banner -->
                            <div class="hp-mock-ready">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                <div>
                                    <span class="hp-mock-ready-title">{{ t('Ready to Submit!') }}</span>
                                    <span class="hp-mock-ready-sub">8/8 {{ t('checks passed') }}</span>
                                </div>
                                <span class="hp-mock-score">100%</span>
                            </div>
                            <!-- Checklist -->
                            <div class="hp-mock-checks">
                                <div class="hp-mock-check"><span class="hp-ck pass"></span><span>{{ t('Facilities') }}</span><span class="hp-ck-tag">3</span></div>
                                <div class="hp-mock-check"><span class="hp-ck pass"></span><span>{{ t('Suppliers') }}</span><span class="hp-ck-tag">7</span></div>
                                <div class="hp-mock-check"><span class="hp-ck pass"></span><span>{{ t('Ingredients') }}</span><span class="hp-ck-tag">20</span></div>
                                <div class="hp-mock-check"><span class="hp-ck pass"></span><span>{{ t('Certificates') }}</span><span class="hp-ck-tag green">{{ t('All valid') }}</span></div>
                                <div class="hp-mock-check"><span class="hp-ck pass"></span><span>{{ t('Products') }}</span><span class="hp-ck-tag green">100/100</span></div>
                                <div class="hp-mock-check"><span class="hp-ck pass"></span><span>SJPH</span><span class="hp-ck-tag green">{{ t('Approved') }}</span></div>
                            </div>
                            <!-- Export button -->
                            <div class="hp-mock-export">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" /></svg>
                                {{ t('Download Export for SIHALAL') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="hp-section" id="features">
            <div class="hp-container">
                <p class="hp-section-tag">{{ t('Features') }}</p>
                <h2 class="hp-section-title">{{ t('Everything You Need to Get Certified') }}</h2>
                <div class="hp-grid-3">
                    <div v-for="f in features" :key="f.titleKey" class="hp-card">
                        <span class="hp-card-icon">{{ f.icon }}</span>
                        <h3>{{ t(f.titleKey) }}</h3>
                        <p>{{ t(f.descKey) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section class="hp-section hp-section-alt" id="how-it-works">
            <div class="hp-container">
                <p class="hp-section-tag">{{ t('How It Works') }}</p>
                <h2 class="hp-section-title">{{ t('From Zero to SIHALAL-Ready in 6 Steps') }}</h2>
                <div class="hp-grid-3">
                    <div v-for="s in steps" :key="s.num" class="hp-step">
                        <span class="hp-step-num">{{ s.num }}</span>
                        <h3>{{ t(s.titleKey) }}</h3>
                        <p>{{ t(s.descKey) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="hp-cta">
            <div class="hp-container hp-cta-inner">
                <img :src="logoLg" alt="" class="hp-cta-logo" />
                <h2>{{ t('Ready to Get Halal Certified?') }}</h2>
                <p>{{ t('Stop tracking certificates in spreadsheets. Start now and see exactly what you need to submit.') }}</p>
                <Link v-if="!$page.props.auth.user" :href="register()" class="hp-btn hp-btn-white hp-btn-lg">
                    {{ t('Create Free Account') }}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </Link>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="hp-footer">
            <div class="hp-container hp-footer-inner">
                <div class="hp-footer-left">
                    <img :src="logoLg" alt="HalalPass" />
                    <span>HalalPass 2026 — ARSUS IT Solutions</span>
                </div>
                <span>{{ t('Based on UU No. 33/2014 & PP No. 42/2024') }}</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.hp-page {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: #1f2937;
    background: #fff;
}

/* ===== LAYOUT ===== */
.hp-container { max-width: 1140px; margin: 0 auto; padding: 0 28px; }

/* ===== NAV ===== */
.hp-nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
    border-bottom: 1px solid #e5e7eb;
}
.hp-nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
.hp-nav-logo { display: flex; align-items: center; gap: 10px; }
.hp-nav-logo img { height: 36px; width: 36px; }
.hp-nav-logo span { font-size: 18px; color: #111827; letter-spacing: -0.3px; }
.hp-nav-logo strong { font-weight: 800; }
.hp-nav-right { display: flex; align-items: center; gap: 10px; }

/* ===== BUTTONS ===== */
.hp-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;
    text-decoration: none; transition: all 0.15s; border: none; cursor: pointer;
    font-family: inherit;
}
.hp-btn-solid { background: #15803d; color: #fff; }
.hp-btn-solid:hover { background: #166534; }
.hp-btn-text { background: none; color: #15803d; }
.hp-btn-text:hover { background: #f0fdf4; }
.hp-btn-outline { background: #fff; color: #15803d; border: 1.5px solid #bbf7d0; }
.hp-btn-outline:hover { border-color: #15803d; background: #f0fdf4; }
.hp-btn-white { background: #fff; color: #15803d; }
.hp-btn-white:hover { background: #f0fdf4; }
.hp-btn-lg { padding: 13px 28px; font-size: 15px; border-radius: 10px; }

/* ===== HERO ===== */
.hp-hero { padding: 130px 0 80px; background: linear-gradient(180deg, #f0fdf4 0%, #fff 100%); }
.hp-hero-inner { display: flex; align-items: center; gap: 60px; }
.hp-hero-content { flex: 1; }
.hp-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: #fff; border: 1px solid #d1fae5; border-radius: 99px;
    padding: 5px 14px; font-size: 13px; font-weight: 600; color: #15803d;
    margin-bottom: 20px;
}
.hp-badge-dot { width: 7px; height: 7px; border-radius: 50%; background: #dc2626; animation: blink 2s infinite; }
@keyframes blink { 0%,100%{ opacity:1 } 50%{ opacity:.35 } }
.hp-hero-content h1 {
    font-size: 48px; font-weight: 800; line-height: 1.12; letter-spacing: -1.5px;
    color: #052e16; margin-bottom: 18px;
}
.hp-hero-content > p { font-size: 17px; line-height: 1.7; color: #4b5563; margin-bottom: 28px; max-width: 500px; }
.hp-hero-buttons { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; }
.hp-hero-legal { font-size: 12px; color: #9ca3af; }
.hp-hero-visual { flex-shrink: 0; }

/* ===== MOCKUP ===== */
.hp-mockup {
    width: 380px; border-radius: 14px; overflow: hidden;
    background: #fff; border: 1px solid #e5e7eb;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);
    transform: perspective(800px) rotateY(-2deg) rotateX(1deg);
}
.hp-mock-bar {
    display: flex; align-items: center; gap: 6px;
    padding: 10px 14px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
}
.hp-mock-dot { width: 10px; height: 10px; border-radius: 50%; }
.hp-mock-url {
    margin-left: 8px; font-size: 11px; color: #9ca3af;
    background: #fff; border: 1px solid #e5e7eb; border-radius: 4px;
    padding: 2px 10px; flex: 1; text-align: center;
}
.hp-mock-body { padding: 16px; }
.hp-mock-ready {
    display: flex; align-items: center; gap: 10px;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    padding: 12px 14px; margin-bottom: 14px;
}
.hp-mock-ready-title { display: block; font-size: 13px; font-weight: 700; color: #15803d; }
.hp-mock-ready-sub { display: block; font-size: 11px; color: #4ade80; }
.hp-mock-score {
    margin-left: auto; font-size: 22px; font-weight: 900; color: #15803d;
}
.hp-mock-checks { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.hp-mock-check {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; border-radius: 8px;
    background: #f9fafb; font-size: 13px; color: #374151;
}
.hp-ck {
    width: 16px; height: 16px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.hp-ck.pass {
    background: #dcfce7;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2315803d' stroke-width='3.5'%3E%3Cpath d='M5 13l4 4L19 7'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: center;
}
.hp-ck-tag {
    margin-left: auto; font-size: 11px; font-weight: 600;
    background: #f3f4f6; color: #6b7280; padding: 2px 8px; border-radius: 4px;
}
.hp-ck-tag.green { background: #dcfce7; color: #15803d; }
.hp-mock-export {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    background: #15803d; color: #fff; border-radius: 8px;
    padding: 10px; font-size: 12px; font-weight: 600;
}

/* ===== SECTIONS ===== */
.hp-section { padding: 90px 0; }
.hp-section-alt { background: #f9fafb; }
.hp-section-tag {
    font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
    color: #16a34a; margin-bottom: 8px; text-align: center;
}
.hp-section-title {
    font-size: 34px; font-weight: 800; letter-spacing: -1px; color: #111827;
    text-align: center; margin-bottom: 50px; line-height: 1.2;
}

/* ===== GRID ===== */
.hp-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

/* ===== FEATURE CARDS ===== */
.hp-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 28px; transition: box-shadow 0.2s, border-color 0.2s;
}
.hp-card:hover { border-color: #bbf7d0; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.hp-card-icon { font-size: 28px; display: block; margin-bottom: 14px; }
.hp-card h3 { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 6px; }
.hp-card p { font-size: 14px; line-height: 1.6; color: #6b7280; margin: 0; }

/* ===== STEP CARDS ===== */
.hp-step {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 28px;
}
.hp-step-num {
    font-size: 36px; font-weight: 900; color: #d1fae5; letter-spacing: -1px;
    line-height: 1; margin-bottom: 14px;
}
.hp-step h3 { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 6px; }
.hp-step p { font-size: 14px; line-height: 1.6; color: #6b7280; margin: 0; }

/* ===== CTA ===== */
.hp-cta { padding: 0 28px 90px; }
.hp-cta-inner {
    text-align: center; background: #15803d; border-radius: 20px;
    padding: 70px 40px; max-width: 1140px;
}
.hp-cta-logo { width: 64px; height: 64px; margin-bottom: 20px; }
.hp-cta-inner h2 { font-size: 30px; font-weight: 800; color: #fff; margin-bottom: 10px; letter-spacing: -0.5px; }
.hp-cta-inner p { font-size: 15px; color: rgba(255,255,255,0.8); margin: 0 auto 28px; max-width: 460px; }

/* ===== FOOTER ===== */
.hp-footer { border-top: 1px solid #e5e7eb; padding: 28px 0; }
.hp-footer-inner { display: flex; align-items: center; justify-content: space-between; }
.hp-footer-left { display: flex; align-items: center; gap: 10px; }
.hp-footer-left img { width: 28px; height: 28px; }
.hp-footer-left span, .hp-footer-inner > span { font-size: 13px; color: #9ca3af; }

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .hp-hero-inner { flex-direction: column; text-align: center; gap: 36px; }
    .hp-hero-content > p { max-width: 100%; }
    .hp-hero-buttons { justify-content: center; }
    .hp-hero-visual img { width: 200px; height: 200px; }
    .hp-mockup { width: 100%; transform: none; }
    .hp-grid-3 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .hp-hero-content h1 { font-size: 32px; }
    .hp-section-title { font-size: 26px; }
    .hp-grid-3 { grid-template-columns: 1fr; }
    .hp-cta-inner { padding: 48px 24px; }
    .hp-cta-inner h2 { font-size: 24px; }
    .hp-footer-inner { flex-direction: column; gap: 8px; text-align: center; }
}
</style>