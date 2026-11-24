(() => {
 const search = document.getElementById('catalog-search');
 const cards = [...document.querySelectorAll('[data-product]')];
 const filters = [...document.querySelectorAll('[data-filter]')];
 let currentFilter = 'all';
 function updateCatalog() {
  const query = (search?.value || '').trim().toLocaleLowerCase('ru');
  let visible = 0;
  cards.forEach(card => {
   const matches = card.dataset.name.toLocaleLowerCase('ru').includes(query) && (currentFilter === 'all' || card.dataset.available === 'yes');
   card.hidden = !matches;
   if (matches) visible++;
  });
  const empty = document.getElementById('no-results');
  if (empty) empty.hidden = visible > 0;
 }
 search?.addEventListener('input', updateCatalog);
 filters.forEach(button => button.addEventListener('click', () => {
  currentFilter = button.dataset.filter;
  filters.forEach(item => { item.classList.toggle('active', item === button); item.setAttribute('aria-pressed', String(item === button)); });
  updateCatalog();
 }));
 document.querySelector('[data-copy-key]')?.addEventListener('click', async () => {
  const key = document.getElementById('activation-key');
  const status = document.getElementById('copy-status');
  try { await navigator.clipboard.writeText(key.textContent); status.textContent = 'Ключ скопирован'; }
  catch { status.textContent = 'Выделите ключ и скопируйте вручную'; }
 });
})();

(() => {
 const canvas = document.getElementById('code-background');
 if (!canvas) return;
 const context = canvas.getContext('2d');
 if (!context) return;
 const control = document.querySelector('[data-code-motion]');
 const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
 // Illustrative code only; no order, customer or activation data is read.
 const blocks = [
  ['// nexora / digital access', 'const possibilities = await discover();', '', 'function unlock(nextLevel) {', '  const access = new DigitalKey();', '  return access.open(nextLevel);', '}', '', 'export default possibilities;'],
  ['interface DigitalExperience {', '  software: Collection<Program>;', '  access: PersonalKey;', '  next: Possibility[];', '}', '', 'const future = createExperience({', '  curiosity: true,', '  limits: undefined,', '});'],
  ['// build something extraordinary', 'async function launch() {', '  const tools = await catalog.load();', '  const choice = tools.find(yourGoal);', '', '  await choice.initialize();', '  return new Possibilities();', '}', '', 'launch().then(explore);'],
  ['namespace Nexora;', '', 'class NextLevel extends Experience {', '  public function explore(): Future {', '    return new Future(', '      ideas: unlimited(),', '      tools: yourChoice(),', '    );', '  }', '}']
 ];
 let width = 0, height = 0, panels = [];
 let frame = 0, lastTime = 0, elapsed = 0, lastPaint = -Infinity;
 let paused = reducedMotion.matches;
 const lineHeight = 24;
 function resize() {
  width = window.innerWidth; height = window.innerHeight;
  const ratio = Math.min(window.devicePixelRatio || 1, 2);
  canvas.width = Math.round(width * ratio); canvas.height = Math.round(height * ratio);
  context.setTransform(ratio, 0, 0, ratio, 0, 0);
  const columns = width < 760 ? 2 : 3;
  const columnWidth = width / columns;
  panels = Array.from({length: columns * 2}, (_, index) => ({
   x: (index % columns) * columnWidth + 18,
   y: Math.floor(index / columns) * Math.max(height / 2, 280) + 110 + (index % 3) * 38,
   width: columnWidth - 32,
   lines: blocks[index % blocks.length],
   offset: index * 4700,
  }));
  paint();
 }
 function paint() {
  context.clearRect(0, 0, width, height);
  context.font = '12px Consolas, monospace';
  panels.forEach(panel => {
   const total = panel.lines.join('\n').length;
   const cycle = total * 67 + 5200;
   const progress = (elapsed + panel.offset) % cycle;
   let characters = Math.min(total, Math.floor(progress / 67));
   const opacity = progress > cycle - 1600 ? (cycle - progress) / 1600 : 1;
   context.save();
   context.beginPath(); context.rect(panel.x, panel.y - 16, panel.width, lineHeight * panel.lines.length + 18); context.clip();
   context.globalAlpha = opacity;
   let cursorDrawn = false;
   panel.lines.forEach((line, row) => {
    if (characters < 0) return;
    const visible = line.slice(0, characters);
    const y = panel.y + row * lineHeight;
    context.fillStyle = '#6e8490';
    context.fillText(String(row + 1).padStart(2, '0'), panel.x, y);
    context.fillStyle = line.trim().startsWith('//') ? '#77818b' : /^(const|interface|class|namespace|export|async)/.test(line) ? '#c2a28d' : '#9dd4df';
    context.fillText(visible, panel.x + 30, y);
    if (!cursorDrawn && characters <= line.length) {
     if (paused || progress % 1000 < 650) {
      context.fillStyle = '#ff946c';
      context.fillRect(panel.x + 30 + context.measureText(visible).width + 2, y - 11, 6, 14);
     }
     cursorDrawn = true;
    }
    characters -= line.length + 1;
   });
   context.restore();
  });
 }
 function tick(time) {
  if (paused || document.hidden) { frame = 0; return; }
  if (lastTime) elapsed += Math.min(time - lastTime, 100);
  lastTime = time;
  if (time - lastPaint >= 33) { paint(); lastPaint = time; }
  frame = requestAnimationFrame(tick);
 }
 function sync() {
  cancelAnimationFrame(frame); frame = 0; lastTime = 0;
  control.hidden = false;
  control.textContent = paused ? 'Включить анимацию' : 'Пауза анимации';
  control.setAttribute('aria-pressed', String(paused));
  paint();
  if (!paused && !document.hidden) frame = requestAnimationFrame(tick);
 }
 control?.addEventListener('click', () => { paused = !paused; sync(); });
 reducedMotion.addEventListener('change', event => { paused = event.matches; sync(); });
 document.addEventListener('visibilitychange', sync);
 window.addEventListener('resize', resize);
 window.addEventListener('pagehide', () => { cancelAnimationFrame(frame); frame = 0; });
 window.addEventListener('pageshow', sync);
 resize(); sync();
})();
