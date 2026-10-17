import { useAuth } from '../../services/utils/auth';
import { Button } from '../../components/Button';
import { useNavigate } from 'react-router-dom';
import Header from '../../components/layout/Header';
import Sidebar from '../../components/layout/Sidebar';
import Main from '../../components/layout/Main';
import * as C from './styles';
import Footer from '../../components/layout/Footer';

// informações
// Página inicial do app após o usuário já estar autenticado.
// Ela mostra uma mensagem de boas-vindas e oferece a opção de sair da conta.
// home do usuario
function Home() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  // No clique do botão, o sistema desloga o usuário e manda para a tela de login.

  return (
   
    <C.PageShell>

        
      <Header/>
  
      <C.PageLayout>
        <Sidebar />
        <C.Conteiner>

         <Main/>     
            

              {
                /*
             
          <C.Title>Home</C.Title>
        
          <Button type="button" Text="Sair" onClick={() => { logout(); navigate('/'); }}>
            Sair
          </Button>

           */}

        </C.Conteiner>
      </C.PageLayout>
      <Footer />
    </C.PageShell>

  );
}

export default Home;
