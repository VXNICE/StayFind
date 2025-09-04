async function loadBookings() {
  try {
    const res = await fetch('api/bookings_list.php', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    const tbody = document.querySelector('#bookingsTable tbody');
    tbody.innerHTML = data.bookings.map(b => {
      return `<tr>
        <td class="border p-2">${b.id}</td>
        <td class="border p-2">${b.user_name || ''} (${b.user_email || ''})</td>
        <td class="border p-2">${b.room_title || ''}</td>
        <td class="border p-2">${b.start_date} - ${b.end_date}</td>
        <td class="border p-2">${b.payment_status}<br><a class='text-blue-600 underline' href='${b.payment_receipt_path || '#'}' target='_blank'>Receipt</a></td>
        <td class="border p-2 text-center">
          <button class='bg-green-600 text-white px-2 py-1 m-1' onclick='verify(${b.id},"approve")'>Approve</button>
          <button class='bg-red-600 text-white px-2 py-1 m-1' onclick='verify(${b.id},"decline")'>Decline</button>
        </td>
      </tr>`;
    }).join('');
  } catch (e) {
    alert('Failed to load bookings: ' + e.message);
  }
}

async function verify(id, action) {
  try {
    const form = new FormData();
    form.append('booking_id', id);
    form.append('action', action);
    const res = await fetch('api/booking_verify.php', { method: 'POST', body: form, credentials: 'include' });
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    loadBookings();
  } catch (e) {
    alert('Action failed: ' + e.message);
  }
}

loadBookings();
