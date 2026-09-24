import { useAuth } from '../../services/utils/auth';
import { Button } from '../../components/Button';
import './styles.css'
import { useNavigate } from 'react-router-dom';
import * as C from './styles';
import { Link } from 'react-router-dom';
// Página inicial do app após o usuário já estar autenticado.
// Ela mostra uma mensagem de boas-vindas e oferece a opção de sair da conta.
function HomePaciente() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  // No clique do botão, o sistema desloga o usuário e manda para a tela de login.

  return (
<>

<header>
<br /> <br />
<h2>Continuum</h2>

<nav>

<Link className='link' to="/" >

Inicio
</Link>

<Link className='link' to="/" > Exames </Link>

<Link className='link' to="/" > Perfil </Link>

</nav>
</header>


    <C.Conteiner>
      <C.Title>Home</C.Title>
      <p>Seja bem-vindo  {user?.email || 'usuário'}! </p>
      <Button type="button" Text="Sair" onClick={() => { logout(); navigate('/login_pacientes'); }}>
        Sair
      </Button>
    </C.Conteiner>
    </>
  );
}

export default HomePaciente;
