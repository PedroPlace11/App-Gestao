import { ref } from 'vue';

function buildUrl(endpoint, query) {
  const base = endpoint.startsWith('/api/') ? endpoint : `/api/v1${endpoint}`;

  if (!query) {
    return base;
  }

  const params = new URLSearchParams();

  Object.entries(query).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '') {
      return;
    }

    params.append(key, String(value));
  });

  const queryString = params.toString();
  return queryString ? `${base}?${queryString}` : base;
}

function getCsrfToken() {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  return token || '';
}

export function useApi() {
  const loading = ref(false);
  const error = ref(null);

  const request = async (endpoint, options = {}) => {
    loading.value = true;
    error.value = null;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);

    try {
      const headers = new Headers(options.headers ?? {});
      headers.set('Accept', 'application/json');

      const csrfToken = getCsrfToken();
      if (csrfToken) {
        headers.set('X-CSRF-TOKEN', csrfToken);
      }

      let body;
      if (options.body instanceof FormData) {
        body = options.body;
      } else if (options.body && typeof options.body === 'object') {
        headers.set('Content-Type', 'application/json');
        body = JSON.stringify(options.body);
      } else if (typeof options.body === 'string') {
        body = options.body;
      }

      const url = buildUrl(endpoint, options.query);
      console.log(`[API] ${options.method || 'GET'} ${url}`, body ? JSON.parse(body) : '');

      const response = await fetch(url, {
        ...options,
        body,
        headers,
        credentials: 'include',
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      console.log(`[API] Response: ${response.status} ${response.statusText}`);

      if (!response.ok) {
        const fallback = `HTTP ${response.status}`;
        let message = fallback;
        let data = null;

        try {
          data = await response.json();
          console.log(`[API] Error response body:`, data);
          message = data.message ?? fallback;

          if (data?.errors && typeof data.errors === 'object') {
            const firstErrorGroup = Object.values(data.errors)[0];
            if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
              message = firstErrorGroup[0];
            }
          }
        } catch (e) {
          console.log(`[API] Could not parse error response:`, e);
          message = fallback;
        }

        const requestError = new Error(message);
        requestError.status = response.status;
        requestError.payload = data;
        throw requestError;
      }

      if (response.status === 204) {
        return null;
      }

      const responseData = await response.json();
      console.log(`[API] Response data:`, responseData);
      return responseData;
    } catch (requestError) {
      clearTimeout(timeoutId);

      if (requestError instanceof Error && requestError.name === 'AbortError') {
        error.value = 'Pedido expirou. Servidor não respondeu a tempo.';
        console.error('[API] Request timeout');
      } else {
        error.value = requestError instanceof Error ? requestError.message : 'Unknown API error';
        console.error('[API] Request failed:', error.value);
      }

      throw requestError;
    } finally {
      loading.value = false;
    }
  };

  const get = (endpoint, query) => request(endpoint, { method: 'GET', query });
  const post = (endpoint, body) => request(endpoint, { method: 'POST', body });
  const put = (endpoint, body) => request(endpoint, { method: 'PUT', body });
  const remove = (endpoint) => request(endpoint, { method: 'DELETE' });

  return {
    loading,
    error,
    request,
    get,
    post,
    put,
    remove,
  };
}
