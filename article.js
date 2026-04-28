/* ═══════════════════════════════════════════════════
   GOCOSYS — article-script.js
═══════════════════════════════════════════════════ */

/* ── Cursor ── */
const dot=document.querySelector('.cursor-dot'), ring=document.querySelector('.cursor-ring');
if(dot&&ring){
  let mx=0,my=0,rx=0,ry=0;
  window.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;});
  (function anim(){rx+=(mx-rx)*.14;ry+=(my-ry)*.14;dot.style.left=mx+'px';dot.style.top=my+'px';ring.style.left=rx+'px';ring.style.top=ry+'px';requestAnimationFrame(anim);})();
  document.addEventListener('mouseover',e=>{ if(e.target.closest('a,button,.blog-card,.sidebar-card')) document.body.classList.add('cursor-hover'); else document.body.classList.remove('cursor-hover'); });
}

/* ── Particles ── */
(function(){
  const c=document.getElementById('particles'); if(!c)return;
  const cols=['#90CAF9','#c8942a','#FFE246','#ce93d8','#80cbc4'];
  for(let i=0;i<22;i++){
    const p=document.createElement('div'); p.className='particle';
    const s=Math.random()*3+2;
    Object.assign(p.style,{width:s+'px',height:s+'px',left:Math.random()*100+'vw',top:Math.random()*100+'vh',background:cols[~~(Math.random()*cols.length)],animationDuration:(Math.random()*9+5)+'s',animationDelay:(Math.random()*7)+'s',borderRadius:Math.random()>.5?'50%':'2px'});
    c.appendChild(p);
  }
})();

/* ── Mobile Menu ── */
const toggleBtn=document.getElementById('mobileToggle'), navLinks=document.getElementById('navLinks');
if(toggleBtn&&navLinks){
  toggleBtn.addEventListener('click',()=>{navLinks.classList.toggle('open');const ic=toggleBtn.querySelector('i');if(ic)ic.className=navLinks.classList.contains('open')?'bi bi-x-lg fs-5':'bi bi-list fs-5';});
  navLinks.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{navLinks.classList.remove('open');const ic=toggleBtn.querySelector('i');if(ic)ic.className='bi bi-list fs-5';}));
  document.addEventListener('click',e=>{if(!toggleBtn.contains(e.target)&&!navLinks.contains(e.target)){navLinks.classList.remove('open');const ic=toggleBtn.querySelector('i');if(ic)ic.className='bi bi-list fs-5';}});
}

/* ── Navbar scroll ── */
const navbar=document.querySelector('.navbar-custom');
window.addEventListener('scroll',()=>{if(navbar)navbar.style.background=window.scrollY>60?'rgba(5,6,15,.95)':'rgba(5,6,15,.75)';});

/* ── Reading Progress ── */
const progress=document.getElementById('readingProgress');
window.addEventListener('scroll',()=>{
  if(!progress)return;
  const sc=document.documentElement.scrollTop, h=document.documentElement.scrollHeight-window.innerHeight;
  progress.style.width=(h>0?(sc/h)*100:0)+'%';
});

/* ── Scroll reveal ── */
function observeReveal(){
  new IntersectionObserver((entries,obs)=>{
    entries.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*90);obs.unobserve(e.target);}});
  },{threshold:.1,rootMargin:'0px 0px -40px 0px'}).observe.bind;

  document.querySelectorAll('.reveal:not(.visible)').forEach((el,i)=>{
    const obs=new IntersectionObserver(([e],o)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('visible'),i*70);o.unobserve(e.target);}},{threshold:.1});
    obs.observe(el);
  });
}

/* ── Helpers ── */
const tagClass    =cat=>({ai:'blog-tag-ai',seo:'blog-tag-seo',marketing:'blog-tag-marketing',career:'blog-tag-career'}[cat]||'');
const thumbBg     =cat=>({ai:'cat-ai',seo:'cat-seo',marketing:'cat-marketing',career:'cat-career',web:'cat-web'}[cat]||'cat-web');
const thumbGlowCls=cat=>({ai:'thumb-glow-purple',seo:'thumb-glow-gold',marketing:'thumb-glow-pink',career:'thumb-glow-teal',web:'thumb-glow-blue'}[cat]||'thumb-glow-blue');
const ICONS       ={ai:'bi-robot',seo:'bi-graph-up-arrow',marketing:'bi-megaphone-fill',career:'bi-briefcase-fill',web:'bi-code-slash'};
const AUTHORS_BIO ={
  'GOCOSYS Team':'The GOCOSYS editorial team brings together experts in web development, AI, and digital marketing to deliver actionable insights for tech professionals.',
  'Rahul Kumar':'Senior Frontend Engineer with 7+ years building scalable web applications. Passionate about React, TypeScript, and modern web performance.',
  'Priya Subramanian':'SEO Strategist with a deep focus on data-driven growth, technical SEO, and AI-powered search strategies.',
  'Mohan Raj':'Machine Learning Engineer specializing in LLMs, NLP, and production AI systems.',
  'Anitha Nair':'Growth Marketer helping tech startups build sustainable organic growth through content and community.',
  'Suresh Kumar':'Career Coach who has helped 500+ students land roles at top MNCs through structured placement preparation.',
  'Divyadharshini B':'UI Developer and designer passionate about creating beautiful, accessible web interfaces.',
  'Vikram Nair':'AI Solutions Architect building enterprise-scale chatbots and automation systems.',
  'Dr. M. Rajan':'Training & Placement Officer with 15 years of experience connecting students with top tech companies.'
};

/* ── Get article ID from URL ── */
const params=new URLSearchParams(window.location.search);
const artId=parseInt(params.get('id'))||1;
const article=ARTICLES.find(a=>a.id===artId)||ARTICLES[0];

/* ── Populate Page ── */
function renderArticle(){
  // Page title
  document.title = article.title + ' – GOCOSYS Blog';

  // Breadcrumb
  const bc=document.getElementById('breadcrumbTitle');
  if(bc) bc.textContent = article.title.length>40 ? article.title.slice(0,40)+'…' : article.title;

  // Hero meta
  const meta=document.getElementById('articleMeta');
  if(meta) meta.innerHTML=`
    <span class="blog-tag ${tagClass(article.category)}">${article.categoryLabel}</span>
    <span class="blog-tag-date"><i class="bi bi-calendar3"></i> ${article.date}</span>
    <span class="blog-tag-date"><i class="bi bi-clock"></i> ${article.readTime} read</span>`;

  // Title, excerpt
  const t=document.getElementById('articleTitle'); if(t) t.textContent=article.title;
  const ex=document.getElementById('articleExcerpt'); if(ex) ex.textContent=article.excerpt;

  // Author chip
  const aChip=document.getElementById('articleAuthor');
  if(aChip) aChip.innerHTML=`
    <div class="author-av" style="background:${article.authorColor}">${article.authorInitials}</div>
    <div><div class="author-name">${article.author}</div><div class="author-role">${article.authorRole}</div></div>`;

  // Thumb banner
  const banner=document.getElementById('articleThumbBanner');
  if(banner){
    banner.classList.add(thumbBg(article.category));
    const glow=document.getElementById('thumbGlow');
    if(glow) glow.className='thumb-glow '+thumbGlowCls(article.category);
    const icon=document.getElementById('thumbIconLg');
    if(icon) icon.className='bi '+(ICONS[article.category]||'bi-file-text');
  }

  // Article body
  const body=document.getElementById('articleBody');
  if(body) body.innerHTML=article.content;

  // Tags
  const tags=document.getElementById('articleTags');
  if(tags){
    const tagList=['#GOCOSYS','#'+article.categoryLabel.replace(' ',''),'#TechBlog','#Learning'];
    tags.innerHTML=tagList.map(tg=>`<span class="article-tag-item"><i class="bi bi-tag-fill"></i>${tg}</span>`).join('');
  }

  // Author card
  const ac=document.getElementById('authorCard');
  if(ac) ac.innerHTML=`
    <div class="author-card-av" style="background:${article.authorColor}">${article.authorInitials}</div>
    <div>
      <div class="author-card-name">${article.author}</div>
      <div class="author-card-role">${article.authorRole}</div>
      <div class="author-card-bio">${AUTHORS_BIO[article.author]||'Expert contributor at GOCOSYS, sharing knowledge to help professionals grow.'}</div>
    </div>`;

  // Build TOC from h2 tags
  setTimeout(()=>{
    const h2s=document.querySelectorAll('.article-body h2');
    const toc=document.getElementById('tocList');
    if(toc&&h2s.length){
      h2s.forEach((h,i)=>{
        h.id='section-'+i;
        const li=document.createElement('li');
        li.innerHTML=`<a href="#section-${i}">${h.textContent}</a>`;
        toc.appendChild(li);
      });
      // Active TOC on scroll
      const tocLinks=toc.querySelectorAll('a');
      const observer=new IntersectionObserver(entries=>{
        entries.forEach(e=>{
          if(e.isIntersecting){
            tocLinks.forEach(l=>l.classList.remove('active'));
            const active=toc.querySelector(`a[href="#${e.target.id}"]`);
            if(active) active.classList.add('active');
          }
        });
      },{rootMargin:'-20% 0px -60% 0px'});
      h2s.forEach(h=>observer.observe(h));
    } else {
      const card=document.getElementById('tocCard');
      if(card) card.style.display='none';
    }
  },100);

  // Related articles (same category, not same id)
  const relDiv=document.getElementById('relatedArticles');
  if(relDiv){
    const related=ARTICLES.filter(a=>a.id!==article.id&&a.category===article.category).slice(0,3);
    if(related.length===0){
      const allOther=ARTICLES.filter(a=>a.id!==article.id).slice(0,3);
      renderRelated(allOther,relDiv);
    } else renderRelated(related,relDiv);
  }

  // More articles grid (different from current)
  const moreGrid=document.getElementById('moreArticlesGrid');
  if(moreGrid){
    const more=ARTICLES.filter(a=>a.id!==article.id).slice(0,3);
    more.forEach(a=>{
      const col=document.createElement('div');
      col.className='col-md-6 col-lg-4 reveal';
      col.innerHTML=`
        <a href="article.html?id=${a.id}" class="text-decoration-none d-block h-100">
          <div class="blog-card glass-card h-100" style="cursor:pointer">
            <div class="blog-thumb thumb-${a.category==='ai'?'ai':a.category==='seo'?'seo':a.category==='marketing'?'marketing':a.category==='career'?'career':'web'}">
              <div class="thumb-glow ${thumbGlowCls(a.category)}"></div>
              <i class="bi ${ICONS[a.category]||'bi-file-text'} thumb-icon-sm"></i>
              <div class="scan-line"></div>
            </div>
            <div class="blog-body">
              <div class="d-flex gap-2 mb-2">
                <span class="blog-tag ${tagClass(a.category)}">${a.categoryLabel}</span>
                <span class="blog-tag-date"><i class="bi bi-calendar3"></i> ${a.date}</span>
              </div>
              <h5 class="blog-title">${a.title}</h5>
              <p class="blog-excerpt">${a.excerpt}</p>
              <div class="blog-footer">
                <div class="author-chip-sm">
                  <div class="author-av-sm" style="background:${a.authorColor}">${a.authorInitials}</div>
                  <span>${a.author.split(' ')[0]} · ${a.readTime}</span>
                </div>
                <span class="read-link">Read <i class="bi bi-arrow-right-short"></i></span>
              </div>
            </div>
          </div>
        </a>`;
      moreGrid.appendChild(col);
    });
  }

  setTimeout(()=>observeReveal(),200);
}

function renderRelated(list, container){
  list.forEach(a=>{
    const el=document.createElement('a');
    el.href='article.html?id='+a.id;
    el.className='related-item';
    el.innerHTML=`
      <div class="related-thumb ${thumbBg(a.category)}" style="position:relative;overflow:hidden;">
        <div class="thumb-glow ${thumbGlowCls(a.category)}" style="position:absolute;inset:0;"></div>
        <i class="bi ${ICONS[a.category]||'bi-file-text'}" style="font-size:1.3rem;color:rgba(255,255,255,.8);position:relative;z-index:1;"></i>
      </div>
      <div class="related-info">
        <div class="related-title">${a.title}</div>
        <div class="related-meta"><i class="bi bi-clock"></i> ${a.readTime} · ${a.date}</div>
      </div>`;
    container.appendChild(el);
  });
}

/* ── Like Button ── */
const likeBtn=document.getElementById('likeBtn'), likeCount=document.getElementById('likeCount');
const likeKey='gocosys_like_'+artId;
let likes=parseInt(localStorage.getItem(likeKey+'_count')||Math.floor(Math.random()*80+20));
let liked=localStorage.getItem(likeKey)==='true';
if(likeCount) likeCount.textContent=likes;
if(liked&&likeBtn) likeBtn.classList.add('liked');
if(likeBtn){
  likeBtn.addEventListener('click',()=>{
    liked=!liked;
    likes=liked?likes+1:likes-1;
    likeCount.textContent=likes;
    likeBtn.classList.toggle('liked',liked);
    likeBtn.innerHTML=`<i class="bi bi-heart${liked?'-fill':''}"></i> <span>${likes}</span> Like${likes!==1?'s':''}`;
    localStorage.setItem(likeKey,liked);
    localStorage.setItem(likeKey+'_count',likes);
  });
}

/* ── Bookmark Button ── */
const bookmarkBtn=document.getElementById('bookmarkBtn');
const bmKey='gocosys_bookmark_'+artId;
let bookmarked=localStorage.getItem(bmKey)==='true';
if(bookmarked&&bookmarkBtn){ bookmarkBtn.innerHTML='<i class="bi bi-bookmark-fill"></i> Saved'; bookmarkBtn.classList.add('active'); }
if(bookmarkBtn){
  bookmarkBtn.addEventListener('click',()=>{
    bookmarked=!bookmarked;
    bookmarkBtn.innerHTML=bookmarked?'<i class="bi bi-bookmark-fill"></i> Saved':'<i class="bi bi-bookmark"></i> Save';
    bookmarkBtn.classList.toggle('active',bookmarked);
    localStorage.setItem(bmKey,bookmarked);
  });
}

/* ── Share ── */
function shareArticle(type){
  const url=encodeURIComponent(window.location.href);
  const title=encodeURIComponent(article.title);
  if(type==='twitter') window.open(`https://twitter.com/intent/tweet?text=${title}&url=${url}`,'_blank');
  else if(type==='linkedin') window.open(`https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`,'_blank');
  else if(type==='copy'){
    navigator.clipboard.writeText(window.location.href).then(()=>{
      const cb=document.getElementById('copyBtn');
      if(cb){ cb.innerHTML='<i class="bi bi-check-lg"></i>'; cb.style.color='var(--gold)'; setTimeout(()=>{ cb.innerHTML='<i class="bi bi-link-45deg"></i>'; cb.style.color=''; },2000); }
    });
  }
}
window.shareArticle=shareArticle;

/* ── Sidebar Newsletter ── */
const sBtn=document.getElementById('sideNlBtn'), sEmail=document.getElementById('sideNlEmail');
if(sBtn&&sEmail){
  sBtn.addEventListener('click',()=>{
    const e=sEmail.value.trim();
    if(!e||!e.includes('@')){ sEmail.style.borderColor='rgba(244,67,54,.5)'; setTimeout(()=>sEmail.style.borderColor='',1500); return; }
    sBtn.innerHTML='<i class="bi bi-check-lg"></i> Subscribed!';
    sBtn.style.background='linear-gradient(135deg,#4caf50,#81c784)';
    sEmail.value=''; sEmail.disabled=true; sBtn.disabled=true;
  });
}

/* ── INIT ── */
renderArticle();