const menuToggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.main-nav');
if (menuToggle && nav) menuToggle.addEventListener('click', () => nav.classList.toggle('open'));

const searchButton = document.querySelector('[data-search]');
if (searchButton) searchButton.addEventListener('click', () => {
  const query = window.prompt('Search the collection');
  if (query && query.trim()) window.location.href = `shop.html?search=${encodeURIComponent(query.trim())}`;
});

const savedCount = localStorage.getItem('watchCartCount');
if (savedCount) document.querySelectorAll('.cart-count').forEach((item) => { item.textContent = savedCount; });

const addToCart = document.querySelector('#add-to-cart');
if (addToCart) addToCart.addEventListener('click', () => {
  const cart = JSON.parse(localStorage.getItem('watchCart') || '[]');
  const productId = Number(addToCart.dataset.productId || 1);
  const productName = addToCart.dataset.productName || 'Selected watch';
  const productPrice = Number(addToCart.dataset.productPrice || 0);
  const existing = cart.find((item) => item.product_id === productId);
  if (existing) existing.quantity += 1;
  else cart.push({ product_id: productId, name: productName, price: productPrice, quantity: 1 });
  const count = cart.reduce((sum, item) => sum + item.quantity, 0);
  localStorage.setItem('watchCart', JSON.stringify(cart));
  localStorage.setItem('watchCartCount', count);
  document.querySelectorAll('.cart-count').forEach((item) => { item.textContent = count; });
  addToCart.innerHTML = 'Added to cart <span>✓</span>';
  addToCart.disabled = true;
});

const cartItems = document.querySelector('#cart-items');
if (cartItems) {
  const cart = JSON.parse(localStorage.getItem('watchCart') || '[]');
  const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  const totalElement = document.querySelector('#cart-total');
  const orderForm = document.querySelector('#order-form');
  const message = document.querySelector('#cart-message');
  if (!cart.length) {
    cartItems.innerHTML = '<p>Your cart is empty.</p>';
  } else {
    cartItems.innerHTML = cart.map((item) => `<div class="spec-list"><div><strong>${item.name}</strong><span>${item.quantity} × $${item.price.toLocaleString()}</span></div></div>`).join('');
    totalElement.textContent = `Estimated total: $${total.toLocaleString()}`;
    orderForm.hidden = false;
  }
  orderForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(orderForm);
    const payload = { customer_name: formData.get('customer_name'), customer_email: formData.get('customer_email'), items: cart };
    try {
      const response = await fetch('backend/api/orders.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Unable to send order request.');
      localStorage.removeItem('watchCart');
      localStorage.setItem('watchCartCount', '0');
      document.querySelectorAll('.cart-count').forEach((item) => { item.textContent = '0'; });
      orderForm.hidden = true;
      message.textContent = `${result.message} Reference #${result.order_id}.`;
      message.hidden = false;
    } catch (error) {
      message.textContent = 'For local file mode, please contact us on WhatsApp to complete this request.';
      message.hidden = false;
    }
  });
}

document.querySelectorAll('#contact-form, #valuation-form').forEach((form) => form.addEventListener('submit', async (event) => {
  event.preventDefault();
  const formData = new FormData(form);
  const payload = { type: form.id === 'valuation-form' ? 'valuation' : 'contact', name: formData.get('name'), email: formData.get('email'), subject: formData.get('appointment_type') || formData.get('brand'), details: [...formData.entries()].map(([key, value]) => `${key}: ${value}`).join('\n') };
  const submit = form.querySelector('button[type="submit"]');
  try {
    const response = await fetch('backend/api/inquiries.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Unable to send request.');
    form.innerHTML = `<p class="notice">${result.message}</p>`;
  } catch (error) {
    if (submit) submit.textContent = 'Unable to connect. Email us instead.';
  }
}));

const params = new URLSearchParams(window.location.search);
const requestedBrand = params.get('brand');
if (requestedBrand && document.querySelector('.catalog-toolbar strong')) {
  document.querySelector('.catalog-toolbar strong').textContent = `${requestedBrand.toUpperCase()} collection`;
}

const catalog = document.querySelector('.catalog-products');
if (catalog) {
  const existingKeys = new Set([...catalog.querySelectorAll('a[href*="watch="]')].map((link) => new URL(link.href).searchParams.get('watch')));
  (window.additionalWatches || []).filter((watch) => !existingKeys.has(watch.key)).forEach((watch) => {
    const card = document.createElement('article');
    card.className = 'product-card';
    const price = watch.price === null ? 'Price on request' : `$${watch.price.toLocaleString()}`;
    card.innerHTML = `<a href="product.html?watch=${watch.key}"><div class="product-image"><span class="badge">${watch.condition === 'New / unworn' ? 'New arrival' : 'Available now'}</span><img src="${watch.image}" alt="${watch.brand} ${watch.name}"></div><div class="product-meta"><p>${watch.brand}</p><h3>${watch.name}</h3><span>${watch.summary}</span><strong>${price}</strong></div></a>`;
    catalog.append(card);
  });
  const existingNames = new Set([...catalog.querySelectorAll('.product-meta')].map((meta) => `${meta.querySelector('p')?.textContent.trim()} ${meta.querySelector('h3')?.textContent.trim()}`.toLowerCase()));
  fetch('backend/api/products.php')
    .then((response) => response.ok ? response.json() : Promise.reject(new Error('Unable to load inventory.')))
    .then(({ products = [] }) => products.forEach((product) => {
      const nameKey = `${product.brand} ${product.name}`.toLowerCase();
      if (existingNames.has(nameKey)) return;
      const card = document.createElement('article');
      card.className = 'product-card';
      const price = product.price === null ? 'Price on request' : `$${Number(product.price).toLocaleString()}`;
      card.innerHTML = `<a href="product.html?watch=product-${product.id}"><div class="product-image"><span class="badge">Available now</span><img src="${product.image_url}" alt="${product.brand} ${product.name}"></div><div class="product-meta"><p>${product.brand}</p><h3>${product.name}</h3><span>${product.condition_label} · ${product.year || 'Year unavailable'}</span><strong>${price}</strong></div></a>`;
      catalog.append(card);
      existingNames.add(nameKey);
    }))
    .then(() => updateCatalog())
    .catch(() => {});
  const filterLabels = [...document.querySelectorAll('.filter-panel label')];
  const searchQuery = (params.get('search') || '').trim().toLowerCase();
  const selectedFilters = () => filterLabels.filter((label) => label.querySelector('input:checked'))
    .map((label) => label.textContent.trim().toLowerCase());

  const matches = (card, filters) => {
    const text = card.textContent.toLowerCase();
    const brandFilters = filters.filter((filter) => ['rolex', 'patek philippe', 'audemars piguet', 'cartier', 'omega', 'richard mille', 'tudor', 'iwc', 'vacheron constantin', 'jaeger-lecoultre', 'a. lange & sohne', 'zenith', 'breguet', 'hublot', 'girard-perregaux', 'blancpain', 'breitling'].includes(filter));
    const conditionFilters = filters.filter((filter) => filter.includes('new / unworn') || filter.includes('pre-owned'));
    const priceFilters = filters.filter((filter) => filter.startsWith('under') || filter.includes('$10,000') || filter.includes('$30,000+'));
    const price = Number((card.querySelector('.product-meta strong')?.textContent || '').replace(/[^0-9]/g, ''));
    const brandMatch = !brandFilters.length || brandFilters.some((filter) => text.includes(filter));
    const conditionMatch = !conditionFilters.length || conditionFilters.some((filter) => (filter.includes('new') && text.includes('unworn')) || (filter.includes('pre-owned') && !text.includes('unworn')));
    const priceMatch = !priceFilters.length || priceFilters.some((filter) => (filter.startsWith('under') && price > 0 && price < 10000) || (filter.includes('$10,000') && price >= 10000 && price <= 30000) || (filter.includes('$30,000+') && price > 30000));
    return brandMatch && conditionMatch && priceMatch && (!searchQuery || text.includes(searchQuery));
  };

  const emptyState = document.createElement('p');
  emptyState.className = 'catalog-empty';
  emptyState.textContent = 'No watches match those criteria.';
  catalog.after(emptyState);
  const updateCatalog = () => {
    const filters = selectedFilters();
    const cards = [...catalog.querySelectorAll('.product-card')];
    let visible = 0;
    cards.forEach((card) => {
      const show = matches(card, filters);
      card.hidden = !show;
      if (show) visible += 1;
    });
    emptyState.hidden = visible > 0;
    const count = document.querySelector('.catalog-toolbar span');
    if (count) count.textContent = `${visible} ${visible === 1 ? 'piece' : 'pieces'} · Sort by featured`;
  };
  filterLabels.forEach((label) => label.querySelector('input')?.addEventListener('change', updateCatalog));
  updateCatalog();
}
