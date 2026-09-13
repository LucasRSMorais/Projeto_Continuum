import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';
import { FaTrash } from 'react-icons/fa';

function DeletaConta() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();
  const[mostrarConfirmacao, setMostrarConfirmacao] = useState(false);

  // Estado dos campos do formulário e das mensagens de erro.
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
const [message, setMessage] = useState('');




 const deletarConta = async (event) => {
    event.preventDefault();
    setError('');
    setMessage('');

   
    if (!email || !password) {
      setError('Preencha email e senha para conseguir deletar a conta.');
      return;
    }
    const confirmacao = window.confirm('Tem certeza que deseja deletar sua conta? Esta ação não pode ser desfeita.');
    if (!confirmacao) {
      return;
    }

    try {
      const response = await fetch(
        'http://localhost:8000/api/confirmar_deleta.php',{
          method: 'DELETE',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: email,
            senha: password,
          }),
        }
      );

      const dados = await response.json();
      if(dados.success){
        setMessage('Conta deletada com sucesso.');
        setEmail('');
        setPassword('');
       setTimeout(() => {
          navigate('/login_pacientes');
        }, 2000); 
       
      }else{
        setError(dados.message || 'Não foi possível deletar a conta.');
      }

    } catch (error) {
      console.error(error);
      setError('Ocorreu um erro ao tentar deletar a conta.');
    }
  };


// Aqui é renderizado o formulário de login, com campos para e-mail e senha, além de um botão para enviar os dados
  return (
    <C.Container>
      <Title>DELETAR CONTA</Title>
    

      <C.Content>
        <C.Form onSubmit={deletarConta}>
          <Input type="email" placeholder="Email" value={email}
            onChange={(event) => {
              setEmail(event.target.value);
              setError('');
            }}
          />
          <Input type="password" placeholder="Password" value={password} onChange={(event) => {
              setPassword(event.target.value);
              setError('');
            }}
          />
          {error && (<C.labelError>{error}</C.labelError>)}

          <Button   type="submit"> Deletar Conta</Button>
        </C.Form>

      
        <C.LabelSignup>
          Não tem uma conta?
          <C.Strong>
            <Link to="/register_pacientes">{' '}Clique aqui</Link>
          </C.Strong>
        </C.LabelSignup>

         
      </C.Content>

{message&&(<C.Confirmacao>

<h1>Conta deletada com sucesso!</h1>

<p>Sua conta foi deletada com sucesso.</p>


</C.Confirmacao>)}


    </C.Container>
  );
}

export default DeletaConta;