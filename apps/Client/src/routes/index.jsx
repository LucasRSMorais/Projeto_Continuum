import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import Home from '../pages/Home';
import Login from '../pages/Login';
import Register from '../pages/Register';
import { useAuth } from '../services/utils/auth';
import Password from '../pages/password';
import NovaSenha from '../pages/new_senha';
import Register_Pacientes from '../pages/Registrer_pacientes';
import LoginPacientes from '../pages/Login_pacientes';
import HomePaciente from '../pages/Home_paciente';
import DeletaConta from '../pages/deleta_conta';
import ConsultarConta from '../pages/consultar';
import Revogacao from '../pages/revogar';
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
         <Route path="/password" element={<Password />} />
         <Route path="/pass" element={<NovaSenha />} />
        <Route path="/register_pacientes" element={<Register_Pacientes />} />
        <Route path="/login_pacientes" element={<LoginPacientes />} />
        <Route path = "/home_paciente" element = {<HomePaciente />} />
        <Route path="/deleta_conta" element={<DeletaConta />} />
        <Route path="/consultar" element={<ConsultarConta />} />
        <Route path="/revogar" element={<Revogacao />} />
        
        

         
        
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


