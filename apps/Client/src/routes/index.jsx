import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import Home from '../pages/Home';
import Login from '../pages/Login';
import Register from '../pages/Register';
import { useAuth } from '../services/utils/auth';

// Guarda de rota: impede que usuários não autenticados acessem páginas privadas.
// Enquanto a sessão está sendo verificada, mostra uma tela de carregamento.
const PrivateRoute = ({ children }) => {
  const {isAuthenticated, loading} = useAuth();
  if (loading) {
    return <div>Verificando autenticação...</div>;
  }
  return isAuthenticated
    ? children
    : <Navigate to="/" replace />;
};

// Define as rotas da aplicação.
// A página /home é protegida; as demais podem ser acessadas livremente.
const RoutesApp = () => {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route
          path="/home"
          element={
            <PrivateRoute>
              <Home />
            </PrivateRoute>
          }
        />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
};

export default RoutesApp;


