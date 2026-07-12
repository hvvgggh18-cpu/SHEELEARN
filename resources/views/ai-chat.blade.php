@extends('layouts.dashboard-layout')

@section('title', 'AI Chat')

@section('styles')
<style>
    body.chat-active{overflow-x:hidden!important}
    .chat-active .main-content{overflow:visible}

    .chat-wrap{margin:0;height:calc(100vh - 65px);display:flex;overflow:hidden;border-radius:0;min-height:0;width:100%;max-height:calc(100vh - 65px);position:relative;padding-right:280px}
    .chat-main{flex:1;display:flex;flex-direction:column;min-height:0;overflow:visible}
    #chatMsgContainer{flex:1;min-height:0;overflow-y:auto}
    #chatFooter{position:sticky;bottom:0;left:0;right:0;z-index:10}
    @media(min-width:1024px){.chat-wrap{margin:0}}
    .chat-sidebar{width:280px;position:fixed;top:65px;bottom:0;right:0;z-index:50;border-left:1px solid var(--border);background:var(--surface-2);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);display:flex;flex-direction:column;overflow:hidden;transition:transform .35s cubic-bezier(.16,1,.3,1)}
    .chat-new-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 12px;border-radius:12px;font-size:12px;font-weight:600;color:var(--accent);background:transparent;border:1.5px solid var(--accent);cursor:pointer;transition:all .2s ease}
    .chat-new-btn:hover{background:var(--accent);color:#020617;box-shadow:0 0 20px rgba(34,211,238,0.3)}
    .chat-new-btn:active{transform:scale(0.97)}
    .chat-search{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;background:transparent;border:1px solid rgba(34,211,238,0.2);transition:all .2s ease}
    .chat-search:focus-within{border-color:var(--accent);box-shadow:0 0 12px rgba(34,211,238,0.15)}
    .chat-search input{background:transparent;font-size:11px;color:var(--text-muted);outline:none;width:100%;font-weight:500}
    .chat-search input::placeholder{color:var(--text-dim)}

    .qmode-card{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;background:transparent;border:none;cursor:pointer;transition:all .2s ease;width:100%;text-align:left}
    .qmode-card:hover{background:rgba(34,211,238,0.08);transform:translateX(2px)}
    .qmode-card:active{transform:translateX(0)}
    .qmode-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .2s ease}
    .qmode-card:hover .qmode-icon{transform:scale(1.08)}
    .qmode-icon i{font-size:13px}
    .qmode-card p{font-size:12px;font-weight:500;color:var(--text-muted);transition:color .2s ease}
    .qmode-card:hover p{color:#e2e8f0}

    .conv-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;border:1px solid transparent;cursor:pointer;transition:all .2s ease;width:100%;text-align:left;background:transparent;position:relative}
    .conv-item:hover{background:rgba(34,211,238,0.08)}
    .conv-item.active{background:rgba(34,211,238,0.12);border-color:rgba(34,211,238,0.2)}
    .conv-title{font-size:11px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1}
    .conv-item.active .conv-title{color:#e2e8f0}
    .conv-item:not(.active) .conv-title{color:var(--text-muted)}
    .conv-item-menu-toggle{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:transparent;border:none;color:var(--text-dim);cursor:pointer;transition:background .15s ease,color .15s ease;flex-shrink:0}
    .conv-item-menu-toggle:hover{background:var(--surface-3);color:var(--text-muted)}
    .conv-item-menu{position:absolute;top:100%;right:8px;min-width:140px;padding:6px 0;margin-top:8px;border-radius:12px;background:var(--surface);backdrop-filter:blur(18px);border:1px solid var(--border);box-shadow:0 16px 42px rgba(0,0,0,0.08);opacity:0;visibility:hidden;transform:translateY(-5px);transition:all .18s ease;z-index:20}
    .conv-item.menu-open .conv-item-menu{opacity:1;visibility:visible;transform:translateY(0)}
    .conv-item-action{display:flex;align-items:center;width:100%;padding:10px 12px;font-size:11px;color:var(--text-muted);background:transparent;border:none;text-align:left;cursor:pointer;transition:background .15s ease,color .15s ease}
    .conv-item-action:hover{background:var(--surface-3);color:var(--text-muted)}
    .conv-item-action[data-action="delete"]{color:#f87171}
    .conv-item-action[data-action="archive"]{color:#fbbf24}
    .conv-item-action[data-action="pin"]{color:var(--accent)}
    .conv-item-action[data-action="rename"]{color:var(--text-muted)}
    .conv-item i{font-size:10px;flex-shrink:0}
    .history-section{display:flex;flex-direction:column;gap:6px}
    .history-section.hidden{display:none}
    .history-section:first-of-type{margin-top:0}
    .hist-section-label{font-size:9px;font-weight:700;color:rgba(226,232,240,0.35);text-transform:uppercase;letter-spacing:.16em;font-family:'JetBrains Mono',monospace;padding:0 12px;margin-top:8px;margin-bottom:6px}
    .conv-item.active i{color:var(--accent)}
    .conv-item:not(.active) i{color:var(--text-dim)}
    .hist-label{font-size:9px;font-weight:700;color:rgba(226,232,240,0.4);text-transform:uppercase;letter-spacing:.16em;font-family:'JetBrains Mono',monospace;padding:16px 12px 10px 12px;border-top:1px solid rgba(34,211,238,0.1);margin-top:8px}
    .hist-clear{font-size:10px;color:var(--accent);background:none;border:none;cursor:pointer;transition:all .2s ease;padding:0;opacity:0.7}
    .hist-clear:hover{color:var(--accent);opacity:1}

    .chat-main{flex:1;display:flex;flex-direction:column;min-height:0;overflow:hidden;position:relative;background:transparent}
    .chat-topbar{display:flex;align-items:center;gap:10px;padding:8px 16px;border-bottom:1px solid var(--border);flex-shrink:0;flex-wrap:wrap}
    .mode-chip{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:5px 12px 5px 8px;border-radius:8px;font-size:11px;font-weight:500;color:var(--text-dim);background:transparent;border:1px solid transparent;cursor:pointer;transition:all .15s ease;white-space:nowrap}
    .mode-chip:hover{background:var(--surface-3);border-color:var(--border);color:var(--text-muted)}
    .mode-chip.active{background:var(--accent-dim);border-color:var(--accent-border);color:var(--accent)}
    .chip-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}
    .mode-chip.active .chip-dot{background:var(--accent);box-shadow:0 0 6px rgba(34,211,238,0.4)}
    .mode-chip:not(.active) .chip-dot{background:var(--text-dim)}


    .chat-welcome{display:none;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:24px}
    .chat-welcome.visible{display:flex}
    .wfade{animation:wUp .5s cubic-bezier(.16,1,.3,1) forwards;opacity:0}
    @keyframes wUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
    .ai-avatar-lg{width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,rgba(34,211,238,0.2),rgba(129,140,248,0.15),rgba(52,211,153,0.1));border:1px solid rgba(34,211,238,0.15);display:flex;align-items:center;justify-content:center;position:relative}
    .ai-avatar-lg::after{content:'';position:absolute;inset:-4px;border-radius:20px;border:1.5px solid rgba(34,211,238,0.2);animation:aPulse 2.5s ease-out infinite}
    @keyframes aPulse{0%{transform:scale(1);opacity:1}100%{transform:scale(1.3);opacity:0}}
    .ai-avatar-lg i{font-size:20px;color:#22d3ee}
    .suggest-pill{padding:5px 12px;border-radius:99px;font-size:10.5px;font-weight:500;color:var(--text-dim);background:var(--surface-3);border:1px solid var(--border);cursor:pointer;transition:all .18s ease;white-space:nowrap}
    .suggest-pill:hover{border-color:var(--accent-border);color:var(--text-muted);background:var(--accent-dim)}

    .chat-msg{display:flex;gap:12px;animation:mIn .4s cubic-bezier(.16,1,.3,1) forwards;opacity:0}
    .chat-msg-user{justify-content:flex-end}
    @keyframes mIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    .chat-bubble-user{background:linear-gradient(135deg,#22d3ee,#0891b2);color:#020617;border-radius:16px 16px 4px 16px;padding:11px 16px;max-width:75%}
    .chat-bubble-user p{font-size:13px;line-height:1.625;font-weight:500}
    .ai-avatar-sm{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,rgba(34,211,238,0.2),rgba(129,140,248,0.15));border:1px solid rgba(34,211,238,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px}
    .ai-avatar-sm i{font-size:10px;color:#22d3ee}
    .chat-bubble-ai{background:var(--surface-2);backdrop-filter:blur(16px);border:1px solid var(--accent-border);border-radius:16px 16px 16px 4px;padding:11px 16px;max-width:85%}
    .chat-bubble-ai .msg-text{font-size:13px;line-height:1.7;color:var(--text-muted);white-space:pre-wrap;word-break:break-word;}
    .chat-bubble-ai strong{color:#e2e8f0;font-weight:600}
    .chat-bubble-ai code{padding:2px 6px;border-radius:6px;background:var(--surface-3);color:var(--accent);font-size:12px;font-family:'JetBrains Mono',monospace;white-space:pre-wrap;}
    .chat-bubble-ai .math-expression{color:#93c5fd;font-family:'JetBrains Mono',monospace;}
    .chat-msg-footer{display:flex;align-items:center;gap:8px;margin-top:5px;margin-left:2px}
    .chat-msg-time{font-size:9px;color:var(--text-dim);font-family:'JetBrains Mono',monospace}
    .chat-msg-actions{display:flex;align-items:center;gap:2px;margin-left:8px}
    .chat-msg-actions button{width:22px;height:22px;border-radius:5px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);transition:all .15s ease;background:transparent;border:none;cursor:pointer}
    .chat-msg-actions button:hover{color:var(--text-muted);background:var(--surface-3)}
    .chat-msg-actions button i{font-size:9px}
    .chat-avatar-user{width:30px;height:30px;border-radius:8px;object-fit:cover;border:1px solid var(--border);flex-shrink:0;margin-top:2px}

    /* File attachment badge inside user bubble */
    .chat-bubble-user .file-attach-badge{display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:6px 10px;border-radius:8px;background:var(--surface-3);font-size:11px;font-weight:500}
    .chat-bubble-user .file-attach-badge i{font-size:10px;opacity:0.8}

    .typing-dot{width:7px;height:7px;border-radius:50%;background:rgba(34,211,238,0.5);animation:tB 1.2s ease-in-out infinite}
    .typing-dot:nth-child(2){animation-delay:.15s}
    .typing-dot:nth-child(3){animation-delay:.3s}
    @keyframes tB{0%,60%,100%{transform:translateY(0);opacity:.4}30%{transform:translateY(-5px);opacity:1}}

    .chat-input-wrap{background:var(--surface-2);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all .3s ease}
    .chat-input-wrap:focus-within{border-color:var(--accent-border);box-shadow:0 0 0 3px rgba(34,211,238,0.05),0 0 16px rgba(34,211,238,0.03)}
    .chat-input-wrap textarea{width:100%;background:transparent;font-size:13px;color:var(--text-muted);outline:none;resize:none;line-height:1.625;max-height:120px;font-family:'Inter',system-ui,sans-serif}
    .chat-input-wrap textarea::placeholder{color:var(--text-dim)}
    .input-tool{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);transition:all .15s ease;background:transparent;border:none;cursor:pointer}
    .input-tool:hover{color:var(--accent);background:var(--accent-dim)}
    .input-tool i{font-size:10px}
    .chat-send-btn{width:30px;height:30px;border-radius:10px;background:#22d3ee;display:flex;align-items:center;justify-content:center;color:#020617;border:none;cursor:pointer;transition:all .2s ease}
    .chat-send-btn:hover{background:linear-gradient(135deg,#22d3ee,#06b6d4);transform:scale(1.05);box-shadow:0 0 16px rgba(34,211,238,0.2)}
    .chat-send-btn:active{transform:scale(0.97)}
    .chat-send-btn:disabled{opacity:0.4;cursor:not-allowed;transform:none;box-shadow:none}
    .chat-send-btn i{font-size:11px;font-weight:700}
    .char-count{font-size:9px;color:var(--text-dim);font-family:'JetBrains Mono',monospace}

    .chat-toast{animation:ctIn .35s cubic-bezier(.16,1,.3,1) forwards;pointer-events:auto}
    @keyframes ctIn{from{opacity:0;transform:translateY(-12px) scale(0.96)}to{opacity:1;transform:translateY(0) scale(1)}}
    .chat-toast-out{animation:ctOut .25s ease forwards}
    @keyframes ctOut{to{opacity:0;transform:translateY(-8px) scale(0.96)}}

    /* File attachment preview */
    .attach-preview{display:none;padding:0 12px 8px;animation:attachSlide .25s ease forwards}
    .attach-preview.visible{display:block}
    @keyframes attachSlide{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
    .attach-preview-inner{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:10px;background:var(--surface-3);border:1px solid var(--border)}
    .attach-file-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .attach-file-icon i{font-size:13px}
    .attach-file-icon.pdf{background:rgba(248,113,113,0.12)}
    .attach-file-icon.pdf i{color:#f87171}
    .attach-file-icon.doc{background:rgba(59,130,246,0.12)}
    .attach-file-icon.doc i{color:#3b82f6}
    .attach-file-icon.img{background:rgba(52,211,153,0.12)}
    .attach-file-icon.img i{color:#34d399}
    .attach-file-icon.xls{background:rgba(52,211,153,0.12)}
    .attach-file-icon.xls i{color:#34d399}
    .attach-file-icon.ppt{background:rgba(251,191,36,0.12)}
    .attach-file-icon.ppt i{color:#fbbf24}
    .attach-file-icon.default{background:rgba(129,140,248,0.12)}
    .attach-file-icon.default i{color:#818cf8}
    .attach-file-info{flex:1;min-width:0}
    .attach-file-name{font-size:12px;font-weight:500;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .attach-file-meta{display:flex;align-items:center;gap:8px;margin-top:2px}
    .attach-file-size{font-size:10px;color:var(--text-dim);font-family:'JetBrains Mono',monospace}
    .attach-file-status{font-size:10px;font-weight:500;font-family:'JetBrains Mono',monospace}
    .attach-file-status.uploading{color:#22d3ee}
    .attach-file-status.done{color:#34d399}
    .attach-file-status.error{color:#f87171}
    .attach-progress-bar{width:100%;height:3px;border-radius:99px;background:var(--surface-3);overflow:hidden;margin-top:5px}
    .attach-progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#22d3ee,#06b6d4);width:0%;transition:width .15s ease}
    .attach-progress-fill.done{background:#34d399}
    .attach-progress-fill.error{background:#f87171}
    .attach-remove{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);transition:all .15s ease;background:transparent;border:none;cursor:pointer;flex-shrink:0}
    .attach-remove:hover{color:#f87171;background:rgba(248,113,113,0.1)}
    .attach-remove i{font-size:10px}

    /* Send button spinner */
    .chat-send-btn .send-spinner{display:none;width:14px;height:14px;border:2px solid rgba(2,6,23,0.2);border-top-color:#020617;border-radius:50%;animation:spinR .6s linear infinite}
    .chat-send-btn.loading .send-spinner{display:block}
    .chat-send-btn.loading .fa-arrow-up{display:none}
    @keyframes spinR{to{transform:rotate(360deg)}}

    /* Documents modal */
    .docs-modal-overlay{position:fixed;inset:0;z-index:60;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center}
    .docs-modal-overlay.open{display:flex}
    .docs-modal-box{background:rgba(15,23,42,0.95);backdrop-filter:blur(24px);padding:20px;border-radius:14px;max-width:640px;width:92%;box-shadow:0 16px 48px rgba(0,0,0,0.5);border:1px solid rgba(226,232,240,0.08);animation:docsModalIn .25s cubic-bezier(.16,1,.3,1) forwards}
    @keyframes docsModalIn{from{opacity:0;transform:scale(0.96) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
    .docs-modal-box h3{color:#e2e8f0;font-weight:700;font-size:15px;margin-bottom:14px}
    .docs-modal-close{background:transparent;border:none;color:rgba(226,232,240,0.4);font-size:20px;cursor:pointer;line-height:1;padding:0 4px;transition:color .15s ease}
    .docs-modal-close:hover{color:#e2e8f0}
    .docs-list-wrap{max-height:360px;overflow-y:auto;display:flex;flex-direction:column;gap:6px}
    .docs-list-wrap::-webkit-scrollbar{width:4px}
    .docs-list-wrap::-webkit-scrollbar-thumb{background:rgba(34,211,238,0.1);border-radius:99px}
    .doc-row{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid rgba(226,232,240,0.04);cursor:pointer;transition:all .15s ease}
    .doc-row:hover{background:rgba(34,211,238,0.04);border-color:rgba(34,211,238,0.1)}
    .doc-row.selected{background:rgba(34,211,238,0.08);border-color:rgba(34,211,238,0.2)}
    .doc-row-icon{width:32px;height:32px;border-radius:8px;background:rgba(34,211,238,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .doc-row-icon i{font-size:12px;color:#22d3ee}
    .doc-row-title{font-size:13px;font-weight:500;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1}
    .doc-row-meta{font-size:10px;color:rgba(226,232,240,0.3);font-family:'JetBrains Mono',monospace}
    .doc-process-btn{padding:5px 10px;border-radius:7px;font-size:10px;font-weight:500;color:rgba(226,232,240,0.5);background:transparent;border:1px solid rgba(226,232,240,0.08);cursor:pointer;transition:all .15s ease;white-space:nowrap;flex-shrink:0}
    .doc-process-btn:hover{color:#22d3ee;border-color:rgba(34,211,238,0.2);background:rgba(34,211,238,0.06)}
    .docs-modal-footer{display:flex;justify-content:flex-end;margin-top:14px;gap:8px}
    .docs-cancel-btn{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:500;color:rgba(226,232,240,0.5);background:rgba(226,232,240,0.03);border:1px solid rgba(226,232,240,0.06);cursor:pointer;transition:all .15s ease}
    .docs-cancel-btn:hover{color:#e2e8f0;border-color:rgba(226,232,240,0.12)}
    .docs-insert-btn{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;color:#020617;background:#22d3ee;border:none;cursor:pointer;transition:all .15s ease}
    .docs-insert-btn:hover{background:rgba(34,211,238,0.85)}
    .docs-insert-btn:disabled{opacity:0.4;cursor:not-allowed}
    .docs-empty{text-align:center;padding:24px;color:rgba(226,232,240,0.3);font-size:13px}

    body.theme-light .chat-sidebar{background:rgba(255,255,255,0.6);border-left-color:rgba(15,23,42,0.08)}
    body.theme-light .chat-main{background:transparent}
    body.theme-light .chat-new-btn{color:#3b82f6;background:transparent;border-color:#3b82f6}
    body.theme-light .chat-new-btn:hover{background:#3b82f6;color:#fff;box-shadow:0 0 20px rgba(59,130,246,0.3)}
    body.theme-light .chat-search{background:transparent;border-color:rgba(59,130,246,0.15)}
    body.theme-light .chat-search:focus-within{border-color:#3b82f6;box-shadow:0 0 12px rgba(59,130,246,0.15)}
    body.theme-light .chat-search input{color:#475569}
    body.theme-light .chat-search input::placeholder{color:rgba(15,23,42,0.3)}
    body.theme-light .qmode-card:hover{background:rgba(59,130,246,0.06)}
    body.theme-light .qmode-card p{color:#475569}
    body.theme-light .qmode-card:hover p{color:#1f2937}
    body.theme-light .conv-item:hover{background:rgba(59,130,246,0.06)}
    body.theme-light .conv-item.active{background:rgba(59,130,246,0.1);border-color:rgba(59,130,246,0.2)}
    body.theme-light .conv-item.active .conv-title{color:#334155}
    body.theme-light .conv-item:not(.active) .conv-title{color:#64748b}
    body.theme-light .conv-item.active i{color:rgba(59,130,246,0.5)}
    body.theme-light .conv-item:not(.active) i{color:rgba(15,23,42,0.2)}
    body.theme-light .hist-label{color:rgba(15,23,42,0.35);border-top-color:rgba(59,130,246,0.08)}
    body.theme-light .hist-clear{color:rgba(59,130,246,0.6)}
    body.theme-light .hist-clear:hover{color:#3b82f6}
    body.theme-light .chat-topbar{border-bottom-color:rgba(15,23,42,0.06)}
    body.theme-light .mode-chip{color:#64748b}
    body.theme-light .mode-chip:hover{background:rgba(15,23,42,0.04);border-color:rgba(15,23,42,0.08);color:#334155}
    body.theme-light .mode-chip.active{background:rgba(59,130,246,0.08);border-color:rgba(59,130,246,0.15);color:#1d4ed8}
    body.theme-light .mode-chip.active .chip-dot{background:#3b82f6;box-shadow:0 0 6px rgba(59,130,246,0.4)}
    body.theme-light .mode-chip:not(.active) .chip-dot{background:rgba(15,23,42,0.2)}
    body.theme-light .usage-mini{background:rgba(15,23,42,0.03);border-color:rgba(15,23,42,0.06);color:#64748b}
    body.theme-light .usage-mini-fill{background:#3b82f6}
    body.theme-light .usage-upgrade{background:#3b82f6}
    body.theme-light .usage-upgrade:hover{background:rgba(59,130,246,0.85)}
    body.theme-light .suggest-pill{color:#64748b;background:rgba(15,23,42,0.04);border-color:rgba(15,23,42,0.08)}
    body.theme-light .suggest-pill:hover{border-color:rgba(59,130,246,0.2);color:#334155;background:rgba(59,130,246,0.06)}
    body.theme-light .chat-bubble-user{background:linear-gradient(135deg,#3b82f6,#6366f1)!important;color:#fff!important}
    body.theme-light .chat-bubble-user .file-attach-badge{background:rgba(255,255,255,0.15)}
    body.theme-light .ai-avatar-sm{background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(99,102,241,0.1));border-color:rgba(59,130,246,0.1)}
    body.theme-light .ai-avatar-sm i{color:#3b82f6}
    body.theme-light .ai-avatar-lg{background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(99,102,241,0.1),rgba(16,185,129,0.08));border-color:rgba(59,130,246,0.15)}
    body.theme-light .ai-avatar-lg::after{border-color:rgba(59,130,246,0.2)}
    body.theme-light .ai-avatar-lg i{color:#3b82f6}
    body.theme-light .chat-bubble-ai{background:rgba(15,23,42,0.04);border-color:rgba(15,23,42,0.08)}
    body.theme-light .chat-bubble-ai .msg-text{color:#475569}
    body.theme-light .chat-bubble-ai strong{color:#0f172a}
    body.theme-light .chat-bubble-ai code{background:rgba(15,23,42,0.06);color:#1d4ed8}
    body.theme-light .chat-msg-actions button{color:rgba(15,23,42,0.2)}
    body.theme-light .chat-msg-actions button:hover{color:rgba(15,23,42,0.5);background:rgba(15,23,42,0.04)}
    body.theme-light .chat-msg-time{color:rgba(15,23,42,0.25)}
    body.theme-light .chat-input-wrap{background:rgba(255,255,255,0.6);border-color:rgba(15,23,42,0.1)}
    body.theme-light .chat-input-wrap:focus-within{border-color:rgba(59,130,246,0.3);box-shadow:0 0 0 3px rgba(59,130,246,0.05)}
    body.theme-light .chat-input-wrap textarea{color:#0f172a}
    body.theme-light .chat-input-wrap textarea::placeholder{color:rgba(15,23,42,0.3)}
    body.theme-light #chatFooter{background:rgba(255,255,255,0.95) !important; border-top-color:rgba(15,23,42,0.08) !important; box-shadow:0 -12px 32px rgba(15,23,42,0.08) !important; color:#0f172a !important}
    body.theme-light .chat-wrap{background:transparent;}
    body.theme-light .chat-welcome h1{color:#0f172a !important}
    body.theme-light .chat-welcome p{color:rgba(15,23,42,0.65) !important}
    body.theme-light .chat-welcome .qmode-card p{color:rgba(15,23,42,0.65) !important}
    body.theme-light .chat-welcome .qmode-card{background:transparent !important; border-color:transparent !important}
    body.theme-light .chat-welcome .qmode-card .qmode-icon{background:rgba(34,211,238,0.12) !important}
    body.theme-light .chat-welcome .qmode-card .qmode-icon i{color:#22d3ee !important}
    body.theme-light .chat-welcome .qmode-card div p{color:rgba(15,23,42,0.65) !important}
    body.theme-light .input-tool{color:rgba(15,23,42,0.25)}
    body.theme-light .input-tool:hover{color:#1d4ed8;background:rgba(59,130,246,0.08)}
    body.theme-light .chat-send-btn{background:#3b82f6}
    body.theme-light .chat-send-btn:hover{background:linear-gradient(135deg,#3b82f6,#6366f1);box-shadow:0 0 16px rgba(59,130,246,0.2)}
    body.theme-light .char-count{color:rgba(15,23,42,0.25)}
    body.theme-light .typing-dot{background:rgba(59,130,246,0.5)}
    body.theme-light .chat-new-btn{color:#3b82f6;background:transparent;border-color:#3b82f6}
    body.theme-light .chat-new-btn:hover{background:#3b82f6;color:#fff;box-shadow:0 0 20px rgba(59,130,246,0.3)}
    body.theme-light .attach-preview-inner{background:rgba(15,23,42,0.03);border-color:rgba(15,23,42,0.06)}
    body.theme-light .attach-file-name{color:#334155}
    body.theme-light .attach-file-size{color:rgba(15,23,42,0.35)}
    body.theme-light .attach-file-status.uploading{color:#3b82f6}
    body.theme-light .attach-progress-bar{background:rgba(15,23,42,0.08)}
    body.theme-light .attach-progress-fill{background:linear-gradient(90deg,#3b82f6,#6366f1)}
    body.theme-light .attach-remove{color:rgba(15,23,42,0.25)}
    body.theme-light .attach-remove:hover{color:#ef4444;background:rgba(239,68,68,0.08)}
    body.theme-light .docs-modal-overlay{background:rgba(0,0,0,0.4)}
    body.theme-light .docs-modal-box{background:rgba(255,255,255,0.95);border-color:rgba(15,23,42,0.1)}
    body.theme-light .docs-modal-box h3{color:#0f172a}
    body.theme-light .docs-modal-close{color:rgba(15,23,42,0.4)}
    body.theme-light .docs-modal-close:hover{color:#0f172a}
    body.theme-light .doc-row{border-color:rgba(15,23,42,0.06)}
    body.theme-light .doc-row:hover{background:rgba(59,130,246,0.04);border-color:rgba(59,130,246,0.1)}
    body.theme-light .doc-row.selected{background:rgba(59,130,246,0.08);border-color:rgba(59,130,246,0.2)}
    body.theme-light .doc-row-icon{background:rgba(59,130,246,0.1)}
    body.theme-light .doc-row-icon i{color:#3b82f6}
    body.theme-light .doc-row-title{color:#0f172a}
    body.theme-light .doc-row-meta{color:rgba(15,23,42,0.35)}
    body.theme-light .doc-process-btn{color:rgba(15,23,42,0.5);border-color:rgba(15,23,42,0.1)}
    body.theme-light .doc-process-btn:hover{color:#1d4ed8;border-color:rgba(59,130,246,0.2);background:rgba(59,130,246,0.06)}
    body.theme-light .docs-cancel-btn{color:rgba(15,23,42,0.5);background:rgba(15,23,42,0.04);border-color:rgba(15,23,42,0.08)}
    body.theme-light .docs-cancel-btn:hover{color:#0f172a;border-color:rgba(15,23,42,0.15)}
    body.theme-light .docs-insert-btn{background:#3b82f6}
    body.theme-light .docs-insert-btn:hover{background:rgba(59,130,246,0.85)}
    body.theme-light .docs-empty{color:rgba(15,23,42,0.35)}

    @media(max-width:1024px){
        .chat-sidebar{width:240px}
        .chat-wrap{padding-right:240px}
    }
    @media(max-width:768px){
        .chat-wrap{
            display:block;
            height:auto;
            min-height:calc(100vh - 65px);
            max-height:none;
            overflow:visible;
            padding-right:0;
        }
        .chat-main{
            width:100%;
            min-width:0;
            min-height:calc(100vh - 65px);
            margin-right:0;
            overflow:visible;
            position:relative;
            z-index:1;
        }
        .chat-sidebar{
            display:none!important;
            position:fixed;
            top:65px;
            right:0;
            bottom:0;
            width:min(280px,100vw);
            max-width:100%;
            transform:translateX(100%);
            z-index:60;
            box-shadow:-16px 0 60px rgba(0,0,0,0.25);
            overflow-y:auto;
            -webkit-overflow-scrolling:touch;
        }
        .chat-sidebar.mobile-open{
            display:flex!important;
            transform:translateX(0);
        }
        .chat-topbar{padding:6px 12px}
        .mode-chips-row{overflow-x:auto;flex-wrap:nowrap!important;-webkit-overflow-scrolling:touch}
        .mode-chips-row::-webkit-scrollbar{display:none}
    }
    #chatSidebarOverlay{display:none;position:fixed;inset:0;z-index:55;background:rgba(0,0,0,0.55);}
    #chatSidebarOverlay.active{display:block;}
</style>
@endsection

@section('content')
<div class="chat-wrap">
    <!-- Sidebar -->
    <aside class="chat-sidebar">
        <div class="p-3 flex-shrink-0">
            <button type="button" id="newChatBtn" class="chat-new-btn">
                <i class="fa-solid fa-plus" style="font-size:10px"></i>
                New Chat
            </button>
        </div>
        <div class="px-3 pb-2 flex-shrink-0">
            <div class="chat-search">
                <i class="fa-solid fa-magnifying-glass" style="font-size:10px;color:rgba(226,232,240,0.15)"></i>
                <input type="text" placeholder="Search chats..." id="chatSearchInput">
            </div>
        </div>
        <div class="px-3 pb-2 flex-shrink-0">
            <p class="hist-label">Quick Modes</p>
            <div class="space-y-1.5 mt-1">
                <button type="button" data-mode="general" class="qmode-card">
                    <div class="qmode-icon" style="background:rgba(34,211,238,0.12)"><i class="fa-solid fa-lightbulb" style="color:#22d3ee"></i></div>
                    <p>General help</p>
                </button>
                <button type="button" data-mode="explain" class="qmode-card">
                    <div class="qmode-icon" style="background:rgba(129,140,248,0.12)"><i class="fa-solid fa-book-open" style="color:#818cf8"></i></div>
                    <p>Explain concepts</p>
                </button>
                <button type="button" data-mode="math" class="qmode-card">
                    <div class="qmode-icon" style="background:rgba(52,211,153,0.12)"><i class="fa-solid fa-calculator" style="color:#34d399"></i></div>
                    <p>Math step-by-step</p>
                </button>
                <button type="button" data-mode="summarize" class="qmode-card">
                    <div class="qmode-icon" style="background:rgba(251,191,36,0.12)"><i class="fa-solid fa-compress" style="color:#fbbf24"></i></div>
                    <p>Summarize notes</p>
                </button>
                <button type="button" data-mode="quiz" class="qmode-card">
                    <div class="qmode-icon" style="background:rgba(248,113,113,0.12)"><i class="fa-solid fa-clipboard-question" style="color:#f87171"></i></div>
                    <p>Generate quizzes</p>
                </button>
            </div>
        </div>
        <div class="flex-1 min-h-0 flex flex-col px-2 pb-2 overflow-hidden">
            <div class="flex items-center justify-between px-2 pt-3 pb-1">
                <p class="hist-label" style="padding:0">History</p>
                <button type="button" id="clearHistoryBtn" class="hist-clear">Clear</button>
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto pr-1 space-y-3" id="historyList">
                <div id="pinnedSection" class="history-section hidden">
                    <p class="hist-section-label">Pinned</p>
                    <div id="pinnedList" class="space-y-1"></div>
                </div>
                <div id="recentSection" class="history-section">
                    <p class="hist-section-label">Recents</p>
                    <div id="recentList" class="space-y-1"></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Chat -->
    <div class="chat-main">
        <!-- Top Bar -->
        <div class="chat-topbar" style="align-items:center;justify-content:space-between">
            <button type="button" class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center text-c-40 hover:text-cy hover:bg-cy/5 transition-all" onclick="toggleChatSidebar()" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars text-sm"></i>
            </button>
            <div class="mode-chips-row flex items-center gap-1.5 flex-wrap" style="flex:1">
                <button type="button" class="mode-chip active" data-mode="general"><div class="chip-dot"></div>General</button>
                <button type="button" class="mode-chip" data-mode="explain"><div class="chip-dot"></div>Explain</button>
                <button type="button" class="mode-chip" data-mode="math"><div class="chip-dot"></div>Math</button>
                <button type="button" class="mode-chip" data-mode="summarize"><div class="chip-dot"></div>Summarize</button>
                <button type="button" class="mode-chip" data-mode="quiz"><div class="chip-dot"></div>Quiz</button>
                <button type="button" class="mode-chip" data-mode="flashcards"><div class="chip-dot"></div>Flashcards</button>
                <button type="button" class="mode-chip" data-mode="essay"><div class="chip-dot"></div>Essay</button>
                <button type="button" class="mode-chip" data-mode="research"><div class="chip-dot"></div>Research</button>
                <button type="button" class="mode-chip" data-mode="study"><div class="chip-dot"></div>Study plan</button>
            </div>
        </div>

        <!-- Messages -->
        <div id="chatMsgContainer" class="flex-1 min-h-0 overflow-y-auto px-4 lg:px-6">
            <div id="chatWelcome" class="chat-welcome">
                <div class="wfade text-center" style="max-width:480px;width:100%;animation-delay:.05s">
                    <div class="ai-avatar-lg mx-auto mb-5"><i class="fa-solid fa-brain"></i></div>
                    <h1 style="font-size:22px;font-weight:700;color:#e2e8f0;letter-spacing:-0.02em;margin-bottom:8px">SHEELEARN <span style="background:linear-gradient(135deg,#22d3ee,#818cf8,#34d399);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">AI Tutor</span></h1>
                    <p style="font-size:13px;color:rgba(226,232,240,0.4);line-height:1.625;max-width:380px;margin:0 auto 28px">Your personal learning assistant. Ask questions, get explanations, create flashcards, and master any subject.</p>
                </div>
                <div class="wfade" style="max-width:480px;width:100%;animation-delay:.15s">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div class="qmode-card" style="padding:14px" onclick="chatQuickPrompt('Can you help me understand this topic in simple terms?')">
                            <div class="qmode-icon" style="background:rgba(34,211,238,0.12);width:32px;height:32px"><i class="fa-solid fa-lightbulb" style="color:#22d3ee;font-size:12px"></i></div>
                            <div><p style="font-size:12px;font-weight:600;color:rgba(226,232,240,0.6);margin-bottom:2px">Explain Concepts</p><p style="font-size:10.5px;color:rgba(226,232,240,0.25);line-height:1.5">Break down complex topics simply</p></div>
                        </div>
                        <div class="qmode-card" style="padding:14px" onclick="chatQuickPrompt('Create flashcards from this material to help me study effectively.')">
                            <div class="qmode-icon" style="background:rgba(129,140,248,0.12);width:32px;height:32px"><i class="fa-solid fa-layer-group" style="color:#818cf8;font-size:12px"></i></div>
                            <div><p style="font-size:12px;font-weight:600;color:rgba(226,232,240,0.6);margin-bottom:2px">Generate Flashcards</p><p style="font-size:10.5px;color:rgba(226,232,240,0.25);line-height:1.5">Turn content into study cards</p></div>
                        </div>
                        <div class="qmode-card" style="padding:14px" onclick="chatQuickPrompt('Quiz me on this subject to test my knowledge and understanding.')">
                            <div class="qmode-icon" style="background:rgba(52,211,153,0.12);width:32px;height:32px"><i class="fa-solid fa-clipboard-question" style="color:#34d399;font-size:12px"></i></div>
                            <div><p style="font-size:12px;font-weight:600;color:rgba(226,232,240,0.6);margin-bottom:2px">Practice Quizzes</p><p style="font-size:10.5px;color:rgba(226,232,240,0.25);line-height:1.5">Test with adaptive questions</p></div>
                        </div>
                        <div class="qmode-card" style="padding:14px" onclick="chatQuickPrompt('Help me write a well-structured essay with a clear thesis and arguments.')">
                            <div class="qmode-icon" style="background:rgba(251,191,36,0.12);width:32px;height:32px"><i class="fa-solid fa-pen-fancy" style="color:#fbbf24;font-size:12px"></i></div>
                            <div><p style="font-size:12px;font-weight:600;color:rgba(226,232,240,0.6);margin-bottom:2px">Writing Assistant</p><p style="font-size:10.5px;color:rgba(226,232,240,0.25);line-height:1.5">Structure essays and reports</p></div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="wfade" style="animation-delay:.25s;">
                    <button class="suggest-pill" onclick="chatQuickPrompt('What is the difference between mitosis and meiosis?')">Mitosis vs Meiosis</button>
                    <button class="suggest-pill" onclick="chatQuickPrompt('Explain Newton\'s three laws of motion with real-world examples.')">Newton's Laws</button>
                    <button class="suggest-pill" onclick="chatQuickPrompt('How does photosynthesis work step by step?')">Photosynthesis</button>
                    <button class="suggest-pill" onclick="chatQuickPrompt('Summarize the key causes of World War I.')">World War I</button>
                </div>
            </div>

            <!-- Thread -->
            <div id="messages" class="hidden" style="padding:20px 0;max-width:768px;margin:0 auto;width:100%;display:flex;flex-direction:column;gap:16px"></div>
        </div>

        <!-- Input Footer -->
        <div id="chatFooter" style="position:sticky;bottom:0;left:0;right:0;flex-shrink:0;background:rgba(2,6,23,0.98);backdrop-filter:blur(22px);border-top:1px solid rgba(226,232,240,0.06);padding:12px 16px 16px;box-shadow:0 -12px 32px rgba(0,0,0,0.28)">
            <div style="max-width:768px;margin:0 auto">
                <form id="aiChatForm" autocomplete="off" enctype="multipart/form-data">
                    <select id="modeSelect" name="mode" class="sr-only" aria-hidden="true">
                        <option value="general">General</option>
                        <option value="explain">Explain</option>
                        <option value="math">Math</option>
                        <option value="summarize">Summarize</option>
                        <option value="quiz">Quiz</option>
                        <option value="flashcards">Flashcards</option>
                        <option value="essay">Essay</option>
                        <option value="research">Research</option>
                        <option value="study">Study plan</option>
                    </select>
                    <div class="chat-input-wrap">
                        <!-- File attachment preview -->
                        <div id="attachPreview" class="attach-preview">
                            <div class="attach-preview-inner">
                                <div id="attachIcon" class="attach-file-icon default">
                                    <i class="fa-solid fa-file"></i>
                                </div>
                                <div class="attach-file-info">
                                    <p id="attachFileName" class="attach-file-name">document.pdf</p>
                                    <div class="attach-file-meta">
                                        <span id="attachFileSize" class="attach-file-size">0 KB</span>
                                        <span id="attachFileStatus" class="attach-file-status done">Ready</span>
                                    </div>
                                    <div id="attachProgressBar" class="attach-progress-bar" style="display:none">
                                        <div id="attachProgressFill" class="attach-progress-fill"></div>
                                    </div>
                                </div>
                                <button type="button" id="attachRemoveBtn" class="attach-remove" title="Remove file">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Toolbar -->
                        <div style="display:flex;align-items:center;gap:4px;padding:10px 12px 4px">
                            <button type="button" id="attachBtn" class="input-tool" title="Attach file (PDF, DOCX, TXT, PPTX, XLSX, CSV, PNG, JPG)"><i class="fa-solid fa-paperclip"></i></button>
                            <button type="button" id="useDocsBtn" class="input-tool" title="Use document context"><i class="fa-solid fa-file-lines"></i></button>
                            <button type="button" id="voiceBtn" class="input-tool" title="Voice input"><i class="fa-solid fa-microphone"></i></button>
                            <div style="flex:1"></div>
                            <span class="char-count" id="chatCharCount">0 / 20000</span>
                        </div>
                        <!-- Textarea -->
                        <div style="padding:0 12px 6px">
                            <textarea id="chatInput" name="message" rows="1" placeholder="Ask anything about your studies..."></textarea>
                        </div>
                        <!-- Send -->
                        <div style="display:flex;align-items:center;gap:8px;padding:0 12px 10px">
                            <div style="flex:1"></div>
                            <button type="submit" id="chatSendBtn" class="chat-send-btn" aria-label="Send message">
                                <i class="fa-solid fa-arrow-up"></i>
                                <div class="send-spinner"></div>
                            </button>
                        </div>
                    </div>
                </form>
                <p style="text-align:center;font-size:9.5px;color:rgba(226,232,240,0.12);margin-top:8px;font-weight:500">SHEELEARN AI can make mistakes. Verify important information with your course materials.</p>
            </div>
        </div>
    </div>
</div>

<div id="chatSidebarOverlay"></div>
<!-- Toast container -->
<div id="chatToastBox" style="position:fixed;top:16px;right:16px;z-index:100;display:flex;flex-direction:column;gap:8px;pointer-events:none"></div>

<!-- Hidden file input -->
<input type="file" id="attachInput" class="hidden" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx,.csv,.png,.jpg,.jpeg" />

<!-- Documents modal -->
<div id="documentsModal" class="docs-modal-overlay">
    <div class="docs-modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <h3>Your Documents</h3>
            <button type="button" id="closeDocsModal" class="docs-modal-close">&times;</button>
        </div>
        <div id="documentsList" class="docs-list-wrap"></div>
        <div class="docs-modal-footer">
            <button type="button" id="docsCancelBtn" class="docs-cancel-btn">Cancel</button>
            <button type="button" id="docsUseBtn" class="docs-insert-btn" disabled>Insert Selected</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.body.classList.add('chat-active');

/* ============================================================
   DOM REFERENCES
   ============================================================ */
const aiChatForm       = document.getElementById('aiChatForm');
const messagesElement   = document.getElementById('messages');
const historyList       = document.getElementById('historyList');
const pinnedList        = document.getElementById('pinnedList');
const recentList        = document.getElementById('recentList');
const pinnedSection     = document.getElementById('pinnedSection');
const chatInput         = document.getElementById('chatInput');
const modeSelect        = document.getElementById('modeSelect');
const clearHistoryBtn   = document.getElementById('clearHistoryBtn');
const chatWelcome       = document.getElementById('chatWelcome');
const chatCharCount     = document.getElementById('chatCharCount');
const chatSendBtn       = document.getElementById('chatSendBtn');

// File attachment DOM
const attachBtn         = document.getElementById('attachBtn');
const attachInput       = document.getElementById('attachInput');
const attachPreview     = document.getElementById('attachPreview');
const attachIcon        = document.getElementById('attachIcon');
const attachFileName    = document.getElementById('attachFileName');
const attachFileSize    = document.getElementById('attachFileSize');
const attachFileStatus  = document.getElementById('attachFileStatus');
const attachProgressBar = document.getElementById('attachProgressBar');
const attachProgressFill= document.getElementById('attachProgressFill');
const attachRemoveBtn   = document.getElementById('attachRemoveBtn');

/* ============================================================
   STATE
   ============================================================ */
@php
    $initialConversations = $conversations->map(fn($conversation) => [
        'id' => $conversation->id,
        'title' => $conversation->title,
        'updated_at' => optional($conversation->updated_at)->toIsoString(),
    ])->toArray();

    $initialMessages = $activeMessages->map(fn($message) => [
        'id' => $message->id,
        'role' => $message->role,
        'content' => $message->content,
    ])->values()->toArray();
@endphp

const initialConversations = @json($initialConversations);
const initialMessages = @json($initialMessages);
let activeConversationId = @json(optional($activeConversation)->id);
let isLoadingConversation = false;
let isSending = false;

const PINNED_CONVERSATIONS_KEY = 'aiChatPinnedConversations';
let pinnedConversationIds = loadPinnedConversationIds();

function loadPinnedConversationIds() {
    try {
        const stored = window.localStorage.getItem(PINNED_CONVERSATIONS_KEY);
        return stored ? JSON.parse(stored) : [];
    } catch (e) {
        console.warn('Failed to load pinned conversations', e);
        return [];
    }
}

function savePinnedConversationIds() {
    try {
        window.localStorage.setItem(PINNED_CONVERSATIONS_KEY, JSON.stringify(pinnedConversationIds));
    } catch (e) {
        console.warn('Failed to save pinned conversations', e);
    }
}

// *** KEY FIX: Track the attached File object ***
let attachedFile = null;

// Supported file configuration
const ALLOWED_EXTENSIONS = ['pdf','doc','docx','txt','ppt','pptx','xls','xlsx','csv','png','jpg','jpeg'];
const IMAGE_EXTENSIONS   = ['png','jpg','jpeg'];
const MAX_FILE_SIZE      = 20 * 1024 * 1024; // 20 MB
const SEND_ENDPOINT      = '{{ route("ai-chat.send") }}';

/* ============================================================
   UTILITY FUNCTIONS
   ============================================================ */
function scrollMessages() {
    requestAnimationFrame(() => {
        const container = document.getElementById('chatMsgContainer');
        if (container) container.scrollTop = container.scrollHeight;
    });
}

function chatEscHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function chatFormatContent(text) {
    let html = chatEscHtml(text);

    // Preserve math expressions and avoid mangling $...$ sequences.
    html = html.replace(/\$\$(.*?)\$\$/gs, '<span class="math-expression">$$$1$$</span>');
    html = html.replace(/\$(.*?)\$/g, '<span class="math-expression">$1</span>');

    // Preserve code blocks and inline code.
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    html = html.replace(/`([^`]+?)`/g, '<code>$1</code>');

    // Keep line breaks for structured answers.
    html = html.replace(/\n/g, '<br>');

    return html;
}

function chatGetTime() {
    const now = new Date();
    let h = now.getHours(), m = now.getMinutes();
    const ap = h >= 12 ? 'PM' : 'AM';
    h = h % 12; if (h === 0) h = 12;
    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ' ' + ap;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function getFileExtension(filename) {
    return (filename || '').split('.').pop().toLowerCase();
}

/* ============================================================
   RENDER FUNCTIONS
   ============================================================ */
@php
    $chatUserAvatarRaw = data_get(Auth::user()->settings, 'profile_avatar');
    $chatUserAvatarSrc = '';
    if (!empty($chatUserAvatarRaw)) {
        $pathOnly = parse_url($chatUserAvatarRaw, PHP_URL_PATH) ?: $chatUserAvatarRaw;
        $chatUserAvatarSrc = request()->getSchemeAndHttpHost() . $pathOnly;
    } else {
        $chatUserAvatarSrc = 'https://picsum.photos/seed/user' . Auth::id() . '/40/40.jpg';
    }
@endphp
function renderMessage(content, role, fileName) {
    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg ' + (role === 'assistant' ? '' : 'chat-msg-user');

    if (role === 'user') {
        let fileBadge = '';
        if (fileName) {
            const ext = getFileExtension(fileName);
            const isImg = IMAGE_EXTENSIONS.includes(ext);
            const iconClass = isImg ? 'fa-solid fa-image' : ext === 'pdf' ? 'fa-solid fa-file-pdf' : ['doc','docx'].includes(ext) ? 'fa-solid fa-file-word' : ['xls','xlsx','csv'].includes(ext) ? 'fa-solid fa-file-excel' : ['ppt','pptx'].includes(ext) ? 'fa-solid fa-file-powerpoint' : 'fa-solid fa-file';
            fileBadge = '<div class="file-attach-badge"><i class="' + iconClass + '"></i> ' + chatEscHtml(fileName) + '</div>';
        }
        wrapper.innerHTML =
            '<div style="max-width:75%">' +
                '<div class="chat-bubble-user"><p>' + chatEscHtml(content) + '</p>' + fileBadge + '</div>' +
                '<p class="chat-msg-time" style="text-align:right;margin-top:4px">' + chatGetTime() + '</p>' +
            '</div>' +
            '<img src="{{ $chatUserAvatarSrc }}" alt="You" class="chat-avatar-user">';
    } else {
        wrapper.innerHTML =
            '<div class="ai-avatar-sm"><i class="fa-solid fa-brain"></i></div>' +
            '<div style="flex:1;min-width:0">' +
                '<div class="chat-bubble-ai"><div class="msg-text">' + chatFormatContent(content) + '</div></div>' +
                '<div class="chat-msg-footer">' +
                    '<span class="chat-msg-time">' + chatGetTime() + '</span>' +
                    '<div class="chat-msg-actions">' +
                        '<button onclick="chatCopyMsg(this)" title="Copy"><i class="fa-regular fa-copy"></i></button>' +
                        '<button title="Regenerate"><i class="fa-solid fa-rotate"></i></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    messagesElement.appendChild(wrapper);
    scrollMessages();
    return wrapper;
}

function renderTypingIndicator() {
    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg';
    wrapper.id = 'chatTypingIndicator';
    wrapper.innerHTML =
        '<div class="ai-avatar-sm"><i class="fa-solid fa-brain"></i></div>' +
        '<div class="chat-bubble-ai" style="display:flex;align-items:center;gap:6px;padding:14px 18px">' +
            '<div class="typing-dot"></div>' +
            '<div class="typing-dot"></div>' +
            '<div class="typing-dot"></div>' +
        '</div>';
    messagesElement.appendChild(wrapper);
    scrollMessages();
    return wrapper;
}

function removeTypingIndicator() {
    const el = document.getElementById('chatTypingIndicator');
    if (el) el.remove();
}

function renderHistoryItem(conversation) {
    const item = document.createElement('div');
    item.className = 'conv-item' + (activeConversationId === conversation.id ? ' active' : '');
    item.setAttribute('role', 'button');
    item.tabIndex = 0;
    item.dataset.conversationId = conversation.id;
    item.dataset.updatedAt = conversation.updated_at ?? '';

    const title = document.createElement('p');
    title.className = 'conv-title';
    title.textContent = conversation.title || 'Untitled study chat';

    const isPinned = pinnedConversationIds.includes(conversation.id);
    item.dataset.pinned = isPinned ? 'true' : 'false';

    const icon = document.createElement('i');
    icon.className = 'fa-regular fa-message';

    const menuWrapper = document.createElement('div');
    menuWrapper.className = 'conv-menu';

    const menuToggle = document.createElement('button');
    menuToggle.type = 'button';
    menuToggle.className = 'conv-item-menu-toggle';
    menuToggle.setAttribute('aria-label', 'Conversation actions');
    menuToggle.innerHTML = '<i class="fa-solid fa-ellipsis-vertical"></i>';

    const menu = document.createElement('div');
    menu.className = 'conv-item-menu';
    menu.innerHTML = `
        <button type="button" class="conv-item-action" data-action="pin">Pin</button>
        <button type="button" class="conv-item-action" data-action="rename">Rename</button>
        <button type="button" class="conv-item-action" data-action="archive">Archive</button>
        <button type="button" class="conv-item-action" data-action="delete">Delete</button>
    `;

    menuWrapper.appendChild(menuToggle);
    menuWrapper.appendChild(menu);

    item.appendChild(icon);
    item.appendChild(title);
    item.appendChild(menuWrapper);
    if (isPinned) {
        pinnedList.appendChild(item);
    } else {
        recentList.appendChild(item);
    }
    updatePinnedSection();
    return item;
}

function updatePinnedSection() {
    pinnedSection.classList.toggle('hidden', pinnedList.children.length === 0);
}

function renderConversationMessages(messages) {
    if (!messagesElement) return;
    messagesElement.innerHTML = '';
    if (!messages || !messages.length) {
        if (chatWelcome) chatWelcome.classList.add('visible');
        messagesElement.classList.add('hidden');
        messagesElement.style.display = 'none';
        return;
    }
    if (chatWelcome) chatWelcome.classList.remove('visible');
    messagesElement.classList.remove('hidden');
    messagesElement.style.display = 'flex';
    messages.forEach(message => renderMessage(message.content, message.role));
    scrollMessages();
}

/* ============================================================
   CONVERSATION NAVIGATION
   ============================================================ */
function setActiveConversation(conversation) {
    const conversationId = typeof conversation === 'number' ? conversation : conversation?.id;
    activeConversationId = Number(conversationId);
    historyList.querySelectorAll('.conv-item').forEach(item => {
        item.classList.toggle('active', Number(item.dataset.conversationId) === activeConversationId);
    });
}

async function loadConversation(conversationId) {
    if (isLoadingConversation || Number(conversationId) === activeConversationId) return;
    isLoadingConversation = true;
    chatWelcome.classList.remove('visible');
    messagesElement.classList.remove('hidden');
    messagesElement.style.display = 'flex';
    messagesElement.innerHTML = '<div class="chat-msg"><div class="ai-avatar-sm"><i class="fa-solid fa-brain"></i></div><div class="chat-bubble-ai"><div class="msg-text" style="color:rgba(226,232,240,0.4)">Loading conversation...</div></div></div>';

    try {
        const response = await fetch(`{{ url('/ai-chat/conversation') }}/${conversationId}`, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        if (response.ok && data.success) {
            setActiveConversation(data.conversation);
            renderConversationMessages(data.messages || []);
            scrollMessages();
        }
    } catch (error) {
        console.error('Unable to load conversation.', error);
    } finally {
        isLoadingConversation = false;
    }
}

/* ============================================================
   MODE SYNC
   ============================================================ */
function syncModeChips() {
    const val = modeSelect.value;
    document.querySelectorAll('.mode-chip').forEach(chip => {
        chip.classList.toggle('active', chip.dataset.mode === val);
    });
}

// Quick mode buttons in sidebar
document.querySelectorAll('.qmode-card[data-mode]').forEach(button => {
    button.addEventListener('click', () => {
        modeSelect.value = button.dataset.mode;
        syncModeChips();
        chatInput.focus();
    });
});

// Mode chips in topbar
document.querySelectorAll('.mode-chip[data-mode]').forEach(chip => {
    chip.addEventListener('click', () => {
        modeSelect.value = chip.dataset.mode;
        syncModeChips();
    });
});

/* ============================================================
   HISTORY / SEARCH / NEW CHAT
   ============================================================ */
historyList.addEventListener('click', (event) => {
    const menuToggle = event.target.closest('.conv-item-menu-toggle');
    if (menuToggle) {
        const item = event.target.closest('.conv-item');
        if (!item) return;
        const open = !item.classList.contains('menu-open');
        historyList.querySelectorAll('.conv-item.menu-open').forEach(other => {
            if (other !== item) other.classList.remove('menu-open');
        });
        item.classList.toggle('menu-open', open);
        event.stopPropagation();
        return;
    }

    const actionButton = event.target.closest('.conv-item-action');
    if (actionButton) {
        const item = event.target.closest('.conv-item');
        if (!item) return;
        handleHistoryAction(actionButton.dataset.action, item);
        event.stopPropagation();
        return;
    }

    const item = event.target.closest('.conv-item');
    if (!item) return;
    if (event.target.closest('.conv-item-menu')) return;
    item.classList.remove('menu-open');
    loadConversation(item.dataset.conversationId);
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('.conv-item')) {
        historyList.querySelectorAll('.conv-item.menu-open').forEach(item => item.classList.remove('menu-open'));
    }
});

document.getElementById('chatSearchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    historyList.querySelectorAll('.conv-item').forEach(item => {
        const title = item.querySelector('.conv-title')?.textContent.toLowerCase() || '';
        item.style.display = title.indexOf(q) !== -1 ? 'flex' : 'none';
    });
});

function handleHistoryAction(action, item) {
    switch (action) {
        case 'pin':
            pinConversation(item);
            break;
        case 'rename':
            renameConversation(item);
            break;
        case 'archive':
            archiveConversation(item);
            break;
        case 'delete':
            deleteConversation(item);
            break;
    }
    item.classList.remove('menu-open');
}

function pinConversation(item) {
    const conversationId = Number(item.dataset.conversationId);
    if (item.dataset.pinned === 'true') {
        chatToast('Conversation is already pinned', 'info');
        return;
    }

    item.dataset.pinned = 'true';
    pinnedConversationIds = Array.from(new Set([...pinnedConversationIds, conversationId]));
    savePinnedConversationIds();
    pinnedList.appendChild(item);
    updatePinnedSection();
    chatToast('Pinned conversation', 'success');
}

function renameConversation(item) {
    const titleElement = item.querySelector('.conv-title');
    const currentTitle = titleElement?.textContent || '';
    const newTitle = prompt('Rename conversation:', currentTitle);
    if (newTitle !== null) {
        titleElement.textContent = newTitle.trim() || 'Untitled study chat';
        chatToast('Conversation renamed', 'success');
    }
}

function archiveConversation(item) {
    if (item.dataset.pinned === 'true') {
        const conversationId = Number(item.dataset.conversationId);
        pinnedConversationIds = pinnedConversationIds.filter(id => id !== conversationId);
        savePinnedConversationIds();
    }
    item.style.display = 'none';
    chatToast('Archived conversation', 'info');
}

function deleteConversation(item) {
    const conversationId = Number(item.dataset.conversationId);
    if (!confirm('Delete this conversation? This cannot be undone.')) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const deleteUrl = `{{ url('/ai-chat/conversation') }}/${conversationId}`;

    fetch(deleteUrl, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
    }).then(async (res) => {
        if (!res.ok) {
            const errorText = await res.text();
            console.error('Conversation delete failed:', res.status, errorText);
            chatToast('Failed to delete conversation', 'error');
            return;
        }

        const data = await res.json();
        if (!data.success) {
            console.error('Conversation delete response error:', data);
            chatToast(data.message || 'Failed to delete conversation', 'error');
            return;
        }

        if (item.dataset.pinned === 'true') {
            pinnedConversationIds = pinnedConversationIds.filter(id => id !== conversationId);
            savePinnedConversationIds();
        }

        item.remove();
        if (Number(activeConversationId) === Number(conversationId)) {
            activeConversationId = null;
            renderConversationMessages([]);
        }

        chatToast('Conversation deleted', 'success');
    }).catch((e) => {
        console.error('Conversation delete exception:', e);
        chatToast('Failed to delete conversation', 'error');
    });
}

document.getElementById('newChatBtn').addEventListener('click', () => {
    activeConversationId = null;
    historyList.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
    renderConversationMessages([]);
    clearAttachPreview();
    chatInput.focus();
    chatToast('New chat started', 'info');
});

clearHistoryBtn.addEventListener('click', async () => {
    if (!confirm('Clear all chat history? This cannot be undone.')) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch('{{ route("ai-chat.clear-history") }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        });

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Clear history failed:', res.status, errorText);
            chatToast('Failed to clear history', 'error');
            return;
        }

        const data = await res.json();
        if (!data.success) {
            console.error('Clear history response error:', data);
            chatToast(data.message || 'Failed to clear history', 'error');
            return;
        }

        pinnedConversationIds = [];
        savePinnedConversationIds();

        // Force a fresh reload so cleared history does not reappear from stale state.
        window.location.reload();
    } catch (e) {
        console.error('Clear history exception:', e);
        chatToast('Failed to clear history', 'error');
    }
});

/* ============================================================
   TEXTAREA AUTO-RESIZE + CHAR COUNT
   ============================================================ */
chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    chatCharCount.textContent = this.value.length + ' / 20000';
});

// Enter to send (Shift+Enter for newline)
chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        aiChatForm.dispatchEvent(new Event('submit', { cancelable: true }));
    }
});

/* ============================================================
   QUICK PROMPT
   ============================================================ */
window.chatQuickPrompt = function(text) {
    chatInput.value = text;
    chatInput.dispatchEvent(new Event('input'));
    aiChatForm.dispatchEvent(new Event('submit'));
};

/* ============================================================
   COPY MESSAGE
   ============================================================ */
window.chatCopyMsg = function(btn) {
    const bubble = btn.closest('.chat-msg').querySelector('.msg-text');
    if (!bubble) return;
    navigator.clipboard.writeText(bubble.textContent.trim()).then(() => {
        chatToast('Copied to clipboard', 'success');
        btn.innerHTML = '<i class="fa-solid fa-check" style="color:#34d399"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1500);
    });
};

/* ============================================================
   TOAST NOTIFICATIONS
   ============================================================ */
function chatToast(message, type) {
    type = type || 'info';
    const box = document.getElementById('chatToastBox');
    const t = document.createElement('div');
    const bgs = { success: 'rgba(52,211,153,0.12)', error: 'rgba(248,113,113,0.12)', info: 'rgba(34,211,238,0.12)', warning: 'rgba(251,191,36,0.12)' };
    const bds = { success: 'rgba(52,211,153,0.3)', error: 'rgba(248,113,113,0.3)', info: 'rgba(34,211,238,0.3)', warning: 'rgba(251,191,36,0.3)' };
    const ics = { success: 'fa-solid fa-check', error: 'fa-solid fa-xmark', info: 'fa-solid fa-info', warning: 'fa-solid fa-triangle-exclamation' };
    const cls = { success: '#34d399', error: '#f87171', info: '#22d3ee', warning: '#fbbf24' };
    t.className = 'chat-toast';
    t.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:12px;background:' + bgs[type] + ';border:1px solid ' + bds[type] + ';backdrop-filter:blur(16px);max-width:380px';
    t.innerHTML = '<i class="' + ics[type] + '" style="font-size:11px;color:' + cls[type] + ';flex-shrink:0"></i><span style="font-size:12px;font-weight:500;color:rgba(226,232,240,0.7);line-height:1.4">' + chatEscHtml(message) + '</span>';
    box.appendChild(t);
    setTimeout(() => {
        t.classList.add('chat-toast-out');
        setTimeout(() => t.remove(), 260);
    }, 3500);
}

/* ============================================================
   FILE ATTACHMENT HANDLING  *** KEY FIX ***
   ============================================================ */

// Open file picker
attachBtn.addEventListener('click', () => {
    if (isSending) return;
    attachInput.click();
});

// When user selects a file
attachInput.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    // Validate extension
    const ext = getFileExtension(file.name);
    if (!ALLOWED_EXTENSIONS.includes(ext)) {
        chatToast('Unsupported file type. Allowed: PDF, DOCX, DOC, TXT, PPTX, PPT, XLSX, XLS, CSV, PNG, JPG, JPEG', 'error');
        this.value = '';
        return;
    }

    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
        chatToast('File too large. Maximum size is 20 MB.', 'error');
        this.value = '';
        return;
    }

    // Validate non-empty
    if (file.size === 0) {
        chatToast('The selected file is empty.', 'error');
        this.value = '';
        return;
    }

    // Store the file reference
    attachedFile = file;
    showAttachPreview(file);
    chatInput.focus();
});

function showAttachPreview(file) {
    const ext = getFileExtension(file.name);
    const isImage = IMAGE_EXTENSIONS.includes(ext);
    const isPdf = ext === 'pdf';
    const isDoc = ['doc', 'docx'].includes(ext);
    const isXls = ['xls', 'xlsx', 'csv'].includes(ext);
    const isPpt = ['ppt', 'pptx'].includes(ext);

    let iconClass = 'attach-file-icon default';
    let iconHtml = '<i class="fa-solid fa-file"></i>';

    if (isImage) {
        iconClass = 'attach-file-icon img';
        iconHtml = '<i class="fa-solid fa-image"></i>';
    } else if (isPdf) {
        iconClass = 'attach-file-icon pdf';
        iconHtml = '<i class="fa-solid fa-file-pdf"></i>';
    } else if (isDoc) {
        iconClass = 'attach-file-icon doc';
        iconHtml = '<i class="fa-solid fa-file-word"></i>';
    } else if (isXls) {
        iconClass = 'attach-file-icon xls';
        iconHtml = '<i class="fa-solid fa-file-excel"></i>';
    } else if (isPpt) {
        iconClass = 'attach-file-icon ppt';
        iconHtml = '<i class="fa-solid fa-file-powerpoint"></i>';
    }

    attachIcon.className = iconClass;
    attachIcon.innerHTML = iconHtml;
    attachFileName.textContent = file.name;
    attachFileSize.textContent = formatFileSize(file.size);
    attachFileStatus.textContent = 'Ready';
    attachFileStatus.className = 'attach-file-status done';
    attachProgressBar.style.display = 'none';
    attachProgressFill.style.width = '0%';
    attachProgressFill.className = 'attach-progress-fill';

    attachPreview.classList.add('visible');
}

function clearAttachPreview() {
    attachedFile = null;
    attachInput.value = '';
    attachPreview.classList.remove('visible');
    attachFileStatus.textContent = 'Ready';
    attachFileStatus.className = 'attach-file-status done';
    attachProgressBar.style.display = 'none';
    attachProgressFill.style.width = '0%';
}

function setAttachPreviewSending() {
    attachFileStatus.textContent = 'Uploading...';
    attachFileStatus.className = 'attach-file-status uploading';
    attachProgressBar.style.display = 'block';
    attachProgressFill.style.width = '0%';
    attachProgressFill.className = 'attach-progress-fill';
}

function setAttachPreviewProgress(pct) {
    attachFileStatus.textContent = pct + '%';
    attachProgressFill.style.width = pct + '%';
}

function setAttachPreviewDone() {
    attachFileStatus.textContent = 'Sent';
    attachFileStatus.className = 'attach-file-status done';
    attachProgressFill.style.width = '100%';
    attachProgressFill.className = 'attach-progress-fill done';
}

function setAttachPreviewError(msg) {
    attachFileStatus.textContent = msg || 'Failed';
    attachFileStatus.className = 'attach-file-status error';
    attachProgressFill.className = 'attach-progress-fill error';
}

attachRemoveBtn.addEventListener('click', () => {
    if (isSending) return;
    // Clear UI preview and inform server to clear active attachment context
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    fetch('{{ route("ai-chat.clear-attachment") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } }).finally(() => {
        clearAttachPreview();
    });
});

/* ============================================================
   FORM SUBMISSION  *** THE CORE FIX ***
   Uses FormData + XMLHttpRequest to send file AND text together
   in a single multipart/form-data request.
   ============================================================ */
aiChatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (isSending) return;

    const message = chatInput.value.trim();

    // Validation: need at least message or file
    if (!message && !attachedFile) {
        chatToast('Please type a message or attach a file.', 'warning');
        chatInput.focus();
        return;
    }

    // Validation: message length
    if (message.length > 20000) {
        chatToast('Message is too long. Maximum 20000 characters.', 'error');
        return;
    }

    isSending = true;
    chatSendBtn.disabled = true;
    chatSendBtn.classList.add('loading');

    // Show messages area
    chatWelcome.classList.remove('visible');
    messagesElement.classList.remove('hidden');
    messagesElement.style.display = 'flex';

    // Render user message with optional file badge
    const displayText = message || '(File attached — no text message)';
    renderMessage(displayText, 'user', attachedFile ? attachedFile.name : null);

    // Clear input immediately
    chatInput.value = '';
    chatInput.style.height = 'auto';
    chatCharCount.textContent = '0 / 20000';

    // Show typing indicator
    renderTypingIndicator();

    // If file attached, show upload progress in preview
    if (attachedFile) {
        setAttachPreviewSending();
    }

    // *** Build FormData — this is what actually sends the file ***
    const formData = new FormData();
    formData.append('message', message);
    formData.append('mode', modeSelect.value);
    if (activeConversationId) {
        formData.append('conversation_id', activeConversationId);
    }
    // *** Append the actual File object — this is the critical fix ***
    if (attachedFile) {
        formData.append('file', attachedFile, attachedFile.name);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Use XMLHttpRequest for upload progress tracking
    const xhr = new XMLHttpRequest();
    xhr.open('POST', SEND_ENDPOINT, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.timeout = 180000; // 3 min timeout for large file processing

    // Upload progress (only fires when there's a file in the body)
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable && attachedFile) {
            const pct = Math.round((e.loaded / e.total) * 100);
            setAttachPreviewProgress(pct);
        }
    });

    xhr.addEventListener('load', function() {
        // Always clean up UI state
        removeTypingIndicator();
        isSending = false;
        chatSendBtn.disabled = false;
        chatSendBtn.classList.remove('loading');

        let response;
        try {
            response = JSON.parse(xhr.responseText);
        } catch (parseErr) {
            renderMessage('The server returned an invalid response. Please try again.', 'assistant');
            chatToast('Invalid server response', 'error');
            if (attachedFile) setAttachPreviewError('Error');
            return;
        }

        if (xhr.status >= 200 && xhr.status < 300 && response.success) {
            // --- SUCCESS ---

            // Update conversation ID
            if (response.conversation) {
                const conv = response.conversation;
                if (conv.id) {
                    activeConversationId = conv.id;
                    const exists = historyList.querySelector('[data-conversation-id="' + conv.id + '"]');
                    if (!exists && conv.title) {
                        renderHistoryItem({ id: conv.id, title: conv.title, updated_at: new Date().toISOString() });
                        setActiveConversation(conv.id);
                    } else if (exists) {
                        setActiveConversation(conv.id);
                    }
                }
            }

            // Render AI response (backend returns `reply`)
            renderMessage(response.reply ?? response.response ?? '', 'assistant');

            // Clear file attachment on success
            if (attachedFile) {
                setAttachPreviewDone();
                setTimeout(() => clearAttachPreview(), 600);
            }

        } else {
            // --- FAILURE ---
            const errorMsg = response.error || response.message || 'Something went wrong. Please try again.';
            renderMessage(errorMsg, 'assistant');
            chatToast(errorMsg, 'error');

            if (attachedFile) {
                setAttachPreviewError('Failed');
                // Keep file attached so user can retry without re-selecting
            }
        }

        scrollMessages();
    });

    xhr.addEventListener('error', function() {
        removeTypingIndicator();
        isSending = false;
        chatSendBtn.disabled = false;
        chatSendBtn.classList.remove('loading');

        const msg = 'Network error. Please check your internet connection and try again.';
        renderMessage(msg, 'assistant');
        chatToast(msg, 'error');
        if (attachedFile) setAttachPreviewError('Network error');
        scrollMessages();
    });

    xhr.addEventListener('timeout', function() {
        removeTypingIndicator();
        isSending = false;
        chatSendBtn.disabled = false;
        chatSendBtn.classList.remove('loading');

        const msg = 'Request timed out. The file may be too large or the server is busy. Please try again with a smaller file.';
        renderMessage(msg, 'assistant');
        chatToast(msg, 'error');
        if (attachedFile) setAttachPreviewError('Timeout');
        scrollMessages();
    });

    // Send the FormData (contains both text fields AND the file)
    xhr.send(formData);
});

/* ============================================================
   DOCUMENTS MODAL
   ============================================================ */
const chatSidebar = document.querySelector('.chat-sidebar');
const chatSidebarOverlay = document.getElementById('chatSidebarOverlay');

function toggleChatSidebar() {
    if (!chatSidebar || !chatSidebarOverlay) return;
    const isOpen = !chatSidebar.classList.contains('mobile-open');
    chatSidebar.classList.toggle('mobile-open', isOpen);
    chatSidebarOverlay.classList.toggle('active', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
}

if (chatSidebarOverlay) {
    chatSidebarOverlay.addEventListener('click', toggleChatSidebar);
}

const documentsModal = document.getElementById('documentsModal');
const documentsList  = document.getElementById('documentsList');
const docsUseBtn     = document.getElementById('docsUseBtn');
let selectedDocId = null;

document.getElementById('useDocsBtn').addEventListener('click', async () => {
    documentsModal.classList.add('open');
    documentsList.innerHTML = '<div class="docs-empty">Loading documents...</div>';
    try {
        const res = await fetch('{{ route("ai-chat.documents") }}', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (res.ok && data.documents && data.documents.length) {
            documentsList.innerHTML = '';
            data.documents.forEach(doc => {
                const row = document.createElement('div');
                row.className = 'doc-row';
                row.dataset.docId = doc.id;
                row.innerHTML =
                    '<div class="doc-row-icon"><i class="fa-solid fa-file-lines"></i></div>' +
                    '<p class="doc-row-title">' + chatEscHtml(doc.title) + '</p>' +
                    '<span class="doc-row-meta">' + chatEscHtml(doc.type || '') + '</span>';
                row.addEventListener('click', () => {
                    documentsList.querySelectorAll('.doc-row').forEach(r => r.classList.remove('selected'));
                    row.classList.add('selected');
                    selectedDocId = doc.id;
                    docsUseBtn.disabled = false;
                });
                documentsList.appendChild(row);
            });
        } else {
            documentsList.innerHTML = '<div class="docs-empty">No documents found. Upload files to get started.</div>';
        }
    } catch (e) {
        documentsList.innerHTML = '<div class="docs-empty">Failed to load documents.</div>';
    }
});

function closeDocsModal() {
    documentsModal.classList.remove('open');
    selectedDocId = null;
    docsUseBtn.disabled = true;
}

document.getElementById('closeDocsModal').addEventListener('click', closeDocsModal);
document.getElementById('docsCancelBtn').addEventListener('click', closeDocsModal);
documentsModal.addEventListener('click', (e) => { if (e.target === documentsModal) closeDocsModal(); });

docsUseBtn.addEventListener('click', () => {
    if (!selectedDocId) return;
    const selectedRow = documentsList.querySelector('.doc-row.selected');
    const title = selectedRow?.querySelector('.doc-row-title')?.textContent || 'this document';
    chatInput.value = 'Based on the document "' + title + '", ';
    chatInput.dispatchEvent(new Event('input'));
    closeDocsModal();
    chatInput.focus();
    chatToast('Document context selected. Type your question and send.', 'info');
});

/* ============================================================
   VOICE INPUT (basic Web Speech API)
   ============================================================ */
const voiceBtn = document.getElementById('voiceBtn');
let isRecording = false;
let recognition = null;

if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    recognition.addEventListener('result', (event) => {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }
        chatInput.value = transcript;
        chatInput.dispatchEvent(new Event('input'));
    });

    recognition.addEventListener('end', () => {
        isRecording = false;
        voiceBtn.style.color = '';
        voiceBtn.style.background = '';
    });

    recognition.addEventListener('error', () => {
        isRecording = false;
        voiceBtn.style.color = '';
        voiceBtn.style.background = '';
        chatToast('Voice input failed. Please try again.', 'error');
    });

    voiceBtn.addEventListener('click', () => {
        if (isSending) return;
        if (isRecording) {
            recognition.stop();
        } else {
            isRecording = true;
            voiceBtn.style.color = '#f87171';
            voiceBtn.style.background = 'rgba(248,113,113,0.15)';
            recognition.start();
            chatToast('Listening... Speak now.', 'info');
        }
    });
} else {
    voiceBtn.style.display = 'none';
}

/* ============================================================
   INITIALIZATION
   ============================================================ */
(function init() {
    // Render initial history
    initialConversations.forEach(conv => renderHistoryItem(conv));

    // Render active conversation messages or welcome message
    renderConversationMessages(Array.isArray(initialMessages) ? initialMessages : []);

    // Focus input
    if (chatInput) chatInput.focus();
})();
</script>
@endsection