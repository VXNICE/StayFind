async function api(path, options = {}) {
  const res = await fetch(`/api/${path}`, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options,
  });
  if (!res.ok) throw new Error((await res.json()).error || 'Request failed');
  return res.json();
}

// Example usage:
// await api('login.php', { method: 'POST', body: JSON.stringify({ email, password }) });
// await api('rooms.php?q=manila'); // GET
