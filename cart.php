<?php require 'layout.php'; page_header('Cart'); ?>
<main class="wrap"><h2 class="sec">Shopping cart</h2><div id="cartbox"></div></main>
<?php page_footer(); ?>
<script>
function draw() {
  const c = getCart(), ids = Object.keys(c), box = document.getElementById('cartbox');
  if (!ids.length) { box.innerHTML = '<p>Your cart is empty. <a href="shop.php"><u>Continue shopping</u></a></p>'; return; }
  let t = 0, h = '<table><tr><th></th><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>';
  ids.forEach(id => { const i = c[id]; t += i.price * i.qty;
    h += `<tr><td>${i.image ? `<img class="mini" src="uploads/${esc(i.image)}">` : ''}</td><td>${esc(i.name)}</td><td>KES ${i.price}</td>
      <td><button onclick="chg(${id},-1)">-</button> ${i.qty} <button onclick="chg(${id},1)">+</button></td>
      <td>KES ${i.price * i.qty}</td><td><button class="del" onclick="rm(${id})">Remove</button></td></tr>`; });
  box.innerHTML = h + `</table><p class="total">Total: KES ${t}</p><a class="btn" href="checkout.php">Proceed to checkout</a>`;
}
function chg(id, d) { const c = getCart(); c[id].qty += d; if (c[id].qty < 1) delete c[id]; saveCart(c); draw(); }
function rm(id) { const c = getCart(); delete c[id]; saveCart(c); draw(); }
draw();
</script>
