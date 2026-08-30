import { useAuth } from '../../services/utils/auth';
import { Button } from '../../components/Button';
import { useNavigate } from 'react-router-dom';
import * as C from './styles';

// Esta página é responsável por renderizar a tela inicial após o login do usuário
// Aqui o usuário pode ver uma mensagem de boas-vindas e um botão para sair do sistema

function Home() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  // Função que lida com o logout do usuário

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
