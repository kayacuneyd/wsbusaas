export const API_BASE = import.meta.env.VITE_API_BASE || 'https://your-backend.com';

async function req(path: string, options: RequestInit = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
      Authorization: `Bearer ${import.meta.env.VITE_API_TOKEN || 'change-me-api-token'}`
    },
    ...options
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || 'Request failed');
  }
  return res.json();
}

export const Api = {
  checkDomain: (domain: string) => req('/api/check-domain', { method: 'POST', body: JSON.stringify({ domain }) }),
  createOrder: (payload: any) => req('/api/create-order', { method: 'POST', body: JSON.stringify(payload) }),
  updateTheme: (payload: any) => req('/api/update-theme-config', { method: 'POST', body: JSON.stringify(payload) }),
  initiatePayment: (order_id: number) => req('/api/initiate-payment', { method: 'POST', body: JSON.stringify({ order_id }) }),
  orderStatus: (order_id: number) => req(`/api/order-status?order_id=${order_id}`)
};
