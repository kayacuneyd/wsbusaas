// API URL Configuration
// Production: Set VITE_API_URL in Vercel environment variables to https://bezmidar.de/api
// Development: Defaults to localhost:8000
const getApiUrl = () => {
  // Check if VITE_API_URL is explicitly set
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL;
  }

  // Production detection: if we're not on localhost, use production backend
  if (typeof window !== 'undefined' && !window.location.hostname.includes('localhost')) {
    return 'https://api.bezmidar.de';
  }

  // Development default
  return 'http://localhost:8000/api';
};

export const API_URL = getApiUrl();

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

export async function fetchPackages() {
  const response = await fetch(`${API_URL}/packages`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    }
  });
  return response.json();
}
