import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';
import { buildApiUrl } from '../../config/api';

import { FaTrash } from 'react-icons/fa';

function ConsultarConta() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();
  const[mostrarConfirmacao, setMostrarConfirmacao] = useState(false);

  // Estado dos campos do formulário e das mensagens de erro.
  const [email, setEmail] = useState('');
  const [usuario, setUsuario] = useState(null);
  const [error, setError] = useState('');
const {user} = useAuth();

const consultarUsuario = async (event) => {
    event.preventDefault();
    setError('');
    setUsuario(null);
    {/*if (!email) {
      setError('Digite o email.');
      return;
    }
    */}
    try {
      const response = await fetch(
         buildApiUrl('consultar.php'),
        
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: user?.email,
          }),
        }
      );
      const data = await response.json();
      if(data.success){
        setUsuario(data.usuario);
      } else {
        setError(data.message || 'Usuário não encontrado.');
      }
    } catch (error) {
      console.error(error);
      setError('Erro ao consultar usuário.');
    }
}

const exportarUsuario = async (event) => {
    event.preventDefault();
    if (!usuario) {
      setError('Nenhum usuário para exportar.');
      return;
    }
{/*window.location.href = `http://localhost:8000/api/exportar.php?email=${encodeURIComponent(email)}`;
*/}

window.location.href = `${buildApiUrl('exportar.php')}?email=${encodeURIComponent(user?.email)} `;

};
    
  


return(
<C.Container>
      <Title>Consultar Conta</Title>

      <C.Content>
        <C.Form onSubmit={consultarUsuario}>
          <Input type="email" placeholder="Email" value={user?.email|| ""}
          readOnly
            
          />
         
          {error && (<C.labelError>{error}</C.labelError>)}

          <Button type="submit">Consultar</Button>
        </C.Form>
         <Link to="/login_pacientes">{' '}
                          <Button  type="submit"> Voltar para tela de login </Button>
                           </Link> 

      
       
          
         
      </C.Content>

      {usuario &&  (<C.DadoUsuario>
        <h2>Informações do Usuário</h2>
        <p><strong>Nome:</strong> {usuario.nome_completo}</p>
          <p><strong>Email:</strong> {usuario.email}</p>
           <p><strong>Telefone</strong> {usuario.telefone}</p>
          <p><strong>Endereço:</strong> {usuario.endereco}</p>
          <p><strong>Sexo:</strong> {usuario.sexo}</p>
          <p><strong>Data de Nascimento:</strong> {usuario.data_nascimento}</p>
          <Button type="submit" onClick={exportarUsuario}>
            Exportar Dados
          </Button>
          
        
      </C.DadoUsuario>)}


    </C.Container>



);


}

export default ConsultarConta;