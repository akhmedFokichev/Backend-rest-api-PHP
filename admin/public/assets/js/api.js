/**
 * api.js — клиент REST API из браузера.
 *
 * Назначение: fetch к /admin/api/proxy/* (токен добавляет PHP на сервере).
 */
const APP_BASE = '/admin';

const Api = {
  async request(method, path, body = null) {
    const options = {
      method: method.toUpperCase(),
      headers: {
        Accept: 'application/json',
      },
    };

    if (body !== null) {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    const response = await fetch(APP_BASE + '/api/proxy/' + path.replace(/^\//, ''), options);

    if (response.status === 204) {
      return null;
    }

    let data = null;

    try {
      data = await response.json();
    } catch (e) {
      data = { message: 'Invalid JSON response' };
    }

    if (response.status === 401) {
      window.location.href = APP_BASE + '/login';
      throw new Error('Unauthorized');
    }

    if (!response.ok) {
      const error = new Error(data.message || data.error || 'Request failed');
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return data;
  },

  get(path) {
    return this.request('GET', path);
  },

  post(path, body) {
    return this.request('POST', path, body);
  },

  put(path, body) {
    return this.request('PUT', path, body);
  },

  delete(path) {
    return this.request('DELETE', path);
  },
};

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = String(text ?? '');
  return div.innerHTML;
}
