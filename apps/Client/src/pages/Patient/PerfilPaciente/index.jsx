import { useAuth } from '../../../services/utils/auth';
import HeaderPaciente from '../../../components/layout/Patient/HeaderPaciente';
import Main from '../../../components/layout/Patient/Mains/MainProfile';
import Footer from '../../../components/layout/Footer';

import { useNavigate } from 'react-router-dom';
import * as C from './styles';
import { Link } from 'react-router-dom';

// Página inicial do app após o usuário já estar autenticado.
// Ela mostra uma mensagem de boas-vindas e oferece a opção de sair da conta.
function PerfilPaciente() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  // No clique do botão, o sistema desloga o usuário e manda para a tela de login.

  return (
    <C.PageShell>
      <HeaderPaciente />
      <C.PageLayout>
        <C.Conteiner>
          <Main />
        </C.Conteiner>
      </C.PageLayout>
      <Footer />
    </C.PageShell>
  );
}
 
export default PerfilPaciente;