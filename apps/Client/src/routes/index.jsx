import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import Home from '../pages/Doctor/Home';
import Login from '../pages/Doctor/Login';
import { useAuth } from '../services/utils/auth';
import Password from '../pages/password';
import NovaSenha from '../pages/new_senha';
import PrimeiroAcessoPaciente from '../pages/Admin/CRUD/first_access';
import PatientRegister from '../pages/Admin/CRUD/patient_register';
import LoginPacientes from '../pages/Patient/Login_pacientes';
import HomePaciente from '../pages/Patient/Home_paciente';
import LoginSenha from '../pages/passoword2';
import NovaSenha2 from '../pages/new_senha2';
import Consetimento from '../pages/Patient/consentimentos';
import PerfilPaciente from '../pages/Patient/PerfilPaciente';
import Privacidade from '../pages/Patient/Privacidade';
const devAuthBypass = import.meta.env.DEV
  && import.meta.env.VITE_DEV_AUTH_BYPASS === 'true';

const PrivateRoute = ({ children }) => {
  const { isAuthenticated, loading } = useAuth();

  if (devAuthBypass) {
    return children;
  }

  if (loading) {
    return <div>Verificando autenticação...</div>;
  }

  return isAuthenticated ? children : <Navigate to="/" replace />;
};

// Define o conjunto de rotas da aplicação.
// Centralizar a navegação aqui facilita o controle de acesso,
// a manutenção e a organização das telas do sistema.
const RoutesApp = () => {
  return (
    <BrowserRouter>
      <Routes>
        {/* Fluxo de autenticação e recuperação de senha do profissional de saúde */}
        <Route path="/" element={<Login />} />
        <Route path="/password" element={<Password />} />
        <Route path="/novaSenha" element={<NovaSenha2 />} />
        <Route path="/pass" element={<NovaSenha />} />
        <Route path="/recuperacao" element={<LoginSenha />} />

        {/* Fluxo específico para pacientes */}
        <Route path="/login_pacientes" element={<LoginPacientes />} />
        <Route path="/home_paciente" element={<PrivateRoute><HomePaciente /></PrivateRoute>} />
        <Route path="/perfilpaciente" element={<PrivateRoute><PerfilPaciente /></PrivateRoute>} />
        <Route path="/privacidade" element={<PrivateRoute><Privacidade /></PrivateRoute>} />


        {/* Páginas administrativas e de configuração */}
        <Route path="/consentimentos" element={<PrivateRoute><Consetimento /></PrivateRoute>} />
        <Route path="/consetimentos" element={<Navigate to="/consentimentos" replace />} />
        <Route path="/revogar" element={<Navigate to="/consentimentos" replace />} />
        <Route path="/cadastrar_pacientes" element={<PatientRegister />} />
        <Route path="/primeiro_acesso_paciente" element={<PrimeiroAcessoPaciente />} />


        {/* Fluxo específico para profissionais */}
        <Route path="/home" element={<PrivateRoute><Home /></PrivateRoute>} />

        {/* Qualquer rota desconhecida retorna para o login */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
};

export default RoutesApp;
