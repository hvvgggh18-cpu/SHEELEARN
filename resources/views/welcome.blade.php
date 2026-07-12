<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SHEELEARN">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SHEELEARN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Firebase JS (for Phone Authentication) -->
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] },
                    colors: {
                        n: { DEFAULT:'#020617', 1:'#0a0f1e', 2:'#0f172a', 3:'#162033' },
                        c: { DEFAULT:'#e2e8f0', 60:'rgba(226,232,240,0.6)', 40:'rgba(226,232,240,0.4)', 25:'rgba(226,232,240,0.25)', 15:'rgba(226,232,240,0.15)', 10:'rgba(226,232,240,0.10)', 8:'rgba(226,232,240,0.08)', 5:'rgba(226,232,240,0.05)', 3:'rgba(226,232,240,0.03)' },
                        cy: { DEFAULT:'#22d3ee', dim:'rgba(34,211,238,0.15)', glow:'rgba(34,211,238,0.08)' },
                        ac: { DEFAULT:'#818cf8', dim:'rgba(129,140,248,0.15)' },
                        gn: { DEFAULT:'#34d399', dim:'rgba(52,211,153,0.15)' },
                    }
                }
            }
        }
    </script>
    <style>
        *{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
        body{font-family:'Inter',system-ui,sans-serif;background:#020617;color:#e2e8f0;overflow-x:hidden}
        ::-webkit-scrollbar{width:4px}
        ::-webkit-scrollbar-track{background:#020617}
        ::-webkit-scrollbar-thumb{background:rgba(34,211,238,0.12);border-radius:99px}
        ::-webkit-scrollbar-thumb:hover{background:rgba(34,211,238,0.25)}

        #scrollProgress{position:fixed;top:0;left:0;height:2px;background:linear-gradient(90deg,#22d3ee,#818cf8,#34d399);z-index:9999;transition:width .1s linear;width:0}
        #spotlight{position:fixed;inset:0;pointer-events:none;z-index:1;transition:background .15s ease}

        @property --ba{syntax:'<angle>';initial-value:0deg;inherits:false}
        @property --ba2{syntax:'<angle>';initial-value:180deg;inherits:false}

        .g1{background:rgba(15,23,42,0.6);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(34,211,238,0.06)}
        .g2{background:rgba(15,23,42,0.7);backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);border:1px solid rgba(34,211,238,0.08)}
        .g3{background:rgba(15,23,42,0.8);backdrop-filter:blur(32px);-webkit-backdrop-filter:blur(32px);border:1px solid rgba(34,211,238,0.1)}
        .gnav{background:rgba(2,6,23,0.6);backdrop-filter:blur(32px);-webkit-backdrop-filter:blur(32px);border:1px solid rgba(34,211,238,0.08)}

        .cyber-text{background:linear-gradient(135deg,#22d3ee 0%,#818cf8 50%,#34d399 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .cyber-text-flow{background:linear-gradient(270deg,#22d3ee,#818cf8,#34d399,#22d3ee);background-size:300% 100%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:flowGrad 5s ease infinite}
        @keyframes flowGrad{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}

        .glow-card{--ba:0deg;padding:1px;border-radius:20px;background:conic-gradient(from var(--ba),transparent 20%,rgba(34,211,238,0.25) 35%,rgba(129,140,248,0.2) 50%,rgba(52,211,153,0.2) 65%,transparent 80%);animation:spinBorder 6s linear infinite}
        .glow-card-inner{border-radius:19px;background:rgba(2,6,23,0.94);backdrop-filter:blur(24px);height:100%}
        @keyframes spinBorder{to{--ba:360deg}}

        .glow-card-alt{--ba2:180deg;padding:1px;border-radius:20px;background:conic-gradient(from var(--ba2),transparent 15%,rgba(52,211,153,0.2) 30%,rgba(34,211,238,0.25) 50%,rgba(129,140,248,0.15) 70%,transparent 85%);animation:spinBorder2 7s linear infinite}
        @keyframes spinBorder2{to{--ba2:540deg}}

        .hex-grid{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='52'%3E%3Cpath d='M30 0L60 15v22L30 52 0 37V15z' fill='none' stroke='rgba(34,211,238,0.03)' stroke-width='0.5'/%3E%3C/svg%3E");background-size:60px 52px}
        .circuit-grid{background-image:linear-gradient(rgba(34,211,238,0.015) 1px,transparent 1px),linear-gradient(90deg,rgba(34,211,238,0.015) 1px,transparent 1px);background-size:40px 40px}
        .dot-grid{background-image:radial-gradient(rgba(34,211,238,0.06) 1px,transparent 1px);background-size:24px 24px}

        .scanlines::before{content:'';position:absolute;inset:0;pointer-events:none;z-index:2;background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.03) 2px,rgba(0,0,0,0.03) 4px)}

        @keyframes aurora1{0%,100%{transform:translate(0,0) scale(1);opacity:.4}33%{transform:translate(60px,-50px) scale(1.1);opacity:.6}66%{transform:translate(-30px,60px) scale(.9);opacity:.3}}
        @keyframes aurora2{0%,100%{transform:translate(0,0) scale(1);opacity:.3}33%{transform:translate(-50px,30px) scale(.85);opacity:.5}66%{transform:translate(40px,-25px) scale(1.05);opacity:.25}}
        @keyframes aurora3{0%,100%{transform:translate(0,0) scale(1);opacity:.25}50%{transform:translate(30px,50px) scale(1.15);opacity:.4}}

        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
        .fl1{animation:float 7s ease-in-out infinite}.fl2{animation:float 7s ease-in-out infinite 1.5s}.fl3{animation:float 7s ease-in-out infinite 3s}.fl4{animation:float 7s ease-in-out infinite .8s}

        @keyframes pulseDot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.3;transform:scale(1.8)}}
        .pulse-dot{animation:pulseDot 2.5s ease-in-out infinite}

        @keyframes slowSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
        .slow-spin{animation:slowSpin 120s linear infinite}

        @keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
        .marquee-track{animation:marquee 35s linear infinite}
        .marquee-track:hover{animation-play-state:paused}

        .light-leak{position:relative;overflow:hidden}
        .light-leak::before{content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;background:linear-gradient(90deg,transparent,rgba(34,211,238,0.02),transparent);transition:left .7s ease;pointer-events:none;z-index:1}
        .light-leak:hover::before{left:130%}

        .tilt-card{transition:transform .15s ease-out,box-shadow .3s ease;transform-style:preserve-3d}

        .feat-card{transition:all .4s cubic-bezier(.16,1,.3,1)}
        .feat-card:hover{transform:translateY(-6px);background:rgba(34,211,238,0.04);border-color:rgba(34,211,238,0.12);box-shadow:0 24px 60px -20px rgba(0,0,0,.5),0 0 50px -20px rgba(34,211,238,.06)}

        /* Auth Modal Error Styles */
        .auth-error-msg{animation:slideInError .3s ease-out}
        @keyframes slideInError{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
        .auth-input.error-field{border-color:rgba(239,68,68,0.5);background:rgba(239,68,68,0.05)}

        .btn-cy{background:linear-gradient(135deg,#22d3ee,#0891b2);color:#020617;box-shadow:0 4px 24px -4px rgba(34,211,238,.3);transition:all .35s cubic-bezier(.16,1,.3,1);position:relative;overflow:hidden;font-weight:700}
        .btn-cy::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.2),transparent 60%);opacity:0;transition:opacity .3s}
        .btn-cy:hover::before{opacity:1}
        .btn-cy:hover{transform:translateY(-2px);box-shadow:0 8px 40px -4px rgba(34,211,238,.45),0 0 60px -10px rgba(34,211,238,.12)}

        .btn-g{background:rgba(34,211,238,0.04);backdrop-filter:blur(12px);border:1px solid rgba(34,211,238,0.1);color:rgba(226,232,240,.6);transition:all .35s cubic-bezier(.16,1,.3,1)}
        .btn-g:hover{background:rgba(34,211,238,0.08);border-color:rgba(34,211,238,0.2);transform:translateY(-2px);color:#e2e8f0}
        .otp-box{width:3rem;height:3rem;border-radius:16px;background:rgba(255,255,255,0.04);border:1px solid rgba(226,232,240,0.12);color:#e2e8f0;text-align:center;font-size:1.125rem;font-weight:700;transition:all .25s ease;box-shadow:0 10px 30px -20px rgba(34,211,238,0.3);}
        .otp-box:focus{outline:none;border-color:#22d3ee;background:rgba(34,211,238,0.12);box-shadow:0 0 0 4px rgba(34,211,238,0.12);transform:scale(1.03);}
        .otp-box:hover{border-color:rgba(34,211,238,0.35);}
        .otp-box.shake{animation:shake-otp .35s ease-in-out;}
        @keyframes shake-otp{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}
        .otp-success{animation:success-glow 0.8s ease forwards;}
        @keyframes success-glow{0%{box-shadow:0 0 0 rgba(34,211,238,0)}50%{box-shadow:0 0 20px rgba(34,211,238,0.35)}100%{box-shadow:0 0 0 rgba(34,211,238,0)}}
        /* Outlined primary button matching btn-cy sizing but transparent */
        .btn-outline-cy{background:transparent;border:2px solid rgba(34,211,238,0.9);color:rgba(34,211,238,0.95);transition:all .28s cubic-bezier(.16,1,.3,1);font-weight:700}
        .btn-outline-cy:hover{background:rgba(34,211,238,0.95);color:#fff;transform:translateY(-2px);box-shadow:0 10px 30px -10px rgba(34,211,238,.45)}
        .btn-outline-cy:active{transform:translateY(0)}

        .rv{opacity:0;transform:translateY(28px);transition:opacity .7s cubic-bezier(.16,1,.3,1),transform .7s cubic-bezier(.16,1,.3,1)}
        .rv.on{opacity:1;transform:translateY(0)}
        .rv-d1{transition-delay:.1s}.rv-d2{transition-delay:.2s}.rv-d3{transition-delay:.3s}.rv-d4{transition-delay:.4s}.rv-d5{transition-delay:.5s}

        .faq-c{max-height:0;overflow:hidden;transition:max-height .5s cubic-bezier(.16,1,.3,1),padding .3s ease}
        .faq-c.open{max-height:300px}
        .faq-i{transition:transform .4s cubic-bezier(.16,1,.3,1)}
        .faq-i.rot{transform:rotate(45deg)}

        .footer-item-content{max-height:0;overflow:hidden;transition:max-height .45s cubic-bezier(.16,1,.3,1),padding .25s ease}
        .footer-item-content.open{max-height:120px}
        .footer-item-icon{transition:transform .4s cubic-bezier(.16,1,.3,1)}
        .footer-item-icon.rot{transform:rotate(45deg)}

        .stat-i{position:relative}
        .stat-i:not(:last-child)::after{content:'';position:absolute;right:0;top:15%;height:70%;width:1px;background:rgba(34,211,238,0.08)}
        @media(max-width:768px){.stat-i:not(:last-child)::after{display:none}}

        .inner-glow{position:relative}
        .inner-glow::after{content:'';position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(34,211,238,0.1),transparent);pointer-events:none}

        @keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
        .typing-cursor{display:inline-block;width:2px;height:14px;background:#22d3ee;margin-left:2px;animation:blink 1s step-end infinite;vertical-align:middle}

        .nav-scrolled{background:rgba(2,6,23,.8)!important;border-color:rgba(34,211,238,.1)!important;box-shadow:0 8px 40px -12px rgba(0,0,0,.6)!important}

        .corner-accent{position:relative}
        .corner-accent::before,.corner-accent::after{content:'';position:absolute;width:16px;height:16px;pointer-events:none}
        .corner-accent::before{top:-1px;left:-1px;border-top:1px solid rgba(34,211,238,0.3);border-left:1px solid rgba(34,211,238,0.3)}
        .corner-accent::after{bottom:-1px;right:-1px;border-bottom:1px solid rgba(34,211,238,0.3);border-right:1px solid rgba(34,211,238,0.3)}

        @keyframes glitchSubtle{0%,100%{text-shadow:2px 0 #22d3ee,-2px 0 #818cf8}25%{text-shadow:-2px -1px #22d3ee,2px 1px #818cf8}50%{text-shadow:1px 2px #22d3ee,-1px -2px #818cf8}75%{text-shadow:-1px 1px #22d3ee,1px -1px #818cf8}}
        .glitch-subtle{animation:glitchSubtle 4s ease-in-out infinite}

        @keyframes dashDraw{from{stroke-dashoffset:251}to{stroke-dashoffset:33}}
        .ring-anim{animation:dashDraw 2s ease-out .8s both}

        @keyframes netPulse{0%,100%{opacity:.03}50%{opacity:.08}}
        .net-pulse{animation:netPulse 3s ease-in-out infinite}

        /* ═══ AUTH MODAL ═══ */
        #authOverlay{position:fixed;inset:0;z-index:100;pointer-events:none;opacity:0;transition:opacity .4s cubic-bezier(.16,1,.3,1)}
        #authOverlay.active{pointer-events:all;opacity:1}
        #authOverlay .overlay-bg{position:absolute;inset:0;background:rgba(2,6,23,0.7);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px)}

        #authModal{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.92);opacity:0;transition:transform .4s cubic-bezier(.16,1,.3,1),opacity .35s ease;width:min(440px,calc(100vw - 48px));max-height:calc(100vh - 64px);overflow-y:auto;pointer-events:none}
        #authOverlay.active #authModal{transform:translate(-50%,-50%) scale(1);opacity:1;pointer-events:all}
        #authOverlay.closing #authModal{transform:translate(-50%,-50%) scale(0.94);opacity:0}
        #authOverlay.closing{opacity:0}

        .auth-panel{display:none;flex-direction:column}
        .auth-panel.active-panel{display:flex;animation:panelFadeIn .35s cubic-bezier(.16,1,.3,1) forwards}
        .auth-panel.exiting{animation:panelFadeOut .2s ease forwards}
        @keyframes panelFadeIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
        @keyframes panelFadeOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(-16px)}}

        .auth-input{background:rgba(15,23,42,0.5);border:1px solid rgba(34,211,238,0.08);color:#e2e8f0;transition:all .25s ease;outline:none}
        .auth-input::placeholder{color:rgba(226,232,240,0.25)}
        .auth-input:focus{border-color:rgba(34,211,238,0.35);box-shadow:0 0 0 3px rgba(34,211,238,0.08),0 0 20px -4px rgba(34,211,238,0.06)}
        .auth-input:hover:not(:focus){border-color:rgba(34,211,238,0.15)}

        .auth-checkbox{appearance:none;-webkit-appearance:none;width:16px;height:16px;border:1px solid rgba(34,211,238,0.15);border-radius:4px;background:rgba(15,23,42,0.5);cursor:pointer;transition:all .2s ease;position:relative;flex-shrink:0}
        .auth-checkbox:checked{background:linear-gradient(135deg,#22d3ee,#0891b2);border-color:#22d3ee}
        .auth-checkbox:checked::after{content:'';position:absolute;top:2px;left:5px;width:4px;height:8px;border:solid #020617;border-width:0 2px 2px 0;transform:rotate(45deg)}
        .auth-checkbox:focus-visible{box-shadow:0 0 0 3px rgba(34,211,238,0.2)}

        .auth-link{color:rgba(34,211,238,0.7);transition:color .2s ease;cursor:pointer;background:none;border:none;padding:0;font:inherit}
        .auth-link:hover{color:#22d3ee}

        .auth-close{position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:rgba(226,232,240,0.4);background:rgba(15,23,42,0.4);border:1px solid rgba(34,211,238,0.06);transition:all .2s ease;cursor:pointer;z-index:2}
        .auth-close:hover{color:#e2e8f0;background:rgba(34,211,238,0.08);border-color:rgba(34,211,238,0.15)}
        .auth-close:focus-visible{box-shadow:0 0 0 3px rgba(34,211,238,0.2);outline:none}

        #authModal::-webkit-scrollbar{width:3px}
        #authModal::-webkit-scrollbar-track{background:transparent}
        #authModal::-webkit-scrollbar-thumb{background:rgba(34,211,238,0.1);border-radius:99px}

        @media(prefers-reduced-motion:reduce){
            #authOverlay,#authModal,.auth-panel,.auth-panel.active-panel,.auth-panel.exiting{animation:none!important;transition:none!important}
            #authOverlay.active #authModal{transform:translate(-50%,-50%) scale(1);opacity:1}
        }
    </style>
</head>
<body class="font-inter">

    @if(session('error'))
        <div id="serverErrorBanner" class="fixed top-6 left-1/2 -translate-x-1/2 z-60 max-w-[920px] w-[calc(100%-40px)] bg-red-600/10 border border-red-600/25 text-red-200 px-4 py-3 rounded-lg text-sm shadow-lg">
            {{ session('error') }}
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                try {
                    const msg = @json(session('error'));
                    if (msg) {
                        // open the auth modal and show the message in the choice panel
                        if (typeof openAuth === 'function') {
                            openAuth('choice');
                            setTimeout(() => {
                                const choicePanel = document.getElementById('authChoicePanel');
                                if (choicePanel) {
                                    const infoDiv = document.createElement('div');
                                    infoDiv.className = 'auth-error-msg bg-red-500/20 border border-red-500/40 text-red-200 px-4 py-3 rounded-lg mb-4 text-sm';
                                    infoDiv.textContent = msg;
                                    choicePanel.prepend(infoDiv);
                                }
                            }, 350);
                        }
                    }
                } catch (e) { console.error(e); }
            });
        </script>
    @endif

<div id="scrollProgress"></div>
<div id="spotlight"></div>

<!-- ═══════════════════════════════════════════════════ -->
<!-- BACKGROUND LAYERS                                   -->
<!-- ═══════════════════════════════════════════════════ -->
<div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full blur-[120px]" style="background:radial-gradient(circle,rgba(34,211,238,.08),transparent 60%);animation:aurora1 14s ease-in-out infinite"></div>
    <div class="absolute -bottom-48 -left-48 w-[550px] h-[550px] rounded-full blur-[140px]" style="background:radial-gradient(circle,rgba(129,140,248,.06),transparent 60%);animation:aurora2 18s ease-in-out infinite"></div>
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-[350px] h-[350px] rounded-full blur-[100px]" style="background:radial-gradient(circle,rgba(52,211,153,.04),transparent 60%);animation:aurora3 20s ease-in-out infinite"></div>
    <div class="absolute inset-0 circuit-grid opacity-60"></div>
    <div class="absolute top-20 right-[10%] slow-spin opacity-[0.03]">
        <svg width="350" height="350" viewBox="0 0 350 350" fill="none"><polygon points="175,10 330,90 330,260 175,340 20,260 20,90" stroke="#22d3ee" stroke-width=".5" fill="none"/><polygon points="175,50 290,110 290,240 175,300 60,240 60,110" stroke="#818cf8" stroke-width=".3" fill="none"/><polygon points="175,90 250,130 250,220 175,260 100,220 100,130" stroke="#34d399" stroke-width=".3" fill="none"/></svg>
    </div>
    <div class="absolute bottom-[20%] left-[5%] slow-spin opacity-[0.02]" style="animation-direction:reverse;animation-duration:90s">
        <svg width="250" height="250" viewBox="0 0 250 250" fill="none"><circle cx="125" cy="125" r="110" stroke="#22d3ee" stroke-width=".4" stroke-dasharray="8 20"/><circle cx="125" cy="125" r="75" stroke="#818cf8" stroke-width=".3" stroke-dasharray="4 15"/><circle cx="125" cy="125" r="40" stroke="#34d399" stroke-width=".3" stroke-dasharray="3 10"/></svg>
    </div>
    <div class="absolute top-[15%] left-[8%] w-1.5 h-1.5 rounded-full bg-cy/20 fl1"></div>
    <div class="absolute top-[55%] right-[10%] w-1 h-1 rounded-full bg-ac/20 fl2"></div>
    <div class="absolute top-[40%] left-[45%] w-1 h-1 rounded-full bg-gn/15 fl3"></div>
    <div class="absolute top-[75%] left-[25%] w-1.5 h-1.5 rounded-full bg-cy/10 fl4"></div>
    <div class="absolute top-[25%] right-[30%] w-1 h-1 rounded-full bg-ac/15 fl1"></div>
    <svg class="absolute inset-0 w-full h-full net-pulse" xmlns="http://www.w3.org/2000/svg">
        <line x1="10%" y1="20%" x2="30%" y2="35%" stroke="rgba(34,211,238,0.04)" stroke-width="0.5"/><line x1="30%" y1="35%" x2="55%" y2="28%" stroke="rgba(129,140,248,0.03)" stroke-width="0.5"/><line x1="55%" y1="28%" x2="80%" y2="45%" stroke="rgba(34,211,238,0.04)" stroke-width="0.5"/><line x1="80%" y1="45%" x2="65%" y2="70%" stroke="rgba(52,211,153,0.03)" stroke-width="0.5"/><line x1="65%" y1="70%" x2="35%" y2="65%" stroke="rgba(34,211,238,0.04)" stroke-width="0.5"/><line x1="35%" y1="65%" x2="15%" y2="80%" stroke="rgba(129,140,248,0.03)" stroke-width="0.5"/>
        <circle cx="10%" cy="20%" r="2" fill="rgba(34,211,238,0.15)"/><circle cx="30%" cy="35%" r="2" fill="rgba(34,211,238,0.12)"/><circle cx="55%" cy="28%" r="2" fill="rgba(129,140,248,0.12)"/><circle cx="80%" cy="45%" r="2" fill="rgba(34,211,238,0.12)"/><circle cx="65%" cy="70%" r="2" fill="rgba(52,211,153,0.12)"/><circle cx="35%" cy="65%" r="2" fill="rgba(34,211,238,0.12)"/><circle cx="15%" cy="80%" r="2" fill="rgba(129,140,248,0.12)"/>
    </svg>
</div>


<!-- ═══════════════════════════════════════════════════ -->
<!-- AUTH MODAL                                          -->
<!-- ═══════════════════════════════════════════════════ -->
<div id="authOverlay" aria-hidden="true">
    <div class="overlay-bg" onclick="closeAuth()"></div>
    <div id="authModal" role="dialog" aria-modal="true" aria-labelledby="authTitle">
        <div class="glow-card">
            <div class="glow-card-inner rounded-[19px] p-8 sm:p-10 relative overflow-hidden">
                <button class="auth-close" onclick="closeAuth()" aria-label="Close authentication panel">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
                <div class="absolute inset-0 pointer-events-none scanlines"></div>
                <div class="absolute inset-0 pointer-events-none circuit-grid opacity-30"></div>
                <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full blur-[60px] pointer-events-none" style="background:radial-gradient(circle,rgba(34,211,238,.1),transparent 60%)"></div>

                <div id="authChoicePanel" class="auth-panel active-panel relative z-10">
                    <div class="flex items-center justify-center gap-2.5 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cy to-cyan-700 flex items-center justify-center shadow-lg shadow-cy/20 relative">
                            <i class="fa-solid fa-brain text-n text-sm font-bold"></i>
                            <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-gn rounded-full border-2 border-n pulse-dot"></div>
                        </div>
                        <span class="text-base font-bold tracking-tight text-c">SHEE<span class="cyber-text">LEARN</span></span>
                    </div>

                    <h2 id="authTitle" class="text-2xl font-black text-c text-center mb-2 uppercase tracking-wide">Quick access to your learning hub</h2>
                    <p class="text-sm text-c-25 text-center mb-8">Choose the easiest way to sign in or create an account in seconds.</p>

                    <div class="space-y-4">
                        <button type="button" onclick="handleOAuthGoogle()" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider flex items-center justify-center gap-3">
                            <i class="fa-brands fa-google text-sm"></i>
                            Continue with Google
                        </button>
                    </div>

                    <p class="text-center text-xs text-c-25 mt-6">Already have an account? <button type="button" class="auth-link font-semibold" onclick="handleOAuthGoogle()">Sign in with Google</button></p>
                </div>

                <div id="authGmailPanel" class="auth-panel relative z-10" style="display:none">
                    <div class="flex items-center justify-center gap-2.5 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-cy/10 border border-cy/10 flex items-center justify-center shadow-lg shadow-cy/10 relative">
                            <i class="fa-solid fa-envelope text-cy text-sm"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 mb-4 text-[10px] uppercase tracking-[.25em] text-c-25">
                        <span class="px-2 py-1 rounded-full bg-cy/10 text-cy">1. Google</span>
                        <i class="fa-solid fa-arrow-right text-c-15"></i>
                        <span class="px-2 py-1 rounded-full bg-cy/10 text-cy">2. OTP</span>
                        <i class="fa-solid fa-arrow-right text-c-15"></i>
                        <span class="px-2 py-1 rounded-full bg-cy/10 text-cy">3. Profile</span>
                    </div>
                    <h2 class="text-2xl font-black text-c text-center mb-2 uppercase tracking-wide">Verify your Gmail</h2>
                    <p id="gmailPanelSubtitle" class="text-sm text-c-25 text-center mb-6">We’ll send a secure verification code to the Gmail account you choose.</p>
                    <form id="gmailStartForm" onsubmit="submitGoogleOtpStart(event)" novalidate>
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-cy/10 bg-cy/5 p-4 text-sm text-c-25">
                                <div class="flex items-center justify-between gap-3">
                                    <span id="gmailPreviewEmail" class="font-medium text-c">Checking Google account…</span>
                                    <i class="fa-solid fa-shield-halved text-cy"></i>
                                </div>
                            </div>
                            <button type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider">
                                Send verification code
                            </button>
                        </div>
                    </form>
                    <form id="gmailVerifyForm" onsubmit="submitGoogleOtpVerify(event)" class="space-y-4 mt-6" style="display:none" novalidate>
                        <div>
                            <label for="gmailOtp" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Verification Code</label>
                            <div class="relative">
                                <i class="fa-solid fa-key absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="gmailOtp" name="otp" type="text" maxlength="6" class="auth-input w-full pl-10 pr-4 py-3 rounded-xl text-sm font-mono" placeholder="000000" inputmode="numeric" autocomplete="one-time-code" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider">
                            Verify code
                        </button>
                        <button type="button" id="gmailResendBtn" class="btn-g w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider" onclick="resendGoogleOtp()">
                            Resend code
                        </button>
                    </form>
                    <form id="gmailProfileForm" onsubmit="submitGoogleProfile(event)" class="space-y-4 mt-6" style="display:none" novalidate>
                        <div>
                            <label for="gmailProfileName" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Full Name</label>
                            <div class="relative mb-3">
                                <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="gmailProfileName" name="name" type="text" class="auth-input w-full pl-10 pr-4 py-3 rounded-xl text-sm font-mono" placeholder="Your full name" autocomplete="name" required>
                            </div>
                            <label for="gmailUsername" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Username</label>
                            <div class="relative mb-3">
                                <i class="fa-solid fa-at absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="gmailUsername" name="username" type="text" class="auth-input w-full pl-10 pr-4 py-3 rounded-xl text-sm font-mono" placeholder="your_username" autocomplete="username" required>
                            </div>
                            <label for="gmailPassword" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Password</label>
                            <div class="relative mb-3">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="gmailPassword" name="password" type="password" class="auth-input w-full pl-10 pr-11 py-3 rounded-xl text-sm font-mono" placeholder="••••••••" autocomplete="new-password" required>
                                <button type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-c-15 hover:text-c-40 transition-colors" onclick="togglePasswordVisibility('gmailPassword',this)" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                            <label for="gmailPasswordConfirmation" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Confirm Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="gmailPasswordConfirmation" name="password_confirmation" type="password" class="auth-input w-full pl-10 pr-11 py-3 rounded-xl text-sm font-mono" placeholder="••••••••" autocomplete="new-password" required>
                            </div>
                            <div id="gmailPasswordStrength" class="text-xs text-c-25 mt-2"></div>
                        </div>
                        <button type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider">
                            Create account
                        </button>
                    </form>
                    <p class="text-center text-xs text-c-25 mt-6">Prefer another way in? <button type="button" class="auth-link font-semibold" onclick="openAuth('choice')">Back</button></p>
                </div>

                <div id="authLoginPanel" class="auth-panel relative z-10" style="display:none">
                    <div class="flex items-center justify-center gap-2.5 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cy to-cyan-700 flex items-center justify-center shadow-lg shadow-cy/20 relative">
                            <i class="fa-solid fa-brain text-n text-sm font-bold"></i>
                            <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-gn rounded-full border-2 border-n pulse-dot"></div>
                        </div>
                        <span class="text-base font-bold tracking-tight text-c">SHEE<span class="cyber-text">LEARN</span></span>
                    </div>
                    <h2 class="text-2xl font-black text-c text-center mb-2 uppercase tracking-wide">Sign in with password</h2>
                    <p class="text-sm text-c-25 text-center mb-8">Use your existing SHEELEARN credentials to access the dashboard.</p>
                    <form onsubmit="handleAuthSubmit(event,'login')" novalidate>
                        <div class="space-y-4">
                            <div>
                                <label for="loginEmail" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Email Address</label>
                                <div class="relative">
                                    <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                    <input id="loginEmail" name="email" type="email" class="auth-input w-full pl-10 pr-4 py-3 rounded-xl text-sm font-mono" placeholder="you@example.com" autocomplete="email" required>
                                </div>
                            </div>
                            <div>
                                <label for="loginPassword" class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Password</label>
                                <div class="relative">
                                    <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                    <input id="loginPassword" name="password" type="password" class="auth-input w-full pl-10 pr-11 py-3 rounded-xl text-sm font-mono" placeholder="••••••••" autocomplete="current-password" required>
                                    <button type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-c-15 hover:text-c-40 transition-colors" onclick="togglePasswordVisibility('loginPassword',this)" aria-label="Toggle password visibility">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider">
                                Sign In
                            </button>
                        </div>
                    </form>
                    <p class="text-center text-xs text-c-25 mt-6"><button type="button" onclick="openAuth('forgot')" class="auth-link font-semibold text-cy hover:underline transition-colors">Forgot Password?</button></p>
                    <div class="mt-4">
                        <button onclick="openAuth('choice')" class="btn-outline-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider" aria-label="Create Account">Create Account</button>
                    </div>
                </div>

                <div id="authForgotPanel" class="auth-panel relative z-10" style="display:none">
                    <div class="flex items-center justify-center gap-2.5 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-cy/10 border border-cy/10 flex items-center justify-center shadow-lg shadow-cy/10 relative">
                            <i class="fa-solid fa-key text-cy text-sm"></i>
                        </div>
                    </div>
                    <h2 class="text-2xl font-black text-c text-center mb-2 uppercase tracking-wide">Forgot Your Password?</h2>
                    <p class="text-sm text-c-25 text-center mb-6">Enter the Gmail address associated with your SHEELEARN account.</p>
                    <form id="forgotEmailForm" onsubmit="submitForgotModal(event)" novalidate>
                        <div>
                            <label class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Email Address</label>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="forgotEmail" name="email" type="email" class="auth-input w-full pl-10 pr-4 py-3 rounded-xl text-sm font-mono" placeholder="you@example.com" autocomplete="email" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider mt-6">Continue</button>
                    </form>

                    <form id="forgotVerifyForm" onsubmit="submitForgotVerify(event)" class="space-y-4 mt-6" style="display:none" novalidate>
                        <p class="text-center text-c-25 mb-2">We sent a 6-digit verification code to:</p>
                        <p id="forgotTargetEmail" class="text-center font-mono mb-4"></p>
                        <div id="forgotOtpGroup" class="grid grid-cols-6 gap-3 justify-center mb-4">
                            <input id="forgotDigit1" class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit 1" autocomplete="one-time-code" required>
                            <input id="forgotDigit2" class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit 2" autocomplete="one-time-code" required>
                            <input id="forgotDigit3" class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit 3" autocomplete="one-time-code" required>
                            <input id="forgotDigit4" class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit 4" autocomplete="one-time-code" required>
                            <input id="forgotDigit5" class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit 5" autocomplete="one-time-code" required>
                            <input id="forgotDigit6" class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" aria-label="Digit 6" autocomplete="one-time-code" required>
                        </div>
                        <button id="forgotVerifyButton" type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider" disabled>Verify Code</button>
                        <div class="flex items-center justify-between mt-4">
                            <button type="button" id="forgotResendBtn" class="btn-g text-xs" onclick="resendForgotModal()">Resend Code</button>
                            <div id="forgotCountdown" class="text-xs text-c-25">Resend available in 60s</div>
                        </div>
                    </form>

                    <form id="forgotResetForm" onsubmit="submitForgotReset(event)" class="space-y-4 mt-6" style="display:none" novalidate>
                        <div>
                            <label class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">New Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="forgotNewPassword" name="password" type="password" class="auth-input w-full pl-10 pr-11 py-3 rounded-xl text-sm font-mono" placeholder="••••••••" required>
                                <button type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-c-15 hover:text-c-40 transition-colors" onclick="togglePasswordVisibility('forgotNewPassword',this)" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-c-40 uppercase tracking-[.15em] font-mono mb-2">Confirm Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-c-15 text-xs"></i>
                                <input id="forgotConfirmPassword" name="password_confirmation" type="password" class="auth-input w-full pl-10 pr-11 py-3 rounded-xl text-sm font-mono" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div id="forgotPwStrength" class="text-xs text-c-25 mt-2"></div>
                        <button type="submit" class="btn-cy w-full text-xs font-bold px-6 py-3.5 rounded-xl uppercase tracking-wider">Save Password</button>
                    </form>

                    <p class="text-center text-xs text-c-25 mt-6"><button type="button" class="auth-link font-semibold" onclick="openAuth('login')">Back to Sign In</button></p>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════ -->
<!-- TOP NAVIGATION BAR                                  -->
<!-- ═══════════════════════════════════════════════════ -->
<nav id="navbar" class="fixed top-4 left-4 right-4 z-50 gnav rounded-2xl px-4 py-2.5 transition-all duration-500 max-w-[calc(100vw-32px)]">
    <div class="flex items-center gap-1">
        <a href="#" id="welcomeLogoTrigger" class="flex items-center gap-2.5 px-3 py-1.5 flex-shrink-0 cursor-pointer">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cy to-cyan-700 flex items-center justify-center shadow-lg shadow-cy/20 relative">
                <i class="fa-solid fa-brain text-n text-sm font-bold"></i>
                <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-gn rounded-full border-2 border-n pulse-dot"></div>
            </div>
            <span class="text-base font-bold tracking-tight text-c">SHEE<span class="cyber-text">LEARN</span></span>
        </a>

        <div class="hidden lg:flex items-center gap-0.5 ml-4">
            <a href="#home" class="text-[11px] font-semibold text-cy/70 hover:text-cy px-4 py-2 rounded-lg hover:bg-cy/5 transition-all duration-200 uppercase tracking-wider">Dashboard</a>
            <a href="#features" class="text-[11px] font-medium text-c-25 hover:text-cy px-4 py-2 rounded-lg hover:bg-cy/5 transition-all duration-200 uppercase tracking-wider">Features</a>
            <a href="#about" class="text-[11px] font-medium text-c-25 hover:text-cy px-4 py-2 rounded-lg hover:bg-cy/5 transition-all duration-200 uppercase tracking-wider">About Us</a>
            <a href="#faq" class="text-[11px] font-medium text-c-25 hover:text-cy px-4 py-2 rounded-lg hover:bg-cy/5 transition-all duration-200 uppercase tracking-wider">News</a>
        </div>

        <div class="flex-1"></div>

        <div class="hidden lg:flex items-center gap-2">
            <button onclick="openAuth('login')" class="text-[11px] font-medium text-c-25 hover:text-c-40 px-4 py-2 rounded-lg hover:bg-c-5 transition-all duration-200 cursor-pointer">Login</button>
            <button onclick="openAuth('choice')" class="btn-cy text-[11px] font-bold px-5 py-2.5 rounded-lg inline-flex items-center gap-2 uppercase tracking-wider cursor-pointer">Get Started<i class="fa-solid fa-arrow-right text-[9px]"></i></button>
        </div>

        <button id="mobileMenuBtn" class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center text-c-40 hover:text-cy hover:bg-cy/5 transition-all ml-1 cursor-pointer" aria-label="Toggle menu">
            <i class="fa-solid fa-bars text-sm" id="menuIcon"></i>
        </button>
    </div>
</nav>

<!-- Mobile Menu -->
<div id="mobileMenu" class="fixed top-20 left-4 right-4 z-40 hidden">
    <div class="g3 rounded-2xl p-4 shadow-2xl shadow-black/40">
        <div class="space-y-1">
            <a href="#home" class="block text-sm font-medium text-c-40 hover:text-cy hover:bg-cy/5 px-4 py-3 rounded-xl transition-all">Dashboard</a>
            <a href="#features" class="block text-sm font-medium text-c-40 hover:text-cy hover:bg-cy/5 px-4 py-3 rounded-xl transition-all">Features</a>
            <a href="#about" class="block text-sm font-medium text-c-40 hover:text-cy hover:bg-cy/5 px-4 py-3 rounded-xl transition-all">About Us</a>
            <a href="#faq" class="block text-sm font-medium text-c-40 hover:text-cy hover:bg-cy/5 px-4 py-3 rounded-xl transition-all">News</a>
            <a href="#contact" class="block text-sm font-medium text-c-40 hover:text-cy hover:bg-cy/5 px-4 py-3 rounded-xl transition-all">Contact</a>
            <div class="pt-2 mt-2 border-t border-c-5 space-y-2">
                <button onclick="openAuth('login');closeMobileMenu()" class="block w-full text-center text-sm font-medium text-c-40 hover:text-c-60 px-4 py-3 rounded-xl hover:bg-c-5 transition-all cursor-pointer">Login</button>
                <button onclick="openAuth('choice');closeMobileMenu()" class="block w-full text-center btn-cy text-sm font-bold px-4 py-3 rounded-xl uppercase tracking-wider cursor-pointer">Get Started</button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════ -->
<!-- HERO                                                -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="home" class="relative z-10 pt-28 sm:pt-36 lg:pt-44 pb-16 sm:pb-28 scanlines">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="max-w-xl">
                <div class="rv inline-flex items-center gap-2.5 g1 rounded-lg px-4 py-2.5 mb-8 inner-glow">
                    <div class="w-1.5 h-1.5 bg-cy rounded-full pulse-dot"></div>
                    <span class="text-[10px] font-bold text-cy/80 tracking-[.2em] uppercase font-mono">AI-Powered Learning Platform</span>
                </div>

                <h1 class="rv rv-d2 mb-4">
                    <span class="block text-4xl sm:text-5xl lg:text-[3.8rem] font-black leading-[1.05] tracking-tight cyber-text-flow uppercase glitch-subtle">SHEELEARN</span>
                </h1>

                <h1 class="rv rv-d1 mb-1">
                    <span class="block text-4xl sm:text-5xl lg:text-[1.8rem] font-black leading-[1.05]">Smart Hub for Education, Exploration, Learning, Evaluation, AI, Research, and Notes.</span>
                </h1>

                <p class="rv rv-d3 text-sm sm:text-base text-c-25 leading-relaxed mb-10 max-w-md font-light">
                    Protect your study time from inefficiency. Our AI-powered platform transforms notes, PDFs, and study materials into summaries, quizzes, flashcards, and intelligent conversations — all in one secure workspace.
                </p>

                <div class="rv rv-d4 flex flex-wrap gap-3">
                    <button onclick="openAuth('choice')" class="btn-cy text-xs font-bold px-7 py-3.5 rounded-lg inline-flex items-center gap-2.5 uppercase tracking-wider cursor-pointer">Get Started<i class="fa-solid fa-arrow-right text-[10px]"></i></button>
                    <a href="#features" class="btn-g text-xs font-medium px-7 py-3.5 rounded-lg inline-flex items-center gap-2.5"><i class="fa-regular fa-circle-play text-sm text-cy/50"></i>Explore Features</a>
                </div>

                <div class="rv rv-d5 mt-10 flex items-center gap-5">
                    <div class="flex -space-x-2" id="heroAvatars">
                        @foreach (array_slice($welcomeStats['featured_users'], 0, 3) as $avatar)
                            <img src="{{ $avatar }}" alt="" class="w-8 h-8 rounded-lg border-2 border-n object-cover">
                        @endforeach
                        @php $remainingUsers = max(0, (int) ($welcomeStats['total_users_raw'] ?? 0) - 3); @endphp
                        @if($remainingUsers > 0)
                            <div class="w-8 h-8 rounded-lg border-2 border-n bg-n-2 text-cy text-[9px] font-bold flex items-center justify-center font-mono" id="userCountBadge">+{{ $remainingUsers >= 1000 ? number_format($remainingUsers / 1000, 0) . 'K' : $remainingUsers }}</div>
                        @else
                            <div class="w-8 h-8 rounded-lg border-2 border-n bg-n-2 text-cy text-[9px] font-bold flex items-center justify-center font-mono" id="userCountBadge"></div>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-0.5 text-cy text-[10px]" id="heroStars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-[10px] text-c-15 mt-0.5" id="heroUserText">Trusted by {{ $welcomeStats['total_users'] }} users</p>
                    </div>
                </div>
            </div>

            <!-- Right: Cyber Dashboard -->
            <div class="rv rv-d2 relative" id="heroDash">
                <div class="glow-card">
                    <div class="glow-card-inner rounded-[19px] p-5 shadow-2xl shadow-black/40 scanlines">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-cy/60"></div>
                                <div class="w-2 h-2 rounded-full bg-ac/40"></div>
                                <div class="w-2 h-2 rounded-full bg-gn/30"></div>
                            </div>
                            <div class="g1 rounded-lg px-3 py-1 text-[9px] text-cy/50 font-mono font-medium tracking-wider">sheelearn.app/dashboard</div>
                            <div class="flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-gn pulse-dot"></div>
                                <span class="text-[8px] text-gn/60 font-mono">SECURE</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="fl1 col-span-2 rounded-xl p-4 border border-cy/10 corner-accent" style="background:linear-gradient(135deg,rgba(34,211,238,.04),rgba(129,140,248,.02))">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fa-solid fa-robot text-cy/40 text-xs"></i>
                                    <span class="text-[9px] font-bold text-cy/40 uppercase tracking-[.15em] font-mono">AI Neural Chat</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="bg-cy/5 rounded-lg px-3 py-2 text-[10px] text-c-40 border border-cy/5">Explain quantum entanglement</div>
                                    <div class="bg-ac/5 rounded-lg px-3 py-2 text-[10px] text-c-60 ml-6 border border-ac/5">Think of it like two connected coins<span class="typing-cursor"></span></div>
                                </div>
                            </div>

                            <div class="fl2 g2 rounded-xl p-4 flex flex-col items-center justify-center">
                                <svg width="64" height="64" viewBox="0 0 64 64" class="mb-2">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="rgba(34,211,238,0.08)" stroke-width="3"/>
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="url(#ringGrad)" stroke-width="3" stroke-linecap="round" stroke-dasharray="176" stroke-dashoffset="23" transform="rotate(-90 32 32)" class="ring-anim"/>
                                    <defs><linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#22d3ee"/><stop offset="100%" stop-color="#818cf8"/></linearGradient></defs>
                                </svg>
                                <span class="text-lg font-bold cyber-text font-mono">87%</span>
                                <span class="text-[8px] text-c-15 font-mono mt-0.5">+12% this week</span>
                            </div>

                            <div class="fl3 g2 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-5 h-5 rounded-md bg-cy/8 flex items-center justify-center"><i class="fa-solid fa-note-sticky text-cy/40 text-[8px]"></i></div>
                                    <span class="text-[9px] font-bold text-c-40 uppercase tracking-wider font-mono">Notes</span>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="h-1.5 bg-cy/8 rounded-full w-full"></div>
                                    <div class="h-1.5 bg-cy/6 rounded-full w-4/5"></div>
                                    <div class="h-1.5 bg-cy/4 rounded-full w-3/5"></div>
                                </div>
                            </div>

                            <div class="fl4 col-span-2 g2 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-gn/8 flex items-center justify-center"><i class="fa-solid fa-layer-group text-gn/40 text-[8px]"></i></div>
                                        <span class="text-[9px] font-bold text-c-40 uppercase tracking-wider font-mono">Flashcards</span>
                                    </div>
                                    <span class="text-[8px] font-bold text-cy bg-cy/8 px-2 py-0.5 rounded-md font-mono">24 NEW</span>
                                </div>
                                <div class="flex gap-2">
                                    <div class="flex-1 bg-c-3 rounded-lg p-2.5 text-center border border-c-5"><p class="text-[7px] text-c-15 mb-1 font-mono uppercase">Front</p><p class="text-[9px] font-semibold text-c-60">Mitosis</p></div>
                                    <div class="flex-1 bg-c-3 rounded-lg p-2.5 text-center border border-c-5"><p class="text-[7px] text-c-15 mb-1 font-mono uppercase">Back</p><p class="text-[9px] font-semibold text-c-60">Cell Division</p></div>
                                    <div class="flex-1 bg-c-3 rounded-lg p-2.5 text-center border border-gn/10"><p class="text-[7px] text-c-15 mb-1 font-mono uppercase">Status</p><p class="text-[9px] font-semibold text-gn/70">Mastered</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-4 -left-4 fl2 glow-card-alt w-40">
                    <div class="glow-card-inner rounded-[19px] p-3">
                        <div class="flex items-center gap-2 mb-1.5"><div class="w-5 h-5 rounded-md bg-ac/10 flex items-center justify-center"><i class="fa-solid fa-clipboard-question text-ac/50 text-[8px]"></i></div><span class="text-[9px] font-bold text-c-40 font-mono uppercase">Quiz</span></div>
                        <div class="flex items-center gap-2"><div class="text-lg font-bold cyber-text font-mono">9/10</div><div class="text-[8px] text-c-25">Excellent!</div></div>
                    </div>
                </div>

                <div class="absolute -top-3 -right-3 fl3 glow-card-alt">
                    <div class="glow-card-inner rounded-[19px] p-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cy to-cyan-700 flex items-center justify-center shadow-lg shadow-cy/20"><i class="fa-solid fa-bolt text-n text-[9px]"></i></div>
                            <div><p class="text-[8px] font-bold text-c-60 font-mono">AI Summarized</p><p class="text-[7px] text-c-15 font-mono">Ch.5 → 120 words</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- MARQUEE                                             -->
<!-- ═══════════════════════════════════════════════════ -->
<div class="relative z-10 py-5 border-y border-cy/5 overflow-hidden">
    <div class="flex whitespace-nowrap marquee-track">
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">AI Summarizer</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Chat with PDF</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Flashcards</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Quiz Generator</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Study Planner</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Analytics</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Research</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Notes</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">AI Summarizer</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Chat with PDF</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Flashcards</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Quiz Generator</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Study Planner</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Analytics</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Research</span><span class="mx-3 text-cy/15">◆</span>
        <span class="mx-8 text-[11px] font-bold text-c-8 uppercase tracking-[.25em] font-mono">Notes</span><span class="mx-3 text-cy/15">◆</span>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════ -->
<!-- FEATURES — BENTO GRID                               -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="features" class="relative z-10 py-24 sm:py-32 hex-grid">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="rv inline-flex items-center gap-2.5 g1 rounded-lg px-4 py-2.5 mb-6 inner-glow"><i class="fa-solid fa-cube text-cy/50 text-[10px]"></i><span class="text-[10px] font-bold text-cy/80 tracking-[.2em] uppercase font-mono">Core Features</span></div>
            <h2 class="rv rv-d1 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-c mb-5 uppercase">Everything you need to <span class="cyber-text-flow">learn smarter</span></h2>
            <p class="rv rv-d2 text-sm sm:text-base text-c-25 leading-relaxed">Powerful AI tools designed to transform how you study, research, and retain information.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            <div class="rv lg:col-span-2 lg:row-span-2"><div class="glow-card h-full"><div class="glow-card-inner rounded-[19px] p-8 sm:p-10 flex flex-col justify-between h-full tilt-card light-leak scanlines"><div><div class="w-14 h-14 rounded-2xl bg-ac/8 border border-ac/10 flex items-center justify-center mb-6"><i class="fa-solid fa-robot text-ac text-xl"></i></div><h3 class="text-xl sm:text-2xl font-bold text-c mb-3 uppercase tracking-wide">AI Chat</h3><p class="text-sm sm:text-base text-c-25 leading-relaxed max-w-lg">Ask intelligent questions about your notes, documents, PDFs, or any topic. Get instant, context-aware responses powered by advanced AI that understands your learning material.</p></div><div class="mt-8 g2 rounded-xl p-5 inner-glow corner-accent"><div class="flex items-center gap-2 mb-3"><i class="fa-solid fa-message text-ac/30 text-xs"></i><span class="text-[9px] font-bold text-c-25 uppercase tracking-wider font-mono">Chat Example: Explain quantum entanglement</span></div><div class="space-y-1.5"><div class="h-2 bg-ac/10 rounded-full w-full"></div><div class="h-2 bg-ac/8 rounded-full w-5/6"></div><div class="h-2 bg-ac/6 rounded-full w-4/6"></div></div><div class="mt-4 flex items-center gap-2 text-ac text-[10px] font-bold font-mono"><i class="fa-solid fa-arrow-down"></i> Response generated in 0.8s</div></div></div></div></div>
            <div class="rv rv-d1"><div class="feat-card g2 rounded-[18px] p-7 h-full tilt-card light-leak inner-glow"><div class="w-12 h-12 rounded-xl bg-cy/8 border border-cy/8 flex items-center justify-center mb-5"><i class="fa-solid fa-wand-magic-sparkles text-cy/60 text-lg"></i></div><h3 class="text-sm font-bold text-c mb-2 uppercase tracking-wide">AI Summarizer</h3><p class="text-sm text-c-25 leading-relaxed">Instantly condense lengthy notes and textbooks into clear, concise summaries with key concepts highlighted.</p></div></div>
            <div class="rv rv-d2"><div class="feat-card g2 rounded-[18px] p-7 h-full tilt-card light-leak inner-glow"><div class="w-12 h-12 rounded-xl bg-gn/8 border border-gn/8 flex items-center justify-center mb-5"><i class="fa-solid fa-layer-group text-gn/60 text-lg"></i></div><h3 class="text-sm font-bold text-c mb-2 uppercase tracking-wide">Flashcards</h3><p class="text-sm text-c-25 leading-relaxed">Auto-generate cards with spaced repetition for maximum long-term retention.</p></div></div>
            <div class="rv"><div class="glow-card-alt h-full"><div class="glow-card-inner rounded-[19px] p-7 h-full tilt-card light-leak"><div class="w-12 h-12 rounded-xl bg-cy/8 border border-cy/8 flex items-center justify-center mb-5"><i class="fa-solid fa-clipboard-question text-cy/60 text-lg"></i></div><h3 class="text-sm font-bold text-c mb-2 uppercase tracking-wide">Quiz Generator</h3><p class="text-sm text-c-25 leading-relaxed">Create custom quizzes with multiple-choice, true/false, and short-answer questions.</p></div></div></div>
            <div class="rv rv-d1"><div class="feat-card g2 rounded-[18px] p-7 h-full tilt-card light-leak inner-glow"><div class="w-12 h-12 rounded-xl bg-rose-400/8 border border-rose-400/8 flex items-center justify-center mb-5"><i class="fa-solid fa-calendar-check text-rose-400/60 text-lg"></i></div><h3 class="text-sm font-bold text-c mb-2 uppercase tracking-wide">Study Planner</h3><p class="text-sm text-c-25 leading-relaxed">AI creates personalized schedules based on your deadlines and learning pace.</p></div></div>
            <div class="rv rv-d2"><div class="feat-card g2 rounded-[18px] p-7 h-full tilt-card light-leak inner-glow"><div class="w-12 h-12 rounded-xl bg-purple-400/8 border border-purple-400/8 flex items-center justify-center mb-5"><i class="fa-solid fa-notebook text-purple-400/60 text-lg"></i></div><h3 class="text-sm font-bold text-c mb-2 uppercase tracking-wide">Smart Notes</h3><p class="text-sm text-c-25 leading-relaxed">Organize, enhance, and automatically tag your notes with AI-powered organization.</p></div></div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- HOW IT WORKS                                        -->
<!-- ═══════════════════════════════════════════════════ -->
<section class="relative z-10 pt-16 pb-20 sm:pt-20 sm:pb-24">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14">
            <div class="rv inline-flex items-center gap-2.5 g1 rounded-lg px-4 py-2.5 mb-6 inner-glow"><i class="fa-solid fa-route text-cy/50 text-[10px]"></i><span class="text-[10px] font-bold text-cy/80 tracking-[.2em] uppercase font-mono">How It Works</span></div>
            <h2 class="rv rv-d1 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-c mb-4 uppercase">The SHEELEARN workflow <span class="cyber-text-flow">in 3 steps</span></h2>
            <p class="rv rv-d2 text-sm sm:text-base text-c-25 leading-relaxed">Upload your study material, let AI extract and summarize it, then review with intelligent learning tools.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 md:gap-6 max-w-5xl mx-auto relative">
            <div class="hidden md:block absolute top-7 left-[16%] right-[16%] h-px" style="background:linear-gradient(90deg,rgba(34,211,238,.15),rgba(129,140,248,.1),rgba(52,211,153,.15))"></div>
            <div class="rv text-center relative"><div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-cy to-cyan-700 text-n text-lg font-black mb-5 shadow-lg shadow-cy/15 relative z-10 font-mono">01</div><h3 class="text-base font-bold text-c mb-2 uppercase tracking-wide">Upload Your Content</h3><p class="text-sm text-c-25 leading-relaxed max-w-xs mx-auto">Add notes, PDFs, slides, images, or plain text. Supported formats are processed automatically for study-ready review.</p></div>
            <div class="rv rv-d1 text-center relative"><div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-ac to-indigo-600 text-n text-lg font-black mb-5 shadow-lg shadow-ac/15 relative z-10 font-mono">02</div><h3 class="text-base font-bold text-c mb-2 uppercase tracking-wide">AI Extracts & Summarizes</h3><p class="text-sm text-c-25 leading-relaxed max-w-xs mx-auto">The platform extracts text, generates summaries, and indexes your material so everything is ready for learning.</p></div>
            <div class="rv rv-d2 text-center relative"><div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-gn to-emerald-600 text-n text-lg font-black mb-5 shadow-lg shadow-gn/15 relative z-10 font-mono">03</div><h3 class="text-base font-bold text-c mb-2 uppercase tracking-wide">Study with AI Tools</h3><p class="text-sm text-c-25 leading-relaxed max-w-xs mx-auto">Use AI Chat, flashcards, quizzes and a study planner to review, retain and act on the content you uploaded.</p></div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- WHY SHEELEARN                                       -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="about" class="relative z-10 py-24 sm:py-32 dot-grid">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 lg:gap-20 items-center">
            <div class="rv relative"><div class="glow-card"><div class="glow-card-inner rounded-[19px] p-8 sm:p-10 shadow-2xl shadow-black/40 scanlines"><div class="space-y-4"><div class="flex items-center gap-4 g2 rounded-xl p-5"><div class="w-11 h-11 rounded-xl bg-cy/8 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-clock text-cy/50"></i></div><div class="flex-1"><p class="text-c-60 font-semibold text-sm">Time Saved</p><p class="text-c-15 text-xs mt-0.5">Average per week</p></div><div class="text-2xl font-black cyber-text font-mono">4.5h</div></div><div class="flex items-center gap-4 g2 rounded-xl p-5"><div class="w-11 h-11 rounded-xl bg-cy/8 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-bullseye text-cy/50"></i></div><div class="flex-1"><p class="text-c-60 font-semibold text-sm">Accuracy Rate</p><p class="text-c-15 text-xs mt-0.5">AI content quality</p></div><div class="text-2xl font-black cyber-text font-mono">98%</div></div><div class="flex items-center gap-4 g2 rounded-xl p-5"><div class="w-11 h-11 rounded-xl bg-cy/8 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-graduation-cap text-cy/50"></i></div><div class="flex-1"><p class="text-c-60 font-semibold text-sm">Grade Improvement</p><p class="text-c-15 text-xs mt-0.5">Average increase</p></div><div class="text-2xl font-black cyber-text font-mono">+99.99%</div></div></div></div></div></div>
            <div>
                <div class="rv inline-flex items-center gap-2.5 g1 rounded-lg px-4 py-2.5 mb-6 inner-glow"><i class="fa-solid fa-shield-halved text-cy/50 text-[10px]"></i><span class="text-[10px] font-bold text-cy/80 tracking-[.2em] uppercase font-mono">Why SHEELEARN</span></div>
                <h2 class="rv rv-d1 text-3xl sm:text-4xl font-black tracking-tight text-c mb-5 uppercase">Built for students who <span class="cyber-text-flow">demand more</span></h2>
                <p class="rv rv-d2 text-sm text-c-25 leading-relaxed mb-10 max-w-md">We combined cutting-edge AI with thoughtful design to create a learning experience that actually works.</p>
                <div class="space-y-2">
                    <div class="rv rv-d1 flex items-start gap-4 p-4 rounded-xl hover:bg-cy/3 transition-all duration-300 group"><div class="w-7 h-7 rounded-lg bg-cy/5 border border-cy/8 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-cy/10 transition-colors"><i class="fa-solid fa-check text-cy/50 text-[10px]"></i></div><div><h4 class="text-sm font-semibold text-c-60 mb-0.5">Save Study Time</h4><p class="text-sm text-c-25">Automate tedious tasks and focus on understanding.</p></div></div>
                    <div class="rv rv-d2 flex items-start gap-4 p-4 rounded-xl hover:bg-cy/3 transition-all duration-300 group"><div class="w-7 h-7 rounded-lg bg-cy/5 border border-cy/8 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-cy/10 transition-colors"><i class="fa-solid fa-check text-cy/50 text-[10px]"></i></div><div><h4 class="text-sm font-semibold text-c-60 mb-0.5">AI Powered Learning</h4><p class="text-sm text-c-25">State-of-the-art models for accurate, context-aware results.</p></div></div>
                    <div class="rv rv-d3 flex items-start gap-4 p-4 rounded-xl hover:bg-cy/3 transition-all duration-300 group"><div class="w-7 h-7 rounded-lg bg-cy/5 border border-cy/8 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-cy/10 transition-colors"><i class="fa-solid fa-check text-cy/50 text-[10px]"></i></div><div><h4 class="text-sm font-semibold text-c-60 mb-0.5">Personalized Experience</h4><p class="text-sm text-c-25">Adapts to your learning style, pace, and preferences.</p></div></div>
                    <div class="rv rv-d4 flex items-start gap-4 p-4 rounded-xl hover:bg-cy/3 transition-all duration-300 group"><div class="w-7 h-7 rounded-lg bg-cy/5 border border-cy/8 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-cy/10 transition-colors"><i class="fa-solid fa-check text-cy/50 text-[10px]"></i></div><div><h4 class="text-sm font-semibold text-c-60 mb-0.5">Learn Anywhere</h4><p class="text-sm text-c-25">Cloud-based platform accessible from any device.</p></div></div>
                    <div class="rv rv-d5 flex items-start gap-4 p-4 rounded-xl hover:bg-cy/3 transition-all duration-300 group"><div class="w-7 h-7 rounded-lg bg-gn/5 border border-gn/8 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-gn/10 transition-colors"><i class="fa-solid fa-lock text-gn/50 text-[10px]"></i></div><div><h4 class="text-sm font-semibold text-c-60 mb-0.5">Secure & Compliant</h4><p class="text-sm text-c-25">Encrypted storage. GDPR & FERPA compliant.</p></div></div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- STATISTICS                                          -->
<!-- ═══════════════════════════════════════════════════ -->
<section class="relative z-10 py-20 sm:py-24">
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at center,rgba(34,211,238,.03) 0%,transparent 60%)"></div>
    <div class="relative max-w-5xl mx-auto px-6 lg:px-8">
        <div class="glow-card"><div class="glow-card-inner rounded-[19px] p-8 sm:p-12 shadow-2xl shadow-black/30 scanlines"><div class="grid grid-cols-2 md:grid-cols-3 gap-8 md:gap-0">
            <div class="rv stat-i text-center px-4"><div class="text-3xl sm:text-4xl font-black cyber-text-flow mb-2 font-mono" data-stat="total_users">{{ $welcomeStats['total_users'] }}</div><p class="text-xs text-c-25 uppercase tracking-wider font-mono">Active Users</p></div>
            <div class="rv rv-d1 stat-i text-center px-4"><div class="text-3xl sm:text-4xl font-black text-c mb-2 font-mono" data-stat="total_documents">{{ $welcomeStats['total_documents'] }}</div><p class="text-xs text-c-25 uppercase tracking-wider font-mono">Notes Processed</p></div>
            <div class="rv rv-d2 stat-i text-center px-4"><div class="text-3xl sm:text-4xl font-black text-c mb-2 font-mono" data-stat="ai_accuracy">{{ $welcomeStats['ai_accuracy'] }}%</div><p class="text-xs text-c-25 uppercase tracking-wider font-mono">AI Accuracy %</p></div>
        </div></div></div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- FAQ                                                 -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="faq" class="relative z-10 py-24 sm:py-32">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16">
            <div class="rv inline-flex items-center gap-2.5 g1 rounded-lg px-4 py-2.5 mb-6 inner-glow"><i class="fa-solid fa-circle-question text-cy/50 text-[10px]"></i><span class="text-[10px] font-bold text-cy/80 tracking-[.2em] uppercase font-mono">FAQ</span></div>
            <h2 class="rv rv-d1 text-3xl sm:text-4xl font-black tracking-tight text-c mb-5 uppercase">Frequently asked <span class="cyber-text-flow">questions</span></h2>
        </div>
        <div class="space-y-3">
            <div class="rv g2 rounded-xl overflow-hidden"><button class="faq-toggle w-full flex items-center justify-between p-5 text-left group"><span class="text-sm font-semibold text-c-60 group-hover:text-c transition-colors pr-4">Is SHEELEARN free to use?</span><i class="fa-solid fa-plus text-cy/40 text-xs faq-i flex-shrink-0"></i></button><div class="faq-c px-5"><p class="text-sm text-c-25 leading-relaxed pb-5">Yes! SHEELEARN offers a generous free tier that includes AI summaries, basic flashcards, and limited PDF chats. Premium plans unlock unlimited access to all features.</p></div></div>
            <div class="rv rv-d1 g2 rounded-xl overflow-hidden"><button class="faq-toggle w-full flex items-center justify-between p-5 text-left group"><span class="text-sm font-semibold text-c-60 group-hover:text-c transition-colors pr-4">What file formats are supported?</span><i class="fa-solid fa-plus text-cy/40 text-xs faq-i flex-shrink-0"></i></button><div class="faq-c px-5"><p class="text-sm text-c-25 leading-relaxed pb-5">We support PDF, DOCX, TXT, Markdown, and image files (JPG, PNG) with OCR. You can also paste text directly or type notes in our editor.</p></div></div>
            <div class="rv rv-d2 g2 rounded-xl overflow-hidden"><button class="faq-toggle w-full flex items-center justify-between p-5 text-left group"><span class="text-sm font-semibold text-c-60 group-hover:text-c transition-colors pr-4">Is my data safe and private?</span><i class="fa-solid fa-plus text-cy/40 text-xs faq-i flex-shrink-0"></i></button><div class="faq-c px-5"><p class="text-sm text-c-25 leading-relaxed pb-5">Absolutely. All data is encrypted at rest and in transit using AES-256 encryption. We are GDPR and FERPA compliant.</p></div></div>
            <div class="rv rv-d3 g2 rounded-xl overflow-hidden"><button class="faq-toggle w-full flex items-center justify-between p-5 text-left group"><span class="text-sm font-semibold text-c-60 group-hover:text-c transition-colors pr-4">Can I use SHEELEARN offline?</span><i class="fa-solid fa-plus text-cy/40 text-xs faq-i flex-shrink-0"></i></button><div class="faq-c px-5"><p class="text-sm text-c-25 leading-relaxed pb-5">Core features like flashcards and notes are available offline once synced. AI-powered features require an internet connection.</p></div></div>
            <div class="rv rv-d4 g2 rounded-xl overflow-hidden"><button class="faq-toggle w-full flex items-center justify-between p-5 text-left group"><span class="text-sm font-semibold text-c-60 group-hover:text-c transition-colors pr-4">How accurate are the AI summaries?</span><i class="fa-solid fa-plus text-cy/40 text-xs faq-i flex-shrink-0"></i></button><div class="faq-c px-5"><p class="text-sm text-c-25 leading-relaxed pb-5">Our AI achieves 98% accuracy on factual content extraction, trained on academic materials to identify key concepts and relationships.</p></div></div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- CTA                                                 -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="contact" class="relative z-10 py-24 sm:py-32">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        <div class="glow-card"><div class="glow-card-inner rounded-[19px] p-10 sm:p-16 text-center relative overflow-hidden scanlines">
            <div class="absolute inset-0 pointer-events-none circuit-grid opacity-40"></div>
            <div class="absolute -top-20 -right-20 w-60 h-60 rounded-full blur-[80px] opacity-30" style="background:radial-gradient(circle,rgba(34,211,238,.15),transparent 60%)"></div>
            <div class="absolute -bottom-20 -left-20 w-60 h-60 rounded-full blur-[80px] opacity-20" style="background:radial-gradient(circle,rgba(129,140,248,.15),transparent 60%)"></div>
            <div class="relative z-10">
                <div class="rv inline-flex items-center gap-2.5 g1 rounded-lg px-4 py-2.5 mb-8 inner-glow"><i class="fa-solid fa-rocket text-cy/50 text-[10px]"></i><span class="text-[10px] font-bold text-cy/80 tracking-[.2em] uppercase font-mono">Get Started</span></div>
                <h2 class="rv rv-d1 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-c mb-5 uppercase">Ready to study <span class="cyber-text-flow">smarter?</span></h2>
                <p class="rv rv-d2 text-sm sm:text-base text-c-25 leading-relaxed mb-10 max-w-lg mx-auto">Transform your learning with AI-powered summaries, personalized study planners, and smart notes — all free to start.</p>
                <div class="rv rv-d3 flex flex-wrap justify-center gap-3">
                    <button onclick="openAuth('choice')" class="btn-cy text-xs font-bold px-8 py-4 rounded-lg inline-flex items-center gap-2.5 uppercase tracking-wider cursor-pointer">Start Free Trial<i class="fa-solid fa-arrow-right text-[10px]"></i></button>
                </div>
                <p class="rv rv-d4 text-[10px] text-c-15 mt-6 font-mono uppercase tracking-wider">No credit card required · Free forever plan · Cancel anytime</p>
            </div>
        </div></div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!-- FOOTER                                              -->
<!-- ═══════════════════════════════════════════════════ -->
<footer class="relative z-10 border-t border-cy/5">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-12">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4"><div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cy to-cyan-700 flex items-center justify-center shadow-lg shadow-cy/20"><i class="fa-solid fa-brain text-n text-xs"></i></div><span class="text-base font-bold tracking-tight text-c">SHEE<span class="cyber-text">LEARN</span></span></div>
                <p class="text-xs text-c-15 leading-relaxed mb-4 max-w-xs">AI-powered learning platform designed to help students study smarter, not harder.</p>
                <div class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-gn pulse-dot"></div><span class="text-[10px] text-gn/60 font-mono uppercase tracking-wider">All systems operational</span></div>
            </div>
            <div>
                <h4 class="text-[10px] font-bold text-c-40 uppercase tracking-[.2em] font-mono mb-4">Product</h4>
                <ul class="space-y-4">
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Features</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Summaries, flashcards, quizzes, and AI chat make study sessions faster and more effective.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Pricing</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Flexible plans for learners of all levels, with a free tier to get started instantly.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>API</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Integrate SHEELEARN with your tools to automate document processing and study workflows.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Changelog</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>See the latest product updates, new features, and improvements rolling out to users.</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div>
                <h4 class="text-[10px] font-bold text-c-40 uppercase tracking-[.2em] font-mono mb-4">Company</h4>
                <ul class="space-y-4">
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>About</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Learn about our mission to make studying simpler, smarter, and more engaging.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Blog</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Read study tips, product stories, and best practices from the SHEELEARN team.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Careers</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Join our growing team and help build tools that empower learners worldwide.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Contact</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Get in touch with support, partnerships, or press inquiries.</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div>
                <h4 class="text-[10px] font-bold text-c-40 uppercase tracking-[.2em] font-mono mb-4">Legal</h4>
                <ul class="space-y-4">
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Privacy</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Review our privacy practices and how we protect your data.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Terms</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>See the terms of service that govern your use of SHEELEARN.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>Security</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Learn how we keep your study materials and personal information safe.</p>
                        </div>
                    </li>
                    <li class="space-y-2">
                        <button type="button" class="footer-item-toggle w-full flex items-center justify-between text-left text-xs text-c-25 hover:text-cy transition-colors"><span>GDPR</span><span class="footer-item-icon"></span></button>
                        <div class="footer-item-content text-xs text-c-25 overflow-hidden max-h-0 transition-all duration-500 ease-in-out pl-3">
                            <p>Read about our GDPR compliance and user rights in Europe.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-cy/5 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-[10px] text-c-15 font-mono">© {{ now()->year }} SHEELEARN. All rights reserved.</p>
            <div class="flex items-center gap-4">
                <a href="#" class="text-c-15 hover:text-cy transition-colors"><i class="fa-brands fa-google text-sm"></i></a>
            </div>
        </div>
    </div>
</footer>


<!-- ═══════════════════════════════════════════════════ -->
<!-- JAVASCRIPT                                          -->
<!-- ═══════════════════════════════════════════════════ -->
<script>
/* ── Scroll Progress ── */
const scrollProgress = document.getElementById('scrollProgress');
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    scrollProgress.style.width = ((scrollTop / docHeight) * 100) + '%';
});

/* ── Cursor Spotlight ── */
const spotlight = document.getElementById('spotlight');
document.addEventListener('mousemove', (e) => {
    spotlight.style.background = `radial-gradient(circle 600px at ${e.clientX}px ${e.clientY}px, rgba(34,211,238,0.03), transparent 60%)`;
});

/* ── Navbar Scroll ── */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('nav-scrolled', window.scrollY > 50);
});

/* ── Mobile Menu ── */
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
const menuIcon = document.getElementById('menuIcon');
function closeMobileMenu() {
    mobileMenu.classList.add('hidden');
    menuIcon.classList.add('fa-bars');
    menuIcon.classList.remove('fa-xmark');
}
mobileMenuBtn.addEventListener('click', () => {
    const isHidden = mobileMenu.classList.contains('hidden');
    mobileMenu.classList.toggle('hidden');
    menuIcon.classList.toggle('fa-bars', !isHidden);
    menuIcon.classList.toggle('fa-xmark', isHidden);
});
mobileMenu.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', closeMobileMenu);
});

/* ═══════════════════════════════════════════════════ */
/* ── AUTH MODAL SYSTEM ──                              */
/* ═══════════════════════════════════════════════════ */
const authOverlay = document.getElementById('authOverlay');
const authModal = document.getElementById('authModal');
const authChoicePanel = document.getElementById('authChoicePanel');
const authGmailPanel = document.getElementById('authGmailPanel');
const authLoginPanel = document.getElementById('authLoginPanel');
const authForgotPanel = document.getElementById('authForgotPanel');
let lastFocusedElement = null;
let isAuthOpen = false;
let isSwitching = false;

function openAuth(panel) {
    if (isAuthOpen) {
        switchPanel(panel);
        return;
    }

    isAuthOpen = true;
    lastFocusedElement = document.activeElement;

    const panels = [authChoicePanel, authGmailPanel, authLoginPanel, authForgotPanel].filter(Boolean);
    panels.forEach(panelEl => {
        panelEl.classList.remove('active-panel', 'exiting');
        panelEl.style.display = 'none';
    });

    let targetPanel;
    switch (panel) {
        case 'gmail': targetPanel = authGmailPanel; break;
        case 'login': targetPanel = authLoginPanel; break;
        case 'forgot': targetPanel = authForgotPanel; break;
        default: targetPanel = authChoicePanel;
    }

    targetPanel.style.display = 'flex';
    void targetPanel.offsetWidth;
    targetPanel.classList.add('active-panel');

    authOverlay.setAttribute('aria-hidden', 'false');
    authModal.setAttribute('aria-labelledby', targetPanel.querySelector('h2')?.id || 'authTitle');

    document.body.style.overflow = 'hidden';
    authOverlay.classList.remove('closing');
    authOverlay.classList.add('active');

    setTimeout(() => {
        const firstInput = targetPanel.querySelector('input');
        if (firstInput) firstInput.focus();
    }, 400);
}

function closeAuth() {
    if (!isAuthOpen || isSwitching) return;
    isAuthOpen = false;

    authOverlay.classList.add('closing');
    authOverlay.classList.remove('active');
    authOverlay.setAttribute('aria-hidden', 'true');

    setTimeout(() => {
        authOverlay.classList.remove('closing');
        document.body.style.overflow = '';
        [authChoicePanel, authGmailPanel, authLoginPanel, authForgotPanel].filter(Boolean).forEach(panelEl => {
            panelEl.classList.remove('active-panel', 'exiting');
            panelEl.style.display = 'none';
        });

        authModal.querySelectorAll('input[type="text"],input[type="email"],input[type="password"],input[type="tel"],input[type="hidden"]').forEach(i => i.value = '');
        authModal.querySelectorAll('input[type="checkbox"]').forEach(c => { c.checked = false; });

        if (lastFocusedElement) {
            lastFocusedElement.focus();
            lastFocusedElement = null;
        }
    }, 400);
}

function switchPanel(target) {
    if (isSwitching) return;
    isSwitching = true;

    const currentPanel = [authChoicePanel, authGmailPanel, authLoginPanel, authForgotPanel].find(panel => panel.style.display === 'flex');
    const nextPanelMap = {
        choice: authChoicePanel,
        gmail: authGmailPanel,
        login: authLoginPanel,
        forgot: authForgotPanel,
    };
    const nextPanel = nextPanelMap[target] || authChoicePanel;

    if (currentPanel) {
        currentPanel.classList.remove('active-panel');
        currentPanel.classList.add('exiting');
    }

    setTimeout(() => {
        [authChoicePanel, authGmailPanel, authLoginPanel, authForgotPanel].filter(Boolean).forEach(panelEl => {
            panelEl.classList.remove('active-panel', 'exiting');
            panelEl.style.display = 'none';
        });

        nextPanel.style.display = 'flex';
        void nextPanel.offsetWidth;
        nextPanel.classList.add('active-panel');

        const firstInput = nextPanel.querySelector('input');
        if (firstInput) firstInput.focus();

        isSwitching = false;
    }, 200);
}

function handleOAuthGoogle() {
    window.location.href = '/auth/google';
}

function showGoogleFlow(email, name) {
    document.getElementById('gmailPreviewEmail').textContent = email || 'Google account selected';
    document.getElementById('gmailPanelSubtitle').textContent = 'We’ve prepared your Google account for a secure verification step.';
    const startForm = document.getElementById('gmailStartForm');
    const verifyForm = document.getElementById('gmailVerifyForm');
    const profileForm = document.getElementById('gmailProfileForm');
    startForm.style.display = 'block';
    verifyForm.style.display = 'none';
    profileForm.style.display = 'none';
    openAuth('gmail');
}

function submitGoogleOtpStart(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/google/otp/send', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            form.style.display = 'none';
            const verifyForm = document.getElementById('gmailVerifyForm');
            verifyForm.style.display = 'block';
            showPanelMessage(verifyForm, result.message);
            startResendCooldown();
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to send verification code.'))
    .finally(() => toggleSubmitState(form, false));
}

let resendCooldownTimer = null;
let resendCooldownRemaining = 0;

function startResendCooldown() {
    resendCooldownRemaining = 60;
    const resendBtn = document.getElementById('gmailResendBtn');
    if (resendBtn) {
        resendBtn.disabled = true;
        resendBtn.textContent = 'Resend code (60s)';
    }

    if (resendCooldownTimer) clearInterval(resendCooldownTimer);
    
    resendCooldownTimer = setInterval(() => {
        resendCooldownRemaining--;
        if (resendBtn) {
            resendBtn.textContent = `Resend code (${resendCooldownRemaining}s)`;
        }
        if (resendCooldownRemaining <= 0) {
            clearInterval(resendCooldownTimer);
            if (resendBtn) {
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend code';
            }
        }
    }, 1000);
}

function resendGoogleOtp() {
    if (resendCooldownRemaining > 0) return;
    
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const verifyForm = document.getElementById('gmailVerifyForm');
    
    fetch('/api/auth/google/otp/send', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showPanelMessage(verifyForm, result.message);
            document.getElementById('gmailOtp').value = '';
            startResendCooldown();
        } else {
            showPanelError(verifyForm, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(verifyForm, 'Unable to resend verification code.'));
}

function submitGoogleOtpVerify(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const otp = form.otp.value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/google/otp/verify', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ otp }),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            form.style.display = 'none';
            const profileForm = document.getElementById('gmailProfileForm');
            profileForm.style.display = 'block';
            showPanelMessage(profileForm, result.message);
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to verify code.'))
    .finally(() => toggleSubmitState(form, false));
}

function submitGoogleProfile(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/google/profile/complete', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = result.redirect || '/dashboard';
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to finish creating your account.'))
    .finally(() => toggleSubmitState(form, false));
}

// Firebase Phone Auth integration
function initFirebaseAuth() {
    try {
        // Use the provided SHEELEARN Firebase web config
        const firebaseConfig = {
            apiKey: "AIzaSyCZGvWgsXIy0qL--L0ZGLp_n73_upM1Zwo",
            authDomain: "sheelearn-a81d8.firebaseapp.com",
            projectId: "sheelearn-a81d8",
            storageBucket: "sheelearn-a81d8.appspot.com",
            messagingSenderId: "695675996587",
            appId: "1:695675996587:web:dc5d998409831768b4aaee",
            measurementId: "G-PQR15VWFNX"
        };

        firebase.initializeApp(firebaseConfig);
        window.firebaseAuth = firebase.auth();
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {size: 'invisible'});
        window.recaptchaVerifier.render().catch(() => {});
    } catch (e) {
        console.warn('Firebase init failed', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initFirebaseAuth();
    const email = @json(session('google_registration.email') ?? '');
    const name = @json(session('google_registration.name') ?? '');
    if (email) {
        showGoogleFlow(email, name || '');
    }
});

function submitGmailStart(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const email = form.email.value.trim();
    const name = form.name ? form.name.value.trim() : '';
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/gmail/start', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ email, name }),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('verifyGmailHidden').value = email;
            form.style.display = 'none';
            const verifyForm = document.getElementById('gmailVerifyForm');
            verifyForm.style.display = 'block';
            let message = result.message;
            if (result.debug_code) {
                message += ' Use code ' + result.debug_code + ' to verify your email.';
            }
            showPanelMessage(verifyForm, message);
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to send verification code.'))
    .finally(() => toggleSubmitState(form, false));
}

function submitGmailVerify(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const email = form.email.value.trim();
    const otp = form.otp.value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/gmail/verify', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ email, otp }),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('gmailProfileName').value = document.getElementById('gmailName').value.trim();
            form.style.display = 'none';
            const profileForm = document.getElementById('gmailProfileForm');
            profileForm.style.display = 'block';
            showPanelMessage(profileForm, result.message);
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to verify code.'))
    .finally(() => toggleSubmitState(form, false));
}

function submitGmailProfile(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const email = document.getElementById('verifyGmailHidden').value.trim();
    const name = form.name.value.trim();
    const goal = form.goal.value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/gmail/complete', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ email, name, goal }),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = result.redirect || '/dashboard';
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to finish creating your account.'))
    .finally(() => toggleSubmitState(form, false));
}

function submitPhoneRequest(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const phoneRaw = form.phone.value.trim();
    let phone = phoneRaw.replace(/[^0-9+]/g, '');
    if (phone && phone[0] !== '+') phone = '+' + phone;

    if (!window.firebaseAuth || !window.recaptchaVerifier) {
        showPanelError(form, 'Phone authentication is not configured on the server.');
        toggleSubmitState(form, false);
        return;
    }

    window.firebaseAuth.signInWithPhoneNumber(phone, window.recaptchaVerifier)
        .then(confirmationResult => {
            window.__firebaseConfirmation = confirmationResult;
            document.getElementById('verifyPhoneHidden').value = phone;
            form.style.display = 'none';
            const verifyForm = document.getElementById('phoneVerifyForm');
            verifyForm.style.display = 'block';
            showPanelMessage(verifyForm, 'Verification code sent.');
        })
        .catch(err => {
                console.error('Firebase signInWithPhoneNumber error', err);
                // Provide clearer guidance for common Firebase phone errors
                const code = err && err.code ? err.code : null;
                if (code === 'auth/operation-not-allowed') {
                    showPanelError(form, 'Phone authentication is not enabled for this Firebase project. Enable Phone provider in Firebase Console (Auth → Sign-in method).');
                } else if (code === 'auth/invalid-phone-number') {
                    showPanelError(form, 'Invalid phone number format. Use international E.164 format, e.g. +15551234567.');
                } else if (code === 'auth/too-many-requests') {
                    showPanelError(form, 'Too many requests. Please wait and try again later.');
                } else if (code === 'auth/quota-exceeded') {
                    showPanelError(form, 'SMS quota exceeded for this project. Check Firebase Console usage/billing.');
                } else if (code && code.startsWith('auth/')) {
                    showPanelError(form, 'Unable to send verification code: ' + (err.message || code));
                } else {
                    showPanelError(form, 'Unable to send verification code. ' + (err.message || ''));
                }
            })
        .finally(() => toggleSubmitState(form, false));
}

function submitPhoneOtp(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const otp = form.otp.value.trim();

    if (!window.__firebaseConfirmation) {
        showPanelError(form, 'No active verification session. Please request a new code.');
        toggleSubmitState(form, false);
        return;
    }

    window.__firebaseConfirmation.confirm(otp)
        .then(async (result) => {
            // Get ID token and send to backend for verification and user login
            const idToken = await result.user.getIdToken();
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/api/auth/phone/firebase-verify', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ id_token: idToken }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || '/dashboard';
                } else {
                    showPanelError(form, data.message || 'Verification failed.');
                }
            })
            .catch(() => showPanelError(form, 'Unable to verify code with server.'))
            .finally(() => toggleSubmitState(form, false));
        })
        .catch(err => {
            console.error('Confirmation error', err);
            showPanelError(form, 'Invalid verification code.');
            toggleSubmitState(form, false);
        });
}

function submitEmailRequest(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const email = form.email.value.trim();
    const name = form.name ? form.name.value.trim() : '';
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/email-otp/request', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ email, name }),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('verifyEmailHidden').value = email;
            form.style.display = 'none';
            const verifyForm = document.getElementById('emailVerifyForm');
            verifyForm.style.display = 'block';
            let message = result.message;
            if (result.debug_code) {
                message += ' Use code ' + result.debug_code + ' to verify your email while email delivery is not configured.';
            }
            showPanelMessage(verifyForm, message);
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to send verification code.'))
    .finally(() => toggleSubmitState(form, false));
}

function submitEmailOtp(event) {
    event.preventDefault();
    const form = event.target;
    toggleSubmitState(form, true);
    hidePanelErrors(form);

    const email = form.email.value.trim();
    const otp = form.otp.value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/auth/email-otp/verify', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ email, otp }),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = result.redirect || '/dashboard';
        } else {
            showPanelError(form, result.message, result.errors);
        }
    })
    .catch(() => showPanelError(form, 'Unable to verify code.'))
    .finally(() => toggleSubmitState(form, false));
}

function toggleSubmitState(form, active) {
    const button = form.querySelector('button[type="submit"]');
    if (!button) return;
    if (active) {
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i>Processing...';
        button.disabled = true;
        button.style.opacity = '0.7';
    } else {
        button.textContent = button.dataset.originalText || 'Continue';
        button.disabled = false;
        button.style.opacity = '1';
    }
}

function showPanelError(form, message, errors = null) {
    hidePanelErrors(form);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'auth-error-msg bg-red-500/20 border border-red-500/40 text-red-200 px-4 py-3 rounded-lg mb-4 text-sm';
    errorDiv.textContent = message;
    form.prepend(errorDiv);
    if (errors) {
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                const fieldErrors = errors[field];
                const errorMsg = document.createElement('div');
                errorMsg.className = 'auth-error-msg text-red-400 text-xs mt-1';
                errorMsg.textContent = fieldErrors[0];
                input.parentElement.appendChild(errorMsg);
            }
        });
    }
}

function showPanelMessage(form, message) {
    hidePanelErrors(form);
    const infoDiv = document.createElement('div');
    infoDiv.className = 'auth-error-msg bg-emerald-500/15 border border-emerald-500/20 text-emerald-100 px-4 py-3 rounded-lg mb-4 text-sm';
    infoDiv.textContent = message;
    form.prepend(infoDiv);
}

function hidePanelErrors(form) {
    form.querySelectorAll('.auth-error-msg').forEach(el => el.remove());
}

// Resend handling
let _resendTimer = null;
function startResendCountdown(seconds = 60) {
    const btn = document.getElementById('resendPhoneBtn');
    const label = document.getElementById('resendCountdown');
    if (!btn || !label) return;
    btn.disabled = true;
    let remaining = seconds;
    label.textContent = `(${remaining}s)`;
    _resendTimer = setInterval(() => {
        remaining -= 1;
        label.textContent = `(${remaining}s)`;
        if (remaining <= 0) {
            clearInterval(_resendTimer);
            btn.disabled = false;
            label.textContent = '';
        }
    }, 1000);
}

function resendPhoneCode() {
    const phone = document.getElementById('verifyPhoneHidden').value;
    if (!phone) return showPanelError(document.getElementById('phoneRequestForm'), 'No phone to resend to.');
    if (!window.firebaseAuth || !window.recaptchaVerifier) return showPanelError(document.getElementById('phoneRequestForm'), 'Phone authentication not available.');
    try {
        window.firebaseAuth.signInWithPhoneNumber(phone, window.recaptchaVerifier)
            .then(confirmationResult => {
                window.__firebaseConfirmation = confirmationResult;
                showPanelMessage(document.getElementById('phoneVerifyForm'), 'Verification code resent.');
                startResendCountdown(60);
            })
            .catch(err => showPanelError(document.getElementById('phoneVerifyForm'), 'Unable to resend code: ' + (err.message || '')));
    } catch (e) {
        showPanelError(document.getElementById('phoneVerifyForm'), 'Unable to resend code.');
    }
}

let _resendEmailTimer = null;
function startEmailResendCountdown(seconds = 60) {
    const btn = document.getElementById('resendEmailBtn');
    const label = document.getElementById('resendEmailCountdown');
    if (!btn || !label) return;
    btn.disabled = true;
    let remaining = seconds;
    label.textContent = `(${remaining}s)`;
    _resendEmailTimer = setInterval(() => {
        remaining -= 1;
        label.textContent = `(${remaining}s)`;
        if (remaining <= 0) {
            clearInterval(_resendEmailTimer);
            btn.disabled = false;
            label.textContent = '';
        }
    }, 1000);
}

function resendEmailCode() {
    const email = document.getElementById('verifyEmailHidden').value;
    const name = document.getElementById('emailFullName') ? document.getElementById('emailFullName').value : '';
    if (!email) return showPanelError(document.getElementById('emailRequestForm'), 'No email to resend to.');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/email-otp/request', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ email, name }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showPanelMessage(document.getElementById('emailVerifyForm'), data.message || 'Verification code resent.');
            startEmailResendCountdown(60);
        } else {
            showPanelError(document.getElementById('emailVerifyForm'), data.message || 'Unable to resend.');
        }
    })
    .catch(() => showPanelError(document.getElementById('emailVerifyForm'), 'Unable to resend code.'));
}

/* ── Password visibility toggle ── */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

/* ── Form submit handler (with API integration) ── */
async function handleAuthSubmit(e, type) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i>Processing...';
    btn.disabled = true;
    btn.style.opacity = '0.7';
    hidePanelErrors(form);

    try {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const endpoint = type === 'register' ? '/api/auth/register' : '/api/auth/login';

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(data)
        });

        const responseText = await response.text();
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('Invalid JSON response:', responseText.substring(0, 200));
            showPanelError(form, 'Server returned an invalid response. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.style.opacity = '1';
            return;
        }

        if (result.success) {
            btn.innerHTML = '<i class="fa-solid fa-check text-xs mr-2"></i>Success!';
            btn.style.opacity = '1';
            if (result.user) {
                localStorage.setItem('user', JSON.stringify(result.user));
                localStorage.setItem('authenticated', 'true');
            }
            window.location.href = result.redirect || '/dashboard';
        } else {
            showPanelError(form, result.message, result.errors);
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    } catch (error) {
        console.error('Auth error:', error);
        showPanelError(form, 'An error occurred. Please try again.');
        btn.innerHTML = originalText;
        btn.disabled = false;
        btn.style.opacity = '1';
    }
}

/* ── Display form errors ── */
function showFormError(form, type, message, errors = null) {
    // Remove existing error messages
    form.querySelectorAll('.auth-error-msg').forEach(el => el.remove());

    // Show general error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'auth-error-msg bg-red-500/20 border border-red-500/40 text-red-200 px-4 py-3 rounded-lg mb-4 text-sm';
    errorDiv.textContent = message;
    form.insertBefore(errorDiv, form.querySelector('.space-y-4'));

    // Show field-specific errors
    if (errors) {
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                const fieldErrors = errors[field];
                const errorMsg = document.createElement('div');
                errorMsg.className = 'auth-error-msg text-red-400 text-xs mt-1';
                errorMsg.textContent = fieldErrors[0];
                input.parentElement.appendChild(errorMsg);
            }
        });
    }
}

/* ── Update UI after authentication ── */
function updateAuthUI(user) {
    // Update nav bar to show user is logged in
    const navButtons = document.querySelectorAll('[onclick*="openAuth"]');
    const navbar = document.getElementById('navbar');
    
    // Remove auth buttons
    navButtons.forEach(btn => btn.remove());
    
    // Add user info and logout button
    const userMenu = document.createElement('div');
    userMenu.className = 'flex items-center gap-2 ml-auto';
    userMenu.innerHTML = `
        <span class="text-xs text-c-25">Welcome, <span class="text-cy font-semibold">${user.name}</span></span>
        <button onclick="handleLogout()" class="text-xs font-medium text-red-400 hover:text-red-300 px-4 py-2 rounded-lg hover:bg-red-500/10 transition-all duration-200 cursor-pointer">Logout</button>
    `;
    navbar.appendChild(userMenu);
}

/* ── Hidden admin trigger on logo ── */
let logoClickCount = 0;
let logoClickTimer = null;
let logoTriggerActive = false;

function resetLogoTrigger() {
    logoClickCount = 0;
    if (logoClickTimer) {
        clearTimeout(logoClickTimer);
        logoClickTimer = null;
    }
}

function attachLogoSecretTrigger() {
    const trigger = document.getElementById('welcomeLogoTrigger');
    if (!trigger) return;

    const handleLogoInteraction = (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (logoClickTimer) {
            clearTimeout(logoClickTimer);
        }

        logoClickCount += 1;
        logoClickTimer = setTimeout(() => {
            resetLogoTrigger();
        }, 3000);

        if (logoClickCount >= 7) {
            resetLogoTrigger();
            window.location.href = '/admin/login';
        }
    };

    trigger.addEventListener('click', handleLogoInteraction);
    document.addEventListener('click', (event) => {
        if (!trigger.contains(event.target)) {
            resetLogoTrigger();
        }
    });
    window.addEventListener('beforeunload', resetLogoTrigger);
    window.addEventListener('pageshow', () => {
        resetLogoTrigger();
    });
}

/* ── Handle logout ── */
async function handleLogout() {
    if (!confirm('Are you sure you want to logout?')) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('/api/auth/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        // Clear user data from local storage
        localStorage.removeItem('user');
        localStorage.removeItem('authenticated');
        
        // Redirect to welcome page (the server redirect will be followed automatically)
        // Adding a small delay to ensure session is properly invalidated
        setTimeout(() => {
            window.location.href = '/';
        }, 200);
    } catch (error) {
        console.error('Logout error:', error);
        alert('Error logging out. Please try again.');
    }
}

/* ── Keyboard: Escape to close ── */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isAuthOpen) {
        e.preventDefault();
        closeAuth();
    }
});

/* ── Focus trap ── */
document.addEventListener('keydown', (e) => {
    if (!isAuthOpen || e.key !== 'Tab') return;

    const focusable = authModal.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );
    if (focusable.length === 0) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (e.shiftKey) {
        if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
        }
    } else {
        if (document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }
});

/* ── FAQ Toggle ── */
document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector('.faq-i');
        const isOpen = content.classList.contains('open');
        document.querySelectorAll('.faq-c').forEach(c => c.classList.remove('open'));
        document.querySelectorAll('.faq-i').forEach(i => i.classList.remove('rot'));
        if (!isOpen) { content.classList.add('open'); icon.classList.add('rot'); }
    });
});

/* ── Footer item toggle ── */
document.querySelectorAll('.footer-item-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector('.footer-item-icon');
        const isOpen = content.classList.contains('open');
        content.classList.toggle('open', !isOpen);
        icon.classList.toggle('rot', !isOpen);
    });
});

/* ── Reveal on Scroll ── */
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('on'); });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
document.querySelectorAll('.rv').forEach(el => revealObserver.observe(el));

/* ── 3D Tilt Cards ── */
document.querySelectorAll('.tilt-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const rotateX = ((y - rect.height / 2) / (rect.height / 2)) * -3;
        const rotateY = ((x - rect.width / 2) / (rect.width / 2)) * 3;
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px)`;
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
    });
});

/* ── Counter Animation ── */
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const target = parseFloat(el.dataset.count);
            const isDecimal = el.dataset.decimal === 'true';
            const divideBy = parseFloat(el.dataset.divide) || 1;
            const suffix = el.dataset.suffix || '';
            const duration = 2000;
            const startTime = performance.now();
            function update(now) {
                const p = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                const val = eased * target;
                if (divideBy > 1) el.textContent = (val / divideBy).toFixed(0) + suffix;
                else if (isDecimal) el.textContent = val.toFixed(1);
                else el.textContent = Math.floor(val).toLocaleString();
                if (p < 1) requestAnimationFrame(update);
            }
            requestAnimationFrame(update);
            counterObserver.unobserve(el);
        }
    });
}, { threshold: 0.5 });
document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

/* ── Smooth scroll ── */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

/* ── Forgot-password modal handlers ── */
let _forgotTimer = null; let _forgotRemaining = 0;
function startForgotCooldown(seconds = 60){
    const btn = document.getElementById('forgotResendBtn');
    const label = document.getElementById('forgotCountdown');
    if(!btn||!label) return; btn.disabled=true; _forgotRemaining=seconds; label.textContent=`Resend available in ${_forgotRemaining}s`;
    if(_forgotTimer) clearInterval(_forgotTimer);
    _forgotTimer=setInterval(()=>{ _forgotRemaining--; if(_forgotRemaining<=0){ clearInterval(_forgotTimer); btn.disabled=false; label.textContent='Resend Code'; } else { label.textContent=`Resend available in ${_forgotRemaining}s`; } },1000);
}

function submitForgotModal(e){
    e.preventDefault(); const form=e.target; hidePanelErrors(form); toggleSubmitState(form,true);
    const email=document.getElementById('forgotEmail').value.trim(); const token=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/request',{ method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token}, body: JSON.stringify({ email }) })
    .then(r=>r.json()).then(data=>{
        if(data.success){ showPanelMessage(form,data.message||'Verification code sent.'); document.getElementById('forgotTargetEmail').textContent=email; clearForgotOtpInputs(); form.style.display='none'; document.getElementById('forgotVerifyForm').style.display='block'; startForgotCooldown(60); setTimeout(()=>document.getElementById('forgotDigit1')?.focus(),50); }
        else { showPanelError(form,data.message||'No SHEELEARN account was found with this email address.'); }
    }).catch(()=> showPanelError(form,'Unable to send verification code.')).finally(()=> toggleSubmitState(form,false));
}

function resendForgotModal(){
    const email=document.getElementById('forgotTargetEmail').textContent || document.getElementById('forgotEmail').value.trim(); if(!email) return;
    const token=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/request',{ method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token}, body: JSON.stringify({ email }) })
    .then(r=>r.json()).then(data=>{ const form=document.getElementById('forgotVerifyForm'); if(data.success) showPanelMessage(form,data.message||'Verification code resent.'); else showPanelError(form,data.message||'Unable to resend.'); startForgotCooldown(60); })
    .catch(()=> showPanelError(document.getElementById('forgotVerifyForm'),'Unable to resend code.'));
}

function getForgotOtpValue(){
    const digits = [];
    for(let i=1;i<=6;i++){ const el=document.getElementById('forgotDigit'+i); if(!el || !/^[0-9]$/.test(el.value)) return null; digits.push(el.value); }
    return digits.join('');
}

function updateForgotVerifyButton(){
    const button = document.getElementById('forgotVerifyButton');
    const otp = getForgotOtpValue();
    if(button) button.disabled = otp === null;
}

function clearForgotOtpInputs(){
    for(let i=1;i<=6;i++){ const el=document.getElementById('forgotDigit'+i); if(el){ el.value=''; el.classList.remove('shake','otp-success'); } }
    updateForgotVerifyButton();
}

function markForgotOtpError(){
    for(let i=1;i<=6;i++){ const el=document.getElementById('forgotDigit'+i); if(el) el.classList.add('shake'); }
    setTimeout(()=>{ for(let i=1;i<=6;i++){ const el=document.getElementById('forgotDigit'+i); if(el) el?.classList.remove('shake'); } },350);
}

function markForgotOtpSuccess(){
    for(let i=1;i<=6;i++){ const el=document.getElementById('forgotDigit'+i); if(el) el.classList.add('otp-success'); }
    setTimeout(()=>{ for(let i=1;i<=6;i++){ const el=document.getElementById('forgotDigit'+i); if(el) el?.classList.remove('otp-success'); } },800);
}

function submitForgotVerify(e){
    e.preventDefault(); const form=e.target; toggleSubmitState(form,true); hidePanelErrors(form);
    const email=document.getElementById('forgotTargetEmail').textContent;
    const otp=getForgotOtpValue() || '';
    const token=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/verify',{ method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token}, body: JSON.stringify({ email, otp }) })
    .then(r=>r.json()).then(data=>{
        if(data.success){ showPanelMessage(form,data.message||'Email verified.'); markForgotOtpSuccess(); form.style.display='none'; document.getElementById('forgotResetForm').style.display='block'; }
        else { showPanelError(form,data.message||'Verification failed.'); markForgotOtpError(); }
    }).catch(()=>{ showPanelError(form,'Unable to verify code.'); markForgotOtpError(); })
    .finally(()=> toggleSubmitState(form,false));
}

function setupForgotOtpInputs(){
    const inputs=[];
    for(let i=1;i<=6;i++){ inputs.push(document.getElementById('forgotDigit'+i)); }
    inputs.forEach((input,index)=>{
        if(!input) return;
        input.addEventListener('input', (event)=>{
            const value = event.target.value.replace(/[^0-9]/g,'');
            event.target.value = value ? value.charAt(value.length-1) : '';
            if(value){ const next = inputs[index+1]; if(next) next.focus(); }
            updateForgotVerifyButton();
        });
        input.addEventListener('keydown', (event)=>{
            if(event.key==='Backspace'){
                if(!event.target.value){ const prev = inputs[index-1]; if(prev){ prev.focus(); prev.value=''; updateForgotVerifyButton(); event.preventDefault(); } }
            }
            if(event.key==='ArrowLeft'){ const prev = inputs[index-1]; if(prev){ prev.focus(); event.preventDefault(); } }
            if(event.key==='ArrowRight'){ const next = inputs[index+1]; if(next){ next.focus(); event.preventDefault(); } }
        });
        input.addEventListener('paste', (event)=>{
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text') || '';
            const digits = paste.replace(/\D/g,'').slice(0,6).split('');
            if(digits.length===0) return;
            digits.forEach((digit, idx)=>{ if(inputs[idx]) inputs[idx].value=digit; });
            const nextIndex = Math.min(digits.length, inputs.length)-1;
            const next = inputs[nextIndex+1] || inputs[nextIndex];
            next?.focus();
            updateForgotVerifyButton();
        });
    });
}

function submitForgotReset(e){
    e.preventDefault(); const form=e.target; toggleSubmitState(form,true); hidePanelErrors(form);
    const password=document.getElementById('forgotNewPassword').value.trim(); const password_confirmation=document.getElementById('forgotConfirmPassword').value.trim(); const token=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/complete',{ method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token}, body: JSON.stringify({ password, password_confirmation }) })
    .then(r=>r.json()).then(data=>{ if(data.success){ showPanelMessage(form,data.message||'Password updated.'); setTimeout(()=>{ switchPanel('login'); setTimeout(()=>{ authLoginPanel.querySelector('input')?.focus(); }, 120); },900); } else { showPanelError(form,data.message||'Unable to update password.'); } }).catch(()=> showPanelError(form,'Unable to update password.')).finally(()=> toggleSubmitState(form,false));
}

// Password strength for modal
document.addEventListener('input', function(e){ if(e.target && e.target.id==='forgotNewPassword'){ const s=strengthScore(e.target.value); const labels=['Very weak','Weak','Fair','Good','Strong','Very strong']; document.getElementById('forgotPwStrength').textContent = 'Strength: '+labels[s]; } });

// OTP input setup
window.addEventListener('DOMContentLoaded', function(){ setupForgotOtpInputs(); attachLogoSecretTrigger(); });

/* ═══════════════════════════════════════════════════ */
/* ── DYNAMIC WELCOME STATISTICS LOADING ──            */
/* ═══════════════════════════════════════════════════ */

window.__welcomeStats = @json($welcomeStats);

// Load and display welcome page statistics
async function loadWelcomeStatistics() {
    if (window.__welcomeStats) {
        applyWelcomeStats(window.__welcomeStats);
    }

    try {
        const response = await fetch('/api/welcome/statistics');
        const data = await response.json();

        console.log('Welcome Statistics API Response:', data);

        // Update stats section
        updateStatistic('total_users', data.total_users);
        updateStatistic('total_documents', data.total_documents);
        updateStatistic('ai_accuracy', data.ai_accuracy + '%');
        updateStatistic('average_rating', data.average_rating);

        // Update hero section with user count and avatars
        updateHeroSection(data);
    } catch (error) {
        console.error('Failed to load welcome statistics:', error);
        console.error('Error details:', error.message);
        // Fallback values are already in HTML, so we don't need to do anything
    }
}

function updateStatistic(statName, value) {
    const element = document.querySelector(`[data-stat="${statName}"]`);
    if (element) {
        element.textContent = value;
    }
}

function applyWelcomeStats(data) {
    if (!data) return;
    updateStatistic('total_users', data.total_users || '0');
    updateStatistic('total_documents', data.total_documents || '0');
    updateStatistic('ai_accuracy', `${data.ai_accuracy ?? 0}%`);
    updateStatistic('average_rating', data.average_rating ?? 0);
    updateHeroSection(data);
}

function updateHeroSection(data) {
    // Update user count badge
    const userCountBadge = document.getElementById('userCountBadge');
    if (userCountBadge && data.total_users_raw > 0) {
        const remaining = Math.max(0, data.total_users_raw - 3);
        userCountBadge.textContent = (remaining > 0 ? '+' + formatNumberShort(remaining) : '');
    }

    // Update user trust text
    const userText = document.getElementById('heroUserText');
    if (userText && data.total_users_raw > 0) {
        userText.textContent = `Trusted by ${data.total_users} users`;
    }

    // Update featured user avatars
    const avatarsContainer = document.getElementById('heroAvatars');
    if (avatarsContainer) {
        // Remove all existing avatar images and placeholders except the count badge
        [...avatarsContainer.children].forEach(child => {
            if (child !== userCountBadge) {
                child.remove();
            }
        });

        // Add featured user avatars
        if (Array.isArray(data.featured_users) && data.featured_users.length > 0) {
            data.featured_users.slice(0, 3).forEach((avatar, index) => {
                const img = document.createElement('img');
                img.src = avatar;
                img.alt = `User ${index + 1}`;
                img.className = 'w-8 h-8 rounded-lg border-2 border-n object-cover';
                avatarsContainer.insertBefore(img, userCountBadge);
            });
        }
    }
}

function formatNumberShort(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(0) + 'K';
    }
    return num.toString();
}

// Load statistics when page is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadWelcomeStatistics);
} else {
    loadWelcomeStatistics();
}
</script>

</body>
</html>