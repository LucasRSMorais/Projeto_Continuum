import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import MyGlobalStyles from './styles/globalStyles';
import RoutesApp from './routes';
import { AuthProvider } from './services/utils/auth';

// Ponto de entrada da aplicação.
// Aqui o React é inicializado, os estilos globais são carregados
// e o provedor de autenticação é envolvido em torno das rotas do app.
createRoot(document.getElementById('root')).render(
  <StrictMode>
    <MyGlobalStyles />
    <AuthProvider>
      <RoutesApp />
    </AuthProvider>
  </StrictMode>,
);