export type ApiResponse<T> = {
  ok: boolean;
  data?: T;
  error?: string;
};

const API_BASE = '/api/index.php';

export async function apiGet<T>(route: string): Promise<ApiResponse<T>> {
  const response = await fetch(`${API_BASE}?route=${encodeURIComponent(route)}`, {
    credentials: 'include'
  });

  return response.json();
}

export async function apiPost<T>(route: string, payload: unknown): Promise<ApiResponse<T>> {
  const response = await fetch(`${API_BASE}?route=${encodeURIComponent(route)}`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  return response.json();
}
