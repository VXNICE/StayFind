// assets/js/dashboard.js

// Auth check and initial load
(async function checkAuth() {
  try {
    const r = await fetch('api/me.php', { credentials: 'include' });
    const { user } = await r.json();
    if (!user) { window.location.href = 'login.html'; return; }
    document.getElementById('signedInName').textContent = user.name || user.email || 'User';
    loadRooms();
  } catch {
    window.location.href = 'login.html';
  }
})();

// Elements
const grid   = document.getElementById('units');
const empty  = document.getElementById('empty');
const selLoc = document.getElementById('filterLocation');
const minR   = document.getElementById('filterMinPrice');
const maxR   = document.getElementById('filterMaxPrice');
const minLbl = document.getElementById('minPriceLabel');
const maxLbl = document.getElementById('maxPriceLabel');

let allRooms = [];

async function loadRooms() {
  empty.classList.add('hidden');
  grid.innerHTML = '';
  try {
    const res = await fetch('api/rooms_list.php', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Failed to load rooms');
    allRooms = data.rooms || [];
    buildLocationFilter(allRooms);
    renderFiltered();
  } catch (e) {
    console.error(e);
    empty.textContent = 'Failed to load rooms.';
    empty.classList.remove('hidden');
  }
}

function buildLocationFilter(rooms) {
  const unique = Array.from(new Set(
    rooms.map(r => (r.location || '').trim()).filter(Boolean)
  )).sort((a,b) => a.localeCompare(b));
  selLoc.innerHTML = '<option value="">All locations</option>' +
    unique.map(loc => `<option value="${escapeHtml(loc)}">${escapeHtml(loc)}</option>`).join('');
}

function renderFiltered() {
  minLbl.textContent = minR.value;
  maxLbl.textContent = maxR.value;
  const min = Number(minR.value);
  const max = Number(maxR.value);
  const loc = selLoc.value;
  const list = allRooms.filter(r => {
    const price = r.price == null ? null : Number(r.price);
    const okLoc = loc === '' || (r.location || '') === loc;
    const okPrice = price == null || (price >= min && price <= max);
    return okLoc && okPrice;
  });
  renderCards(list);
}

function renderCards(rooms) {
  if (!rooms || rooms.length === 0) {
    grid.innerHTML = '';
    empty.textContent = 'No units found.';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  grid.innerHTML = rooms.map(r => {
    const title = r.title || '(Untitled)';
    const loc   = r.location || '';
    const price = r.price != null ? Number(r.price) : '';
    const cap   = r.capacity != null ? r.capacity : '';
    const img   = r.image || '';
    return `
      <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        ${img ? `<img src="${escapeAttr(img)}" alt="${escapeAttr(title)}" class="h-40 w-full object-cover">` : `<div class="h-40 w-full bg-gray-200"></div>`}
        <div class="p-4">
          <h3 class="font-semibold text-gray-900">${escapeHtml(title)}</h3>
          <p class="text-sm text-gray-600">${escapeHtml(loc)}</p>
          <div class="mt-2 flex items-center justify-between text-sm text-gray-700">
            <span>${price !== '' ? '₱ ' + price.toLocaleString() : ''}</span>
            <span>${cap !== '' ? cap + ' pax' : ''}</span>
          </div>
          <div class="mt-3 flex gap-2">
            <button class="flex-1 px-3 py-2 rounded bg-blue-700 text-white text-sm hover:bg-blue-800" onclick="openView(${r.id})">View</button>
            <button class="flex-1 px-3 py-2 rounded bg-green-600 text-white text-sm hover:bg-green-700" onclick="bookRoom(${r.id})">Book Now</button>
          </div>
        </div>
      </div>`;
  }).join('');
}

[selLoc, minR, maxR].forEach(el => el.addEventListener('input', renderFiltered));

// Logout
actionLogout('logoutBtn');
actionLogout('logoutBtnMobile');
function actionLogout(id){
  document.getElementById(id)?.addEventListener('click', async () => {
    try { await fetch('api/logout.php', { credentials: 'include' }); } catch {}
    window.location.href = 'login.html';
  });
}

// View modal
function openView(id){
  const room = allRooms.find(r => Number(r.id) === Number(id));
  if(!room) return;
  document.getElementById('viewTitle').textContent = room.title || '';
  document.getElementById('viewLocation').textContent = room.location || '';
  document.getElementById('viewPrice').textContent = room.price ? '₱ ' + Number(room.price).toLocaleString() : '';
  document.getElementById('viewModal').classList.remove('hidden');
  document.getElementById('viewModal').classList.add('flex');
}
function closeView(){
  document.getElementById('viewModal').classList.add('hidden');
  document.getElementById('viewModal').classList.remove('flex');
}

// Booking
async function bookRoom(roomId){
  const start = prompt('Start date (YYYY-MM-DD)');
  if(!start) return;
  const end = prompt('End date (YYYY-MM-DD)');
  if(!end) return;
  const guests = prompt('Number of guests', '1') || '1';
  const form = new FormData();
  form.append('room_id', roomId);
  form.append('start_date', start);
  form.append('end_date', end);
  form.append('guests', guests);
  try{
    const res = await fetch('api/bookings_create.php',{method:'POST',body:form,credentials:'include'});
    const data = await res.json();
    if(!data.success) throw new Error(data.message);
    openPaymentModal(data.booking_id);
  }catch(e){
    alert('Booking failed: '+e.message);
  }
}

// Payment modal
const paymentModal = document.getElementById('paymentModal');
const paymentForm  = document.getElementById('paymentForm');

function openPaymentModal(id){
  document.getElementById('paymentBookingId').value = id;
  paymentModal.classList.remove('hidden');
  paymentModal.classList.add('flex');
}
function closePaymentModal(){
  paymentModal.classList.add('hidden');
  paymentModal.classList.remove('flex');
}

paymentForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = new FormData(paymentForm);
  try{
    const res = await fetch('api/booking_payment_upload.php',{method:'POST',body:form,credentials:'include'});
    const data = await res.json();
    if(!data.success) throw new Error(data.message);
    alert('Payment submitted!');
    closePaymentModal();
  }catch(err){
    alert('Upload failed: '+err.message);
  }
});

// Helpers
function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
}
function escapeAttr(s){
  return String(s).replace(/"/g,'&quot;');
}

// Expose globally
window.openView = openView;
window.closeView = closeView;
window.bookRoom = bookRoom;
window.openPaymentModal = openPaymentModal;
window.closePaymentModal = closePaymentModal;
