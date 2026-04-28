/* ═══════════════════════════════════════════════════
   GOCOSYS — blog-script.js  (Dynamic Rendering)
═══════════════════════════════════════════════════ */

/* ── Cursor ── */
const dot  = document.querySelector('.cursor-dot');
const ring = document.querySelector('.cursor-ring');
if (dot && ring) {
  let mx=0,my=0,rx=0,ry=0;
  window.addEventListener('mousemove', e => { mx=e.clientX; my=e.clientY; });
  (function anim(){ rx+=(mx-rx)*.14; ry+=(my-ry)*.14; dot.style.left=mx+'px'; dot.style.top=my+'px'; ring.style.left=rx+'px'; ring.style.top=ry+'px'; requestAnimationFrame(anim); })();
  document.addEventListener('mouseover', e => {
    if (e.target.closest('a,button,.blog-card,.featured-card,.filter-btn')) document.body.classList.add('cursor-hover');
    else document.body.classList.remove('cursor-hover');
  });
}

/* ── Particles ── */
(function(){
  const c=document.getElementById('particles'); if(!c)return;
  const cols=['#90CAF9','#c8942a','#FFE246','#ce93d8','#80cbc4'];
  for(let i=0;i<28;i++){
    const p=document.createElement('div'); p.className='particle';
    const s=Math.random()*4+2;
    Object.assign(p.style,{ width:s+'px', height:s+'px', left:Math.random()*100+'vw', top:Math.random()*100+'vh', background:cols[Math.floor(Math.random()*cols.length)], animationDuration:(Math.random()*8+5)+'s', animationDelay:(Math.random()*6)+'s', borderRadius:Math.random()>.5?'50%':'2px' });
    c.appendChild(p);
  }
})();

/* ── Mobile Menu ── */
const toggleBtn=document.getElementById('mobileToggle'), navLinks=document.getElementById('navLinks');
if(toggleBtn&&navLinks){
  toggleBtn.addEventListener('click',()=>{ navLinks.classList.toggle('open'); const ic=toggleBtn.querySelector('i'); if(ic) ic.className=navLinks.classList.contains('open')?'bi bi-x-lg fs-5':'bi bi-list fs-5'; });
  navLinks.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{ navLinks.classList.remove('open'); const ic=toggleBtn.querySelector('i'); if(ic) ic.className='bi bi-list fs-5'; }));
  document.addEventListener('click',e=>{ if(!toggleBtn.contains(e.target)&&!navLinks.contains(e.target)){ navLinks.classList.remove('open'); const ic=toggleBtn.querySelector('i'); if(ic) ic.className='bi bi-list fs-5'; }});
}

/* ── Navbar scroll ── */
const navbar=document.querySelector('.navbar-custom');
window.addEventListener('scroll',()=>{ if(navbar) navbar.style.background=window.scrollY>60?'rgba(5,6,15,.95)':'rgba(5,6,15,.75)'; });

/* ── Scroll reveal ── */
function observeReveal(){
  const els=document.querySelectorAll('.reveal:not(.visible)');
  const obs=new IntersectionObserver(entries=>{
    entries.forEach((e,i)=>{ if(e.isIntersecting){ setTimeout(()=>e.target.classList.add('visible'),i*90); obs.unobserve(e.target); }});
  },{threshold:.1,rootMargin:'0px 0px -40px 0px'});
  els.forEach(el=>obs.observe(el));
}

/* ── Category tag colours ── */
function tagClass(cat){
  const m={ai:'blog-tag-ai',seo:'blog-tag-seo',marketing:'blog-tag-marketing',career:'blog-tag-career'};
  return m[cat]||'';
}
function thumbClass(cat){
  const m={ai:'thumb-ai',seo:'thumb-seo',marketing:'thumb-marketing',career:'thumb-career',web:'thumb-web'};
  return m[cat]||'thumb-web';
}
function thumbGlowClass(cat){
  const m={ai:'thumb-glow-purple',seo:'thumb-glow-gold',marketing:'thumb-glow-pink',career:'thumb-glow-teal',web:'thumb-glow-blue'};
  return m[cat]||'thumb-glow-blue';
}
const ICONS={ai:'bi-robot',seo:'bi-graph-up-arrow',marketing:'bi-megaphone-fill',career:'bi-briefcase-fill',web:'bi-code-slash'};

/* ── Build Featured Card ── */
function buildFeatured(){
  const fc=document.getElementById('featuredCard'); if(!fc)return;
  const art=ARTICLES.find(a=>a.featured)||ARTICLES[0];
  fc.className='featured-card reveal';
  fc.dataset.category=art.category;
  fc.innerHTML=`
    <div class="featured-left">
      <div class="featured-thumb">
        <div class="thumb-glow ${thumbGlowClass(art.category)}"></div>
        <div class="thumb-icon-wrap"><i class="bi ${ICONS[art.category]||'bi-file-text'} thumb-icon"></i></div>
        <div class="scan-line"></div>
      </div>
    </div>
    <div class="featured-right">
      <div class="d-flex gap-2 flex-wrap mb-3">
        <span class="blog-tag ${tagClass(art.category)}">${art.categoryLabel}</span>
        <span class="blog-tag-date"><i class="bi bi-calendar3"></i> ${art.date}</span>
      </div>
      <h2 class="featured-title">${art.title}</h2>
      <p class="featured-desc">${art.excerpt}</p>
      <div class="featured-meta">
        <div class="author-chip">
          <div class="author-av" style="background:${art.authorColor}">${art.authorInitials}</div>
          <div><div class="author-name">${art.author}</div><div class="author-role">${art.readTime} read</div></div>
        </div>
        <a href="article.html?id=${art.id}" class="btn-read">Read Article <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>`;
  setTimeout(()=>observeReveal(),50);
}

/* ── Build Blog Grid ── */
function buildGrid(filter='all', query=''){
  const grid=document.getElementById('blogGrid'); if(!grid)return;
  const featured=document.getElementById('featuredSection');

  // Filter articles (skip featured)
  let arts=ARTICLES.filter(a=>!a.featured);
  if(filter!=='all') arts=arts.filter(a=>a.category===filter);
  if(query) arts=arts.filter(a=>(a.title+' '+a.excerpt+' '+a.categoryLabel).toLowerCase().includes(query.toLowerCase()));

  // Show/hide featured
  if(featured){
    const featArt=ARTICLES.find(a=>a.featured)||ARTICLES[0];
    const showFeat = (filter==='all'||featArt.category===filter) && (!query||(featArt.title+' '+featArt.excerpt).toLowerCase().includes(query.toLowerCase()));
    featured.style.display = showFeat ? '' : 'none';
  }

  // Count
  const total = arts.length + (featured && featured.style.display!=='none' ? 1 : 0);
  const pc=document.getElementById('postCount');
  if(pc) pc.textContent=`Showing ${total} post${total!==1?'s':''}`;

  // No results
  const nr=document.getElementById('noResults');
  if(nr) nr.style.display = arts.length===0 ? 'flex':'none';

  grid.innerHTML='';

  arts.forEach((art,i)=>{
    const col=document.createElement('div');
    col.className='col-md-6 col-lg-4 blog-card-wrap reveal';
    col.dataset.category=art.category;
    col.innerHTML=`
      <a href="article.html?id=${art.id}" class="text-decoration-none d-block h-100">
        <div class="blog-card glass-card h-100" style="cursor:pointer">
          <div class="blog-thumb ${thumbClass(art.category)}">
            <div class="thumb-glow ${thumbGlowClass(art.category)}"></div>
            <i class="bi ${ICONS[art.category]||'bi-file-text'} thumb-icon-sm"></i>
            <div class="scan-line"></div>
          </div>
          <div class="blog-body">
            <div class="d-flex gap-2 mb-2 flex-wrap">
              <span class="blog-tag ${tagClass(art.category)}">${art.categoryLabel}</span>
              <span class="blog-tag-date"><i class="bi bi-calendar3"></i> ${art.date}</span>
            </div>
            <h5 class="blog-title">${art.title}</h5>
            <p class="blog-excerpt">${art.excerpt}</p>
            <div class="blog-footer">
              <div class="author-chip-sm">
                <div class="author-av-sm" style="background:${art.authorColor}${art.category==='seo'?';color:#000':''}">${art.authorInitials}</div>
                <span>${art.author.split(' ')[0]} · ${art.readTime}</span>
              </div>
              <span class="read-link">Read <i class="bi bi-arrow-right-short"></i></span>
            </div>
          </div>
        </div>
      </a>`;
    grid.appendChild(col);
  });

  setTimeout(()=>observeReveal(),50);
}

/* ── Search ── */
const searchInput=document.getElementById('searchInput'), searchClear=document.getElementById('searchClear');
let activeFilter='all';
if(searchInput){
  searchInput.addEventListener('input',()=>{
    const q=searchInput.value.trim();
    if(searchClear) searchClear.style.display=q?'block':'none';
    buildGrid(activeFilter,q);
  });
}
if(searchClear){
  searchClear.addEventListener('click',()=>{ searchInput.value=''; searchClear.style.display='none'; buildGrid(activeFilter,''); searchInput.focus(); });
}

/* ── Filter ── */
document.querySelectorAll('.filter-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter=btn.dataset.filter;
    const q=searchInput?searchInput.value.trim():'';
    buildGrid(activeFilter,q);
  });
});

/* ── Newsletter ── */
const nlBtn=document.getElementById('nlBtn'), nlEmail=document.getElementById('nlEmail');
if(nlBtn&&nlEmail){
  nlBtn.addEventListener('click',()=>{
    const e=nlEmail.value.trim();
    if(!e||!e.includes('@')){ nlEmail.style.borderColor='rgba(244,67,54,.5)'; setTimeout(()=>nlEmail.style.borderColor='',1500); return; }
    nlBtn.innerHTML='<i class="bi bi-check-lg"></i> Subscribed!';
    nlBtn.style.background='linear-gradient(135deg,#4caf50,#81c784)';
    nlEmail.value=''; nlEmail.disabled=true; nlBtn.disabled=true;
  });
}

/* ── INIT ── */
buildFeatured();
buildGrid();