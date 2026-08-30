import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import MyGlobalStyles from './styles/globalStyles';
import RoutesApp from './routes';
import { AuthProvider } from './services/utils/auth';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <MyGlobalStyles />
    <AuthProvider>
      <RoutesApp />
    </AuthProvider>
  </StrictMode>,
);