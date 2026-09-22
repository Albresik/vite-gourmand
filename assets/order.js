const form = document.querySelector('#order-form');
if (form) {
  const price = Number(form.dataset.price); const min = Number(form.dataset.min);
  const quantity = document.querySelector('#quantity'); const distance = document.querySelector('#distance_km');
  const money = value => `${value.toFixed(2).replace('.', ',')} €`;
  function updateTotal() {
    const people = Math.max(min, Number(quantity.value) || min); const km = Math.max(0, Number(distance.value) || 0);
    const menus = people * price; const discount = people >= min + 5 ? menus * .10 : 0;
    const inBordeaux = document.querySelector('#delivery_address').value.toLowerCase().includes('bordeaux'); const delivery = inBordeaux ? 5 : 5 + (.59 * km);
    document.querySelector('#menu-total').textContent = money(menus); document.querySelector('#discount').textContent = `− ${money(discount)}`; document.querySelector('#delivery-fee').textContent = money(delivery); document.querySelector('#total').textContent = money(menus - discount + delivery);
  }
  ['input', 'change'].forEach(event => form.addEventListener(event, updateTotal)); updateTotal();
}
