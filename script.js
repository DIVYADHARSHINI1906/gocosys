/* ═══════════════════════════════════════════════════
   GOCOSYS — script.js (Enhanced Edition)
═══════════════════════════════════════════════════ */

/* ══ CURSOR ══ */
const dot  = document.querySelector('.cursor-dot');
const ring = document.querySelector('.cursor-ring');

if (dot && ring) {
  let mx = 0, my = 0, rx = 0, ry = 0;
  window.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });

  (function animCursor() {
    rx += (mx - rx) * 0.14;
    ry += (my - ry) * 0.14;
    dot.style.left  = mx + 'px';
    dot.style.top   = my + 'px';
    ring.style.left = rx + 'px';
    ring.style.top  = ry + 'px';
    requestAnimationFrame(animCursor);
  })();

  document.querySelectorAll('a, button, .about-card, .blog-card, .workshop-card, .testi-card, .zen-node, .zen-hub').forEach(el => {
    el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
    el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
  });
}

const layers = document.querySelectorAll(".parallax");
document.addEventListener("mousemove", (e) => {
  let x = (window.innerWidth / 2 - e.pageX) / 25;
  let y = (window.innerHeight / 2 - e.pageY) / 25;
  layers.forEach((layer, index) => {
    let depth = (index + 1) * 8;
    layer.style.transform = `translate(${x * depth}px, ${y * depth}px)`;
  });
});

/* ══ MOBILE MENU ══ */
const toggleBtn = document.querySelector('.mobile-toggle');
const navLinks  = document.querySelector('.nav-links');

if (toggleBtn && navLinks) {
  toggleBtn.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    const icon = toggleBtn.querySelector('i');
    if (icon) icon.className = navLinks.classList.contains('open') ? 'bi bi-x-lg fs-5' : 'bi bi-list fs-5';
  });
  navLinks.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      navLinks.classList.remove('open');
      const icon = toggleBtn.querySelector('i');
      if (icon) icon.className = 'bi bi-list fs-5';
    });
  });
  document.addEventListener('click', e => {
    if (!toggleBtn.contains(e.target) && !navLinks.contains(e.target)) {
      navLinks.classList.remove('open');
      const icon = toggleBtn.querySelector('i');
      if (icon) icon.className = 'bi bi-list fs-5';
    }
  });
}

/* ══ HERO 3D TILT ══ */
const heroVisual = document.querySelector('.hero-visual');
const heroImg    = document.querySelector('.hero-main-img');
if (heroVisual && heroImg) {
  heroVisual.addEventListener('mousemove', e => {
    const rect = heroVisual.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const rotX = -((y - rect.height / 2) / 22);
    const rotY =  ((x - rect.width  / 2) / 22);
    heroImg.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) scale(1.02)`;
  });
  heroVisual.addEventListener('mouseleave', () => {
    heroImg.style.transform = 'rotateX(0) rotateY(0) scale(1)';
  });
}

/* ══ SCROLL REVEAL ══ */
const revealEls = document.querySelectorAll(
  '.about-card, .blog-card, .workshop-card, .testi-card, .faq-item, .company-item, .zen-info'
);
revealEls.forEach(el => el.classList.add('reveal'));
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 80);
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
revealEls.forEach(el => revealObserver.observe(el));

/* ══ ZEN WHEEL ══ */
const ZEN_TOPICS = [
  { icon: '💻', label: 'Web Dev',    tag: 'Web',        title: 'Website Development',                desc: 'Modern, responsive, and scalable websites tailored to your business needs with cutting-edge tech.' },
  { icon: '📢', label: 'Marketing',  tag: 'Boost Reach', title: 'Digital Marketing That Drives Growth', desc: 'Grow your brand with powerful, data-driven and targeted marketing strategies to reach the right audience.' },
  { icon: '📊', label: 'SEO',        tag: 'Grow Faster', title: 'SEO That Gets You Found',             desc: 'Boost your search rankings and drive high-quality organic traffic with smart, data-driven SEO strategies.' },
  { icon: '🤖', label: 'AI Agents',  tag: 'Optimize',    title: 'AI Agents & Automation',             desc: 'Automate tasks with intelligent AI chatbots and smart systems that enhance efficiency and reduce workload.' },
  { icon: '📚', label: 'Learn',      tag: 'Future',      title: 'Learning Platform',                  desc: 'Explore structured knowledge through articles, coding tutorials, and live workshops across all technologies.' }
];

function buildZenWheel() {
  const wrap = document.getElementById('zenWheelWrap');
  const svg  = document.getElementById('zenConnSvg');
  if (!wrap || !svg) return;
  const wSize = wrap.offsetWidth;
  const scale = wSize / 500;
  const r     = 195 * scale;
  const cx    = wSize / 2;
  const cy    = wSize / 2;
  const nr    = Math.max(31, 43 * scale);
  ZEN_TOPICS.forEach((t, i) => {
    const angle = (i * 360 / ZEN_TOPICS.length) - 90;
    const rad   = angle * Math.PI / 180;
    const x     = cx + r * Math.cos(rad);
    const y     = cy + r * Math.sin(rad);
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', cx); line.setAttribute('y1', cy);
    line.setAttribute('x2', x);  line.setAttribute('y2', y);
    line.classList.add('zen-conn-line');
    line.id = 'line' + i;
    svg.setAttribute('viewBox', `0 0 ${wSize} ${wSize}`);
    svg.appendChild(line);
    const node = document.createElement('div');
    node.className = 'zen-node';
    node.style.left = (x - nr) + 'px';
    node.style.top  = (y - nr) + 'px';
    node.style.width  = (nr * 2) + 'px';
    node.style.height = (nr * 2) + 'px';
    node.style.animation = `zenFloat${i % 8} ${3.5 + i * 0.3}s ease-in-out infinite ${i * 0.4}s`;
    node.innerHTML = `<div class="zen-node-icon">${t.icon}</div><div class="zen-node-label">${t.label}</div>`;
    node.addEventListener('click', () => selectZen(i));
    wrap.appendChild(node);
  });
}

function selectZen(i) {
  const t = ZEN_TOPICS[i];
  document.getElementById('zenIcon').innerHTML    = `<div style="font-size:2rem">${t.icon}</div>`;
  document.getElementById('zenTag').textContent   = t.tag;
  document.getElementById('zenTitle').textContent = t.title;
  document.getElementById('zenDesc').textContent  = t.desc;

  const hint = document.getElementById('zenHint');
  if (hint) hint.style.display = 'none';

  // Book button show
  const bookWrap  = document.getElementById('zenBookWrap');
  const bookLabel = document.getElementById('zenBookLabel');
  if (bookWrap) {
    bookWrap.style.display = 'block';
    if (bookLabel) bookLabel.textContent = 'Book ' + t.title;
  }

  // Service auto-select
  const sel = document.getElementById('consultService');
  if (sel) {
    const map = {
      'Web Dev':'Training','Marketing':'Online Learning',
      'SEO':'Training','AI Agents':'Internship','Learn':'Workshop'
    };
    const val = map[t.label];
    if (val) for (let o of sel.options) if (o.value===val) { o.selected=true; break; }
  }

  document.querySelectorAll('.zen-node').forEach((n,idx) => n.classList.toggle('active', idx===i));
  document.querySelectorAll('.zen-conn-line').forEach((l,idx) => l.classList.toggle('active', idx===i));
}

function bookService() {
  setTimeout(() => {
    document.getElementById('contact')?.scrollIntoView({ behavior:'smooth' });
  }, 100);
}

let zenTimeout;
window.addEventListener('resize', () => {
  clearTimeout(zenTimeout);
  zenTimeout = setTimeout(() => {
    const wrap = document.getElementById('zenWheelWrap');
    const svg  = document.getElementById('zenConnSvg');
    if (!wrap || !svg) return;
    svg.innerHTML = '';
    document.querySelectorAll('.zen-node').forEach(n => n.remove());
    buildZenWheel();
  }, 200);
});

/* ══ NAVBAR SCROLL EFFECT ══ */
const navbar = document.querySelector('.navbar-custom');
window.addEventListener('scroll', () => {
  if (navbar) navbar.style.background = window.scrollY > 60 ? 'rgba(5,6,15,.92)' : 'rgba(5,6,15,.75)';
});

/* ══ INIT ══ */
buildZenWheel();

/* ══════════════════════════════════════════════════
   CONTACT FORM — Book Free Consultation
   → POST to api_consultation.php?action=book
══════════════════════════════════════════════════ */
const consultBtn = document.getElementById('consultBtn');
const consultMsg = document.getElementById('consultMsgBox');

function showConsultMsg(text, ok) {
  if (!consultMsg) return;
  consultMsg.innerHTML = `
    <div style="padding:12px 18px;border-radius:10px;font-size:.88rem;font-weight:500;
      background:${ok ? 'rgba(76,175,80,.12)' : 'rgba(244,67,54,.12)'};
      border:1px solid ${ok ? 'rgba(76,175,80,.35)' : 'rgba(244,67,54,.35)'};
      color:${ok ? '#a5d6a7' : '#ef9a9a'};">
      ${ok ? '✅' : '❌'} ${text}
    </div>`;
}

if (consultBtn) {
  consultBtn.addEventListener('click', async () => {
    const name    = document.getElementById('consultName')?.value.trim()    || '';
    const email   = document.getElementById('consultEmail')?.value.trim()   || '';
    const phone   = document.getElementById('consultPhone')?.value.trim()   || '';
    const service = document.getElementById('consultService')?.value        || '';
    const message = document.getElementById('consultMessage')?.value.trim() || '';

    if (!name)                       return showConsultMsg('பெயர் உள்ளிடவும் (Name required)', false);
    if (!email || !email.includes('@')) return showConsultMsg('சரியான Email தேவை', false);
    if (!service)                    return showConsultMsg('Area of Interest தேர்ந்தெடுக்கவும்', false);
    if (!message)                    return showConsultMsg('உங்கள் goals பற்றி சொல்லுங்க', false);

    consultBtn.disabled = true;
    consultBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

    try {
      const res  = await fetch('api_consultation.php?action=book', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, phone, service_type: service, message })
      });
      const data = await res.json();

      if (data.success) {
        showConsultMsg(data.message || 'Consultation booked successfully!', true);
        consultBtn.innerHTML = '<i class="bi bi-check-lg"></i> Booked!';
        consultBtn.style.background = 'linear-gradient(135deg,#4caf50,#81c784)';
        ['consultName','consultEmail','consultPhone','consultMessage'].forEach(id => {
          const el = document.getElementById(id); if (el) el.value = '';
        });
        const sel = document.getElementById('consultService');
        if (sel) sel.selectedIndex = 0;
      } else {
        showConsultMsg(data.message || 'Something went wrong. Try again.', false);
        consultBtn.disabled = false;
        consultBtn.innerHTML = '<i class="bi bi-calendar-check-fill"></i> Book Free Consultation Now';
      }
    } catch (err) {
      showConsultMsg('Server error: ' + err.message, false);
      consultBtn.disabled = false;
      consultBtn.innerHTML = '<i class="bi bi-calendar-check-fill"></i> Book Free Consultation Now';
    }
  });
}

/* ══════════════════════════════════════════════════
   NEWSLETTER — Footer Subscribe
   → POST to api_newsletter.php
══════════════════════════════════════════════════ */
(function initNewsletter() {
  // Add IDs to the footer newsletter elements if they don't have them
  const allInputs = document.querySelectorAll('input[placeholder="Your email"]');
  const allBtns   = document.querySelectorAll('button');
  let nlInput = null, nlBtn = null;

  // Find the subscribe button in footer
  allBtns.forEach(btn => {
    if (btn.textContent.trim() === 'Subscribe') {
      nlBtn = btn;
      nlInput = btn.closest('.d-flex')?.querySelector('input[type="email"]');
    }
  });

  if (!nlBtn || !nlInput) return;

  nlBtn.addEventListener('click', async () => {
    const email = nlInput.value.trim();
    if (!email || !email.includes('@')) {
      nlInput.style.borderColor = 'rgba(244,67,54,.5)';
      setTimeout(() => nlInput.style.borderColor = '', 1500);
      return;
    }
    nlBtn.disabled = true;
    nlBtn.textContent = 'Subscribing...';
    try {
      const res  = await fetch('api_newsletter.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });
      const data = await res.json();
      if (data.success) {
        nlBtn.innerHTML = '<i class="bi bi-check-lg"></i> Subscribed!';
        nlBtn.style.background = 'linear-gradient(135deg,#4caf50,#81c784)';
        nlInput.value = '';
        nlInput.disabled = true;
      } else {
        nlBtn.textContent = data.message || 'Already subscribed!';
        nlBtn.disabled = false;
      }
    } catch (e) {
      nlBtn.textContent = 'Error. Retry';
      nlBtn.disabled = false;
    }
  });
})();
