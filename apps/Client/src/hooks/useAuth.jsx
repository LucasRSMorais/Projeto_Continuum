export { AuthContext, AuthProvider, useAuth } from '../services/utils/auth';

export default function useAuthHook() {
  return useAuth();
}