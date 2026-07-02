export type ApiResponse<T> = {
  ok: boolean;
  data?: T;
  error?: string;
};

const appBase = import.meta.env.BASE_URL.replace(/\/$/, '');
export const API_BASE = `${appBase}/api/index.php`;

export function apiUrl(route: string): string {
  return `${API_BASE}?route=${encodeURIComponent(route)}`;
}

async function readApiResponse<T>(response: Response): Promise<ApiResponse<T>> {
  const contentType = response.headers.get('content-type') || '';

  if (!contentType.includes('application/json')) {
    const text = await response.text().catch(() => '');
    const detail = text.trim() ? ` Dettaglio: ${text.trim().slice(0, 180)}` : '';
    return {
      ok: false,
      error: `Risposta API non valida (${response.status}).${detail}`
    };
  }

  const payload = await response.json().catch(() => null) as ApiResponse<T> | null;
  if (!payload) {
    return {
      ok: false,
      error: `JSON API non leggibile (${response.status}).`
    };
  }

  if (!response.ok && payload.ok !== false) {
    return {
      ok: false,
      error: payload.error || `Errore API ${response.status}`
    };
  }

  return payload;
}

export async function apiGet<T>(route: string): Promise<ApiResponse<T>> {
  try {
    const response = await fetch(apiUrl(route), {
      credentials: 'include'
    });

    return readApiResponse<T>(response);
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : 'Impossibile contattare le API.'
    };
  }
}

export async function apiPost<T>(route: string, payload: unknown): Promise<ApiResponse<T>> {
  try {
    const response = await fetch(apiUrl(route), {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    return readApiResponse<T>(response);
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : 'Impossibile contattare le API.'
    };
  }
}
