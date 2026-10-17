import { useAuth } from '../../services/utils/auth';
import { Button } from '../../components/Button';


import HeaderPaciente from '../../components/layout/HeaderPaciente';
import SidebarPaciente from '../../components/layout/SidebarPaciente';

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

  <C.PageShell>

        
      <HeaderPaciente/>
  
      <C.PageLayout>
        <SidebarPaciente />
        <C.Conteiner>

              
            

          

        </C.Conteiner>
      </C.PageLayout>
      
    </C.PageShell>




    
  );
}

export default HomePaciente;
