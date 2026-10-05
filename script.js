const CART_KEY = 'cjm_cosmetic_cart';
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

function getCart() { try { return JSON.parse(localStorage.getItem(CART_KEY)) || {}; } catch (e) { return {}; } }
function saveCart(c) { try { localStorage.setItem(CART_KEY, JSON.stringify(c)); } catch (e) {} updateCount(); }
function addToCart(p, qty = 1) {
  const c = getCart();
  c[p.id] = c[p.id] ? { ...c[p.id], qty: c[p.id].qty + qty } : { name: p.name, price: p.price, image: p.image, qty };
  saveCart(c); toast('Added to cart');
}
function updateCount() {
  const n = Object.values(getCart()).reduce((a, i) => a + i.qty, 0);
  document.querySelectorAll('.cart-count').forEach(el => el.textContent = n);
}
function toast(t) {
  const el = document.getElementById('toast'); el.textContent = t; el.style.display = 'block';
  setTimeout(() => el.style.display = 'none', 1800);
}
document.addEventListener('DOMContentLoaded', updateCount);
