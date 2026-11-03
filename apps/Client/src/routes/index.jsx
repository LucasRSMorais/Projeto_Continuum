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
import Endereco from '../pages/endereco';
import Configuracao from '../pages/configuracao';
import LoginSenha from '../pages/passoword2';
import NovaSenha2 from '../pages/new_senha2';
import Consetimento from '../pages/consentimentos';

// Guarda de rota: impede que usuários não autenticados acessem páginas privadas.
// Enquanto a sessão está sendo verificada, o usuário recebe uma mensagem de carregamento.
const PrivateRoute = ({ children }) => {
  const { isAuthenticated, loading } = useAuth();

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
        <Route path="/register" element={<Register />} />
        <Route path="/password" element={<Password />} />
        <Route path="/novaSenha" element={<NovaSenha2 />} />
        <Route path="/pass" element={<NovaSenha />} />
        <Route path="/recuperacao" element={<LoginSenha />} />

        {/* Fluxo específico para pacientes */}
        <Route path="/register_pacientes" element={<Register_Pacientes />} />
        <Route path="/login_pacientes" element={<LoginPacientes />} />
        <Route path="/home_paciente" element={<PrivateRoute><HomePaciente /></PrivateRoute>} />

        {/* Páginas administrativas e de configuração */}
        <Route path="/configuracao" element={<Configuracao />} />
        <Route path="/deleta_conta" element={<DeletaConta />} />
        <Route path="/consultar" element={<ConsultarConta />} />
        <Route path="/consetimentos" element={<Consetimento />} />
        <Route path="/revogar" element={<Revogacao />} />
        <Route path="/endereco" element={<Endereco />} />

        {/* Página inicial protegida do médico */}
        <Route path="/home" element={<PrivateRoute><Home /></PrivateRoute>} />

        {/* Qualquer rota desconhecida retorna para o login */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
};

export default RoutesApp;


