import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import MyGlobalStyles from './styles/globalStyles';
import RoutesApp from './routes';
import { AuthProvider } from './services/utils/auth';

// Ponto de entrada principal da aplicação React.
// Esta etapa inicializa a interface, aplica os estilos globais e
// encapsula toda a navegação dentro de um provedor de autenticação.
// Assim, todas as páginas e componentes conseguem consultar o estado da sessão.
createRoot(document.getElementById('root')).render(
  <StrictMode>
    <MyGlobalStyles />
    <AuthProvider>
      <RoutesApp />
    </AuthProvider>
  </StrictMode>,
);