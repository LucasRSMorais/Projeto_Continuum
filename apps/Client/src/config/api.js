export const API_BASE_URL = import.meta.env.VITE_BACKEND;

export const buildApiUrl = (path = '') => {
  const cleanBase = API_BASE_URL.replace(/\/$/, '');
  const cleanPath = path.replace(/^\//, '');
  return `${cleanBase}/${cleanPath}`;
};