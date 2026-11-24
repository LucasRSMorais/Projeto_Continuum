import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import Home from '../pages/Doctor/Home';
import Login from '../pages/Doctor/Login';
import { useAuth } from '../services/utils/auth';
import Password from '../pages/Doctor/EmailVerify';
import NovaSenha from '../pages/Doctor/new_senha';
import PrimeiroAcessoPaciente from '../pages/Admin/CRUD/first_access';
import PatientRegister from '../pages/Admin/CRUD/patient_register';
import LoginPacientes from '../pages/Patient/Login_pacientes';
import HomePaciente from '../pages/Patient/Home_paciente';
import LoginSenha from '../pages/Patient/PasswordReset';
import NovaSenhaPaciente from '../pages/Patient/NovaSenha';
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
        <Route path="/Medico/Reset/buscaEmail" element={<Password />} />
        <Route path="/Medico/Reset/Redefinir" element={<NovaSenha />} />
        <Route path="/pass" element={<NovaSenha />} />

        {/* Fluxo específico para pacientes */}
        <Route path="/Paciente/login" element={<LoginPacientes />} />
        <Route path="/Paciente/home" element={<PrivateRoute><HomePaciente /></PrivateRoute>} />
        <Route path="/Paciente/perfil" element={<PrivateRoute><PerfilPaciente /></PrivateRoute>} />
        <Route path="/Paciente/privacidade" element={<PrivateRoute><Privacidade /></PrivateRoute>} />
        <Route path="/Paciente/recuperacao" element={<LoginSenha />} />
        <Route path="/Paciente/Reset/Redefinir" element={<NovaSenhaPaciente />} />
        <Route path="/Paciente/consentimentos" element={<PrivateRoute><Consetimento /></PrivateRoute>} />

        {/* Páginas administrativas e de configuração */}
        <Route path="/ADM/cadastrar_pacientes" element={<PatientRegister />} />
        <Route path="/Paciente/primeiro_acesso_paciente" element={<PrimeiroAcessoPaciente />} />

        {/* Fluxo específico para profissionais */}
        <Route path="/Medico/home" element={<PrivateRoute><Home /></PrivateRoute>} />

        {/* Compatibilidade com URLs antigas usadas por links externos e favoritos */}
        <Route path="/password" element={<Navigate to="/Medico/Reset/buscaEmail" replace />} />
        <Route path="/novaSenha" element={<Navigate to="/Paciente/Reset/Redefinir" replace />} />
        <Route path="/recuperacao" element={<Navigate to="/Paciente/recuperacao" replace />} />
        <Route path="/login_pacientes" element={<Navigate to="/Paciente/login" replace />} />
        <Route path="/home_paciente" element={<Navigate to="/Paciente/home" replace />} />
        <Route path="/perfilpaciente" element={<Navigate to="/Paciente/perfil" replace />} />
        <Route path="/privacidade" element={<Navigate to="/Paciente/privacidade" replace />} />
        <Route path="/consentimentos" element={<Navigate to="/Paciente/consentimentos" replace />} />
        <Route path="/consetimentos" element={<Navigate to="/Paciente/consentimentos" replace />} />
        <Route path="/revogar" element={<Navigate to="/Paciente/consentimentos" replace />} />
        <Route path="/cadastrar_pacientes" element={<Navigate to="/ADM/cadastrar_pacientes" replace />} />
        <Route path="/primeiro_acesso_paciente" element={<Navigate to="/Paciente/primeiro_acesso_paciente" replace />} />
        <Route path="/home" element={<Navigate to="/Medico/home" replace />} />

        {/* Qualquer rota desconhecida retorna para o login */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
};

export default RoutesApp;
