import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';

// Página de nova senha do sistema.
// Aqui o usuário informa a nova senha que vai ser registrada ,
// ele entra após o codigo inserido que foi enviado pelo email do usuario (2FA).


function nova_() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();

 
// Estado dos campos do formulário e das mensagens de erro.
 const [password, setPassword] = useState('');
  const [error, setError] = useState('');

 


  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');


    if (!password) {
      setError('Criar uma nova senha');
      return;
    }
    if (password < 6) {
      setMessage('A senha deve ter pelo menos 6 caracteres.');
      return;
    }




    try {
      const response = await fetch(
        'http://localhost:8000/api/senha.php',
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nova_senha: password,
          
          }),
        }
      );

      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Não foi possivel atualizar uma nova senha.');
        return;
      }

      
    

      navigate('/register');

    } catch (error) {
      console.error(error);
      setError('Não foi possível conectar ao servidor.');
    }
  };

  // Aqui vai ser ação da parte do react enviar a nova senha para API
  return (
    <C.Container>
      <Title>Redefinir a senha </Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>
         <Input
            type="password"
            placeholder="Senha"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
          />
          
          {error && (<C.labelError>{error}</C.labelError>)}
          
          <Button    type="submit"> Enviar</Button>
        </C.Form>

       
        
      </C.Content>
    </C.Container>
  );
}

export default nova_;