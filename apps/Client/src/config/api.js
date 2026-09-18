export const API_BASE_URL = import.meta.env.VITE_BACKEND;

export const buildApiUrl = (path = '') => {
  const cleanBase = API_BASE_URL.replace(/\/$/, '');
  const apiBase = /\/api$/i.test(cleanBase) ? cleanBase : `${cleanBase}/api`;
  const cleanPath = path.replace(/^\//, '');
  return `${apiBase}/${cleanPath}`;
};