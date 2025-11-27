export const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export async function checkDomain(domain: string, tld: string) {
  const response = await fetch(`${API_URL}/check-domain`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ domain, tld }),
  });
  return response.json();
}

export async function createOrder(data: any, token?: string) {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json'
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_URL}/orders`, {
    method: 'POST',
    headers,
    body: JSON.stringify(data)
  });

  return response.json();
}
