import { useAuth } from '../../services/utils/auth';
import { Button } from '../../components/Button';
import { useNavigate } from 'react-router-dom';
import * as C from './styles';

// Página inicial do app após o usuário já estar autenticado.
// Ela mostra uma mensagem de boas-vindas e oferece a opção de sair da conta.
function Home() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  // No clique do botão, o sistema desloga o usuário e manda para a tela de login.

  return (
    <C.Conteiner>
      <C.Title>Home</C.Title>
      <p>Bem-vindo, {user?.email || 'usuário'}!</p>
      <Button type="button" Text="Sair" onClick={() => { logout(); navigate('/'); }}>
        Sair
      </Button>
    </C.Conteiner>
  );
}

export default Home;
