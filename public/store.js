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
