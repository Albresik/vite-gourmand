const filters = document.querySelector('#filters');
const menuList = document.querySelector('#menu-list');
const resultCount = document.querySelector('#result-count');

function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value; return div.innerHTML; }
async function loadMenus() {
  if (!filters || !menuList) return;
  menuList.innerHTML = '<p>Chargement des menus…</p>';
  try {
    const response = await fetch(`api/menus.php?${new URLSearchParams(new FormData(filters))}`);
    const menus = await response.json();
    if (!response.ok) throw new Error(menus.error);
    resultCount.textContent = `${menus.length} menu${menus.length > 1 ? 's' : ''} trouvé${menus.length > 1 ? 's' : ''}`;
    menuList.innerHTML = menus.length ? menus.map(menu => `<article class="col-md-6 col-lg-4"><div class="card menu-card h-100"><img class="card-img-top" src="${escapeHtml(menu.image_url)}" alt="Illustration du menu ${escapeHtml(menu.title)}"><div class="card-body d-flex flex-column"><div><span class="badge text-bg-light">${escapeHtml(menu.theme)}</span> <span class="badge text-bg-success">${escapeHtml(menu.diet)}</span></div><h3 class="h4 mt-3">${escapeHtml(menu.title)}</h3><p>${escapeHtml(menu.description)}</p><p class="small text-secondary">À partir de ${menu.min_people} personnes · ${menu.stock} disponible(s)</p><div class="mt-auto d-flex justify-content-between align-items-center"><strong>${Number(menu.price).toFixed(2)} € <span class="small fw-normal">/ pers.</span></strong><a class="btn btn-outline-dark" href="menu.php?id=${menu.id}">Détail</a></div></div></div></article>`).join('') : '<p>Aucun menu ne correspond à ces critères.</p>';
  } catch (error) { menuList.innerHTML = `<p class="text-danger">${escapeHtml(error.message)}</p>`; }
}
filters?.addEventListener('input', loadMenus); filters?.addEventListener('change', loadMenus); loadMenus();
