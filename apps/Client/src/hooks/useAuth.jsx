// Este hook reaproveita o contexto de autenticação definido em auth.jsx.
// Ele é um atalho para manter a importação mais simples em componentes e páginas.

export { AuthContext, AuthProvider, useAuth } from '../services/utils/auth';

export default function useAuthHook() {
  return useAuth();
}