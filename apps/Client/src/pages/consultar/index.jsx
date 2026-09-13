import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';
import { FaTrash } from 'react-icons/fa';

function ConsultarConta() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();
  const[mostrarConfirmacao, setMostrarConfirmacao] = useState(false);

  // Estado dos campos do formulário e das mensagens de erro.
  const [email, setEmail] = useState('');
  const [usuario, setUsuario] = useState(null);
  const [error, setError] = useState('');


const consultarUsuario = async (event) => {
    event.preventDefault();
    setError('');
    setUsuario(null);
    if (!email) {
      setError('Digite o email.');
      return;
    }
    try {
      const response = await fetch(
        'http://localhost:8000/api/consultar.php',
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: email,
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
window.location.href = `http://localhost:8000/api/exportar.php?email=${encodeURIComponent(email)}`;


};
    
  


return(
<C.Container>
      <Title>Consultar Conta</Title>

      <C.Content>
        <C.Form onSubmit={consultarUsuario}>
          <Input type="email" placeholder="Email" value={email}
            onChange={(event) => {
              setEmail(event.target.value);
              setError('');
            }}
          />
         
          {error && (<C.labelError>{error}</C.labelError>)}

          <Button type="submit">Consultar</Button>
        </C.Form>

      
        <C.LabelSignup>
          Não tem uma conta?
          <C.Strong>
            <Link to="/register_pacientes">{' '}Clique aqui</Link>
          </C.Strong>
        </C.LabelSignup>
        <C.LabelSignup>
          Deleta conta!
          <C.Strong>
            <Link to="/deleta_conta">{' '}Clique aqui</Link>
          </C.Strong>
        </C.LabelSignup>
           <C.LabelSignup>
          Revogação de Conta
          <C.Strong>
            <Link to="/revogar">{' '}Clique aqui</Link>
          </C.Strong>
        </C.LabelSignup>

       
          
         
      </C.Content>

      {usuario &&  (<C.DadoUsuario>
        <h2>Informações do Usuário</h2>
        <p><strong>Nome:</strong> {usuario.nome}</p>
          <p><strong>Email:</strong> {usuario.email}</p>
           <p><strong>CPF:</strong> {usuario.cpf}</p>
          <p><strong>Endereço:</strong> {usuario.endereço}</p>
          <Button type="submit" onClick={exportarUsuario}>
            Exportar Dados
          </Button>
        
      </C.DadoUsuario>)}


    </C.Container>



);


}

export default ConsultarConta;