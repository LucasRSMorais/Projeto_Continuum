/**
 * Centraliza a configuração do backend para que o frontend
 * possa montar URLs de API de forma consistente.
 *
 * O projeto usa variáveis de ambiente do Vite, então este módulo
 * garante que o cliente chame o endpoint correto mesmo em produção,
 * local ou em ambientes do Railway.
 */
export const API_BASE_URL = import.meta.env.VITE_BACKEND;

/**
 * Monta a URL completa da API com suporte a diferentes formatos de base.
 *
 * Exemplos:
 * - http://localhost:8000 -> http://localhost:8000/api
 * - http://localhost:8000/api -> mantém a base existente
 *
 * @param {string} path - caminho específico do endpoint, como 'session.php' ou 'login.php'
 * @returns {string} URL completa pronta para fetch
 */
export const buildApiUrl = (path = '') => {
  // Remove a barra final para evitar URLs duplicadas.
  const cleanBase = API_BASE_URL.replace(/\/$/, '');

  // Se a base já terminar com /api, usa a mesma URL; caso contrário, acrescenta o prefixo.
  const apiBase = /\/api$/i.test(cleanBase) ? cleanBase : `${cleanBase}/api`;

  // Remove barra inicial para evitar '//'.
  const cleanPath = path.replace(/^\//, '');

  return `${apiBase}/${cleanPath}`;
};