<?php
require_once __DIR__ . '/../config.php';
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$operating_hours = get_setting($pdo, 'operating_hours', '');
// Display texts — configurable via Admin > Settings (no more hard-coded)
$welcome_msg = get_setting($pdo, 'display_welcome_message', 'Welcome! Please wait for your number to be called.');
$footer_msg  = get_setting($pdo, 'display_footer_message', 'Please remain seated and wait for your number to be called. Thank you.');
$voice_tpl   = get_setting($pdo, 'display_voice_message', 'Attention please. Ticket {number}. {name}. Please proceed to the counter.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Queue Display - <?php echo htmlspecialchars($system_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --navy-900: #0f172a;
    --navy-800: #1e293b;
    --navy-700: #334155;
    --accent:   #16a34a;
    --accent-soft: rgba(22,163,74,0.15);
    --white:    #ffffff;
    --gray-300: #cbd5e1;
    --gray-400: #94a3b8;
    --gray-500: #64748b;
  }

  body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: var(--navy-900);
    color: var(--white);
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* ── HEADER ── */
  .display-head {
    background: var(--navy-800);
    border-bottom: 3px solid var(--accent);
    padding: 22px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .head-brand {
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .head-logo {
    width: 46px; height: 46px;
    background: var(--accent);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.25rem;
    flex-shrink: 0;
  }
  .head-name {
    font-weight: 800;
    font-size: 1.45rem;
    color: var(--white);
    letter-spacing: 0.5px;
  }
  .head-name .ltr {
    display: inline-block;
    animation: headJump 1.9s ease-in-out infinite;
    animation-delay: calc(var(--i) * 0.07s);
    will-change: transform;
  }
  .head-name .ltr.sp { width: 0.34em; }
  @keyframes headJump {
    0%, 60%, 100% { transform: translateY(0); }
    30%           { transform: translateY(-26%) scaleY(1.04); }
    45%           { transform: translateY(0) scaleY(0.97); }
  }
  @media (prefers-reduced-motion: reduce) {
    .head-name .ltr { animation: none; }
  }
  .head-sub {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--gray-400);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-top: 2px;
  }
  .head-right {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .head-hours {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--gray-300);
    font-size: 0.85rem;
    font-weight: 600;
    padding: 8px 16px;
    background: var(--navy-700);
    border-radius: 8px;
  }
  .head-hours i { color: var(--accent); }
  .head-sound {
    border: 1px solid var(--navy-700);
    background: var(--navy-700);
    border-radius: 8px;
    width: 42px; height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-300);
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.15s;
  }
  .head-sound:hover { border-color: var(--accent); color: var(--accent); }
  .head-sound.off { color: var(--gray-500); }
  .head-clock {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 1px;
    padding: 6px 16px;
    background: var(--navy-700);
    border-radius: 8px;
    line-height: 1.25;
  }
  .head-clock .hc-time {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--white);
    letter-spacing: 1px;
    font-variant-numeric: tabular-nums;
  }
  .head-clock .hc-date {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--gray-400);
    letter-spacing: 1px;
    text-transform: uppercase;
  }
  .head-full {
    border: 1px solid var(--navy-700);
    background: var(--navy-700);
    border-radius: 8px;
    width: 42px; height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-300);
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.15s;
  }
  .head-full:hover { border-color: var(--accent); color: var(--accent); }

  /* ── MAIN LAYOUT ── */
  .display-wrap {
    min-height: calc(100vh - 92px);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 36px 48px;
    gap: 24px;
  }
  .panels-row {
    display: grid;
    grid-template-columns: 1.4fr 1fr 0.8fr;
    gap: 22px;
    max-width: 1280px;
    margin: 0 auto;
    width: 100%;
  }
  .panel {
    background: var(--navy-800);
    border-radius: 16px;
    border: 1px solid var(--navy-700);
    padding: 42px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .panel.now { border-top: 4px solid var(--accent); }
  .panel-label {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 2.5px;
    margin-bottom: 18px;
  }
  .panel-label i { margin-right: 8px; color: var(--accent); }

  .now-serving {
    font-size: clamp(3rem, 6.8vw, 5.6rem);
    font-weight: 900;
    line-height: 1;
    color: var(--white);
    letter-spacing: 3px;
    word-break: break-word;
  }
  .now-service {
    margin-top: 16px;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--accent);
    background: var(--accent-soft);
    border-radius: 8px;
    padding: 8px 18px;
    display: inline-block;
  }
  .now-customer {
    margin-top: 12px;
    font-size: 1.35rem;
    word-break: break-word;
    font-weight: 600;
    color: var(--gray-300);
  }

  .next-ticket {
    font-size: clamp(2.2rem, 4.6vw, 3.7rem);
    font-weight: 800;
    line-height: 1.1;
    color: var(--gray-300);
    letter-spacing: 2px;
    word-break: break-word;
  }
  .next-service {
    margin-top: 12px;
    font-size: 1rem;
    color: var(--gray-400);
  }
  .next-customer {
    margin-top: 8px;
    font-size: 1.15rem;
    word-break: break-word;
    font-weight: 600;
    color: var(--gray-500);
  }

  .waiting-count {
    font-size: clamp(3rem, 6.5vw, 5.6rem);
    font-weight: 900;
    line-height: 1;
    color: var(--accent);
  }
  .waiting-label {
    margin-top: 12px;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--gray-400);
  }

  .placeholder { color: var(--gray-500); font-weight: 800; }

  /* ── ANNOUNCEMENT BAR (professional) ── */
  .ann-bar {
    display: none;
    max-width: 1280px;
    width: 100%;
    margin: 0 auto;
    background: var(--navy-800);
    border: 1px solid var(--navy-700);
    border-left: 6px solid var(--accent);
    border-radius: 12px;
    padding: 20px 30px;
    align-items: center;
    gap: 18px;
    box-shadow: 0 0 0 1px rgba(22,163,74,0.08), 0 8px 32px rgba(22,163,74,0.12);
  }
  .ann-bar.show { display: flex; animation: annPop 0.5s cubic-bezier(0.22, 1, 0.36, 1); }
  @keyframes annPop {
    from { opacity: 0; transform: translateY(16px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }
  @media (prefers-reduced-motion: reduce) {
    .ann-bar.show { animation: none; }
  }
  .ann-bar .ann-icon {
    color: var(--accent);
    font-size: 1.9rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    animation: annPulse 2.2s ease-in-out infinite;
  }
  @keyframes annPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50%      { transform: scale(1.12); opacity: 0.75; }
  }
  .ann-bar .ann-tag {
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--accent);
    background: var(--accent-soft);
    padding: 6px 14px;
    border-radius: 8px;
    flex-shrink: 0;
    white-space: nowrap;
  }
  .ann-bar .ann-msg {
    flex: 1;
    min-width: 0;
    font-size: clamp(1.2rem, 2vw, 1.7rem);
    font-weight: 700;
    color: var(--white);
    line-height: 1.4;
    word-break: break-word;
    text-align: left;
  }

  /* ── TICKER ── */
  .ticker {
    max-width: 1280px;
    width: 100%;
    margin: 0 auto;
    background: var(--navy-800);
    border: 1px solid var(--navy-700);
    border-radius: 10px;
    padding: 17px 0;
    color: var(--gray-300);
    font-size: 1.2rem;
    font-weight: 600;
    min-height: 64px;
    display: flex;
    align-items: center;
    overflow: hidden;
  }
  .ticker-track {
    display: inline-flex;
    white-space: nowrap;
    animation: tickerScroll 18s linear infinite;
    will-change: transform;
  }
  .ticker-track .chunk {
    padding-right: 90px;
    color: var(--gray-300);
  }
  @keyframes tickerScroll {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
  }
  @media (prefers-reduced-motion: reduce) {
    .ticker-track { animation: none; }
  }

  /* ── FOOTER ── */
  .display-foot {
    text-align: center;
    font-size: 0.85rem;
    color: var(--gray-500);
    font-weight: 500;
    letter-spacing: 1px;
  }

  @media (max-width: 900px) {
    .panels-row { grid-template-columns: 1fr; gap: 14px; }
    .display-wrap { padding: 20px; gap: 16px; }
    .display-head { padding: 16px 20px; }
    .head-hours { display: none; }
  }
</style>
</head>
<body>

<header class="display-head">
  <div class="head-brand">
    <div class="head-logo"><i class="bi bi-ticket-perforated-fill"></i></div>
    <div>
      <div class="head-name"><?php
        // Per-letter spans so the brand name does a gentle jumping wave
        $chars = preg_split('//u', $system_name, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($chars as $i => $ch) {
            if ($ch === ' ') {
                echo '<span class="ltr sp">&nbsp;</span>';
            } else {
                echo '<span class="ltr" style="--i:' . $i . '">' . htmlspecialchars($ch) . '</span>';
            }
        }
      ?></div>
      <div class="head-sub">Queue Monitor</div>
    </div>
  </div>
  <div class="head-right">
    <?php if ($operating_hours): ?>
      <span class="head-hours"><i class="bi bi-clock"></i><?php echo htmlspecialchars($operating_hours); ?></span>
    <?php endif; ?>
    <div class="head-clock" id="headClock">
      <span class="hc-time" id="hcTime">—</span>
      <span class="hc-date" id="hcDate">—</span>
    </div>
    <button class="head-sound" id="soundBtn" title="Toggle voice announcement"><i class="bi bi-volume-up-fill"></i></button>
    <button class="head-full" id="fullBtn" title="Toggle fullscreen (TV mode)"><i class="bi bi-arrows-fullscreen"></i></button>
  </div>
</header>

<div class="display-wrap">
  <div class="panels-row">
    <div class="panel now">
      <div class="panel-label"><i class="bi bi-broadcast"></i>Now Serving</div>
      <div class="now-serving" id="nowServing">—</div>
      <div class="now-service" id="nowService" style="display:none;"></div>
      <div class="now-customer" id="nowCustomer" style="display:none;"><i class="bi bi-person"></i><span></span></div>
    </div>
    <div class="panel">
      <div class="panel-label"><i class="bi bi-hourglass-split"></i>Next in Line</div>
      <div class="next-ticket" id="nextTicket">—</div>
      <div class="next-service" id="nextService" style="display:none;"></div>
      <div class="next-customer" id="nextCustomer" style="display:none;"><i class="bi bi-person"></i><span></span></div>
    </div>
    <div class="panel">
      <div class="panel-label"><i class="bi bi-people"></i>Waiting</div>
      <div class="waiting-count" id="waitingCount">0</div>
      <div class="waiting-label">in queue</div>
    </div>
  </div>

  <div class="ann-bar" id="annBanner">
    <span class="ann-icon"><i class="bi bi-megaphone-fill"></i></span>
    <span class="ann-tag">Announcement</span>
    <span class="ann-msg" id="annBannerText"></span>
  </div>

  <div class="ticker">
    <div class="ticker-track" id="tickerTrack">
      <span class="chunk"><?php echo htmlspecialchars($welcome_msg); ?></span>
      <span class="chunk"><?php echo htmlspecialchars($welcome_msg); ?></span>
    </div>
  </div>

  <div class="display-foot"><?php echo htmlspecialchars($footer_msg); ?></div>
</div>

<script>
async function api(url) {
    const res = await fetch(url);
    const data = await res.json().catch(() => ({}));
    return data;
}

// ── CONFIGURABLE TEXTS (injected from settings) ──
const WELCOME_MSG = <?php echo json_encode($welcome_msg); ?>;
const VOICE_TPL   = <?php echo json_encode($voice_tpl); ?>;

// ── VOICE ANNOUNCEMENT ──
let soundOn = true;
let lastAnnouncedId = null;
let lastAnnouncedName = null;
let lastAnnText = null; // tracks shown TV announcement (fix: was undefined setAnn.last)
let voices = [];
let audioCtx = null;

const soundBtn = document.getElementById('soundBtn');

// Unlock audio on any first user gesture (click/touch/keypress anywhere)
function unlockAudio() {
    if (!audioCtx) {
        try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch (e) {}
    }
    if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
    // Warm-up the speech engine so later speaks fire immediately
    try {
        if (typeof speechSynthesis !== 'undefined') {
            speechSynthesis.cancel();
            const warm = new SpeechSynthesisUtterance('');
            warm.volume = 0;
            speechSynthesis.speak(warm);
        }
    } catch (e) {}
}
document.addEventListener('pointerdown', unlockAudio, { once: true });
document.addEventListener('keydown', unlockAudio, { once: true });

soundBtn.addEventListener('click', () => {
    soundOn = !soundOn;
    soundBtn.classList.toggle('off', !soundOn);
    soundBtn.innerHTML = soundOn
        ? '<i class="bi bi-volume-up-fill"></i>'
        : '<i class="bi bi-volume-mute-fill"></i>';
    unlockAudio();
    if (soundOn) {
        announce('Voice announcements are now on.');
    }
});

function announce(text, rate) {
    speak(text, rate);
}

function loadVoices() {
    voices = speechSynthesis ? speechSynthesis.getVoices() : [];
}
function refreshVoices() {
    loadVoices();
    // Re-query shortly after; Chrome loads voices async
    setTimeout(loadVoices, 250);
    setTimeout(loadVoices, 1000);
}
loadVoices();
if (typeof speechSynthesis !== 'undefined') {
    speechSynthesis.onvoiceschanged = refreshVoices;
    // Re-arm periodically to defeat Chrome's 15s speech pause bug
    setInterval(() => { try { speechSynthesis.resume(); } catch (e) {} }, 10000);
}

function pickVoice() {
    if (voices.length === 0) loadVoices();
    // Filipino/Tagalog first, then English-Philippines, then neutral English
    return voices.find(v => /fil|tagalog|^tl|^en[-_]PH|english.{0,3}philippines/i.test(v.lang + ' ' + v.name))
        || voices.find(v => /^en[-_]PH/i.test(v.lang) || /philippines/i.test(v.name))
        || voices.find(v => /^en/i.test(v.lang))
        || voices[0]
        || null;
}

// Make names easier for TTS: no all-caps shouter, no commas (avoids weird pauses)
function speakName(name) {
    if (!name) return '';
    return name
        .replace(/,/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

// Read ticket number digit-by-digit so TTS says "SFI zero nine two four zero zero one"
function speakNumber(num) {
    if (!num) return '';
    const m = num.match(/[A-Z]{2,4}[-]?(?:(\d{4}[-])?(\d{2})(\d{2})[-](\d{3})|-(\d{3}))/);
    if (m) {
        const tail = m[4] || m[5]; // the running-number part (e.g. 001)
        const short = m[2] && m[3] ? m[2] + m[3] : ''; // MMDD for old format
        const digit = s => (s || '').split('').join(' ');
        const prefix = (num.match(/^[^0-9]+/) || [''])[0].replace(/[-]/g, '');
        return (prefix + ' ' + (short ? digit(short) : '') + ' ' + tail.split('').join(' ')).replace(/\s+/g, ' ').trim();
    }
    return num.split('').join(' ');
}

function announceNew(name, number, service) {
    // Voice script is configurable (Admin > Settings) — {number}, {name}, {service} placeholders
    const text = VOICE_TPL
        .replace(/\{number\}/g, number ? speakNumber(number) : '')
        .replace(/\{name\}/g, name ? speakName(name) : '')
        .replace(/\{service\}/g, service || '')
        .replace(/\s+/g, ' ')
        .trim();
    speak(text || 'Attention please.');
}

// Short "ding" chime before the voice, so the room notices even on silent-voice setups
function beep() {
    if (!audioCtx) return;
    const ctx = audioCtx;
    const o = ctx.createOscillator();
    const g = ctx.createGain();
    o.type = 'sine';
    o.frequency.value = 880;
    g.gain.setValueAtTime(0.0001, ctx.currentTime);
    g.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + 0.01);
    g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.7);
    o.connect(g);
    g.connect(ctx.destination);
    o.start();
    o.stop(ctx.currentTime + 0.8);
}

function speak(text, rate = 0.95) {
    if (!soundOn) return;
    if (typeof speechSynthesis === 'undefined') return;
    beep();
    // Do NOT call cancel() right before speak() — known Chrome silent-failure bug
    const u = new SpeechSynthesisUtterance(text);
    u.rate = rate;
    u.pitch = 1.05;
    u.volume = 1;
    const v = pickVoice();
    if (v) u.voice = v;
    u.onerror = () => {};
    speechSynthesis.speak(u);
}

// ── AUTO-FIT: shrink font until the whole text fits — walang putol na text ──
function fitText(el) {
    if (!el) return;
    el.style.fontSize = '';
    let guard = 0;
    while (guard++ < 12 && (el.scrollWidth > el.clientWidth + 1 || el.scrollHeight > el.clientHeight + 1)) {
        const cur = parseFloat(getComputedStyle(el).fontSize);
        if (cur <= 14) break;
        el.style.fontSize = (cur - 2) + 'px';
    }
}

// ── LIVE CLOCK (date + time sa header) ──
function tickClock() {
    const now = new Date();
    const t = document.getElementById('hcTime');
    const d = document.getElementById('hcDate');
    if (t) t.textContent = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    if (d) d.textContent = now.toLocaleDateString('en-PH', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
}
setInterval(tickClock, 1000);
tickClock();

// ── FULLSCREEN (TV mode) ──
document.getElementById('fullBtn').addEventListener('click', () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen();
    }
});

function setVal(id, val, showSel) {
    const el = document.getElementById(id);
    if (val) {
        el.textContent = val;
        el.classList.remove('placeholder');
    } else {
        el.textContent = '—';
        el.classList.add('placeholder');
    }
    if (showSel) showSel.style.display = val ? 'inline-block' : 'none';
}

function setTicker(text) {
    if (setTicker.last === text) return;
    setTicker.last = text;
    const track = document.getElementById('tickerTrack');
    track.querySelectorAll('.chunk').forEach(c => c.textContent = text);
    // Scale speed to message length (longer text = longer loop), min 14s
    track.style.animationDuration = Math.max(14, text.length * 0.45) + 's';
    // Restart the animation cleanly when the message changes
    track.style.animation = 'none';
    void track.offsetWidth;
    track.style.animation = '';
}

async function updateDisplay() {
    const data = await api('/quewing_system/api/tickets/queue');
    const current = data.current;
    const tickets = data.tickets || [];

    // NOW SERVING
    const nowServing = document.getElementById('nowServing');
    const nowService = document.getElementById('nowService');
    const nowCustomer = document.getElementById('nowCustomer');
    const currentlyCalled = current && current.ticket_number;

    // Announce when a new ticket is called
    if (currentlyCalled) {
        const name = current.customer_name || '';
        if (lastAnnouncedId !== current.id || lastAnnouncedName !== name) {
            lastAnnouncedId = current.id;
            lastAnnouncedName = name;
            announceNew(name, current.ticket_number, current.service_name);
        }
    } else {
        lastAnnouncedId = null;
        lastAnnouncedName = null;
    }

    if (currentlyCalled) {
        setVal('nowServing', current.ticket_number);
        setVal('nowService', current.service_name || '', nowService);
        if (current.customer_name) {
            nowCustomer.querySelector('span').textContent = current.customer_name;
            nowCustomer.style.display = 'block';
        } else {
            nowCustomer.style.display = 'none';
        }
    } else {
        nowServing.textContent = '—';
        nowServing.classList.add('placeholder');
        nowService.style.display = 'none';
        nowCustomer.style.display = 'none';
    }

    // NEXT (first ticket that is NOT the current serving one)
    const next = tickets.find(t => !current || t.id !== current.id) || null;
    const nextTicket = document.getElementById('nextTicket');
    const nextService = document.getElementById('nextService');
    const nextCustomer = document.getElementById('nextCustomer');
    if (next && next.ticket_number) {
        setVal('nextTicket', next.ticket_number);
        setVal('nextService', next.service_name || '', nextService);
        if (next.customer_name) {
            nextCustomer.querySelector('span').textContent = next.customer_name;
            nextCustomer.style.display = 'block';
        } else {
            nextCustomer.style.display = 'none';
        }
    } else {
        nextTicket.textContent = '—';
        nextTicket.classList.add('placeholder');
        nextService.style.display = 'none';
        nextCustomer.style.display = 'none';
    }

    // WAITING COUNT
    const waiting = tickets.filter(t => t.status === 'waiting').length;
    document.getElementById('waitingCount').textContent = waiting;

    // ANNOUNCEMENT BAR (admin-controlled, professional style)
    const ann = data.announcement || { active: false, text: '' };
    const banner = document.getElementById('annBanner');
    const bannerText = document.getElementById('annBannerText');
    if (ann.active && ann.text) {
        if (lastAnnText !== ann.text) {
            lastAnnText = ann.text;
            bannerText.textContent = ann.text;
            banner.classList.remove('show');
            void banner.offsetWidth; // restart entrance animation
            banner.classList.add('show');
        }
    } else {
        lastAnnText = null;
        banner.classList.remove('show');
    }

    // AUTO-FIT: ticket numbers at pangalan, laging buo kahit mahaba
    fitText(document.getElementById('nowServing'));
    fitText(document.getElementById('nextTicket'));

    // TICKER — continuous leftward marquee (welcome / serving / next)
    if (current && current.ticket_number) {
        setTicker('Now serving ' + current.ticket_number + ' — please proceed to the counter');
    } else if (next && next.ticket_number) {
        setTicker('Next: ' + next.ticket_number);
    } else if (waiting > 0) {
        setTicker(waiting + ' person(s) waiting in line');
    } else {
        setTicker(WELCOME_MSG);
    }
}

setInterval(updateDisplay, 2000);
updateDisplay();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
