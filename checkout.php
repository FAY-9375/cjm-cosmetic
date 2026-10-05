<?php require 'layout.php'; page_header('Checkout'); ?>
<main class="wrap two">
  <section class="box">
    <h2>Delivery details</h2>
    <input id="name" placeholder="Full name">
    <input id="phone" placeholder="M-Pesa number e.g. 0712345678">
    <textarea id="address" rows="3" placeholder="Delivery address / town"></textarea>
    <button id="pay">Pay with M-Pesa</button>
    <p id="msg"></p>
  </section>
  <aside class="box"><h2>Order summary</h2><div id="sum"></div></aside>
</main>
<?php page_footer(); ?>
<script>
const $ = id => document.getElementById(id);
const cart = getCart();
let total = 0;
$('sum').innerHTML = Object.values(cart).map(i => { total += i.price * i.qty; return `<p>${esc(i.name)} x${i.qty}<br><strong>KES ${i.price * i.qty}</strong></p>`; }).join('') + `<p class="total">Total: KES ${total}</p>`;
if (!total) { $('sum').innerHTML = '<p>Your cart is empty.</p>'; $('pay').disabled = true; }

$('pay').onclick = async () => {
  const msg = $('msg');
  if (!$('name').value.trim() || !$('phone').value.trim() || !$('address').value.trim()) return msg.textContent = 'Fill in all details.';
  $('pay').disabled = true; msg.textContent = 'Sending M-Pesa prompt...';
  try {
    const res = await fetch('pay.php', { method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: $('name').value, phone: $('phone').value, address: $('address').value,
        cart: Object.entries(cart).map(([id, i]) => ({ id: +id, qty: i.qty })) }) });
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    msg.textContent = 'Check your phone and enter your M-Pesa PIN...';
    poll(data.order_id);
  } catch (e) { msg.textContent = 'Error: ' + e.message; $('pay').disabled = false; }
};
function poll(id, tries = 0) {
  setTimeout(async () => {
    const r = await (await fetch('status.php?id=' + id)).json();
    if (r.status === 'PAID') {
      saveCart({});
      $('msg').innerHTML = `Payment received! Order #${id}, receipt ${esc(r.mpesa_receipt)}. <a href="track.php?id=${id}"><u>Track order</u></a>`;
    } else if (r.status === 'FAILED') { $('msg').textContent = 'Failed: ' + (r.result_desc || 'Payment cancelled.'); $('pay').disabled = false; }
    else if (tries < 20) poll(id, tries + 1);
    else { $('msg').textContent = 'Timed out. Please try again.'; $('pay').disabled = false; }
  }, 3000);
}
</script>
