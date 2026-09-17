import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { data, Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';
import { FaTrash } from 'react-icons/fa';

function revogacao() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();
  const[mostrarConfirmacao, setMostrarConfirmacao] = useState(false);

  // Estado dos campos do formulário e das mensagens de erro.
  const [email, setEmail] = useState('');
  const [usuario, setUsuario] = useState(null);
  const [error, setError] = useState('');
   const [message, setMessage] = useState('');


const revogarConta = async(event)=>{
    event.preventDefault();
    setMessage('');
    setError('');



if (!email){

  setError("Informe o Email");
  return ;
}



try{

const response = await fetch(
  'http://localhost:8000/api/revoga_conta.php',
  {
    method : 'POST',
    credentials : 'include',
    headers:{
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
            email : email
            
          
          }),
  }

);

  const dado1 = await response.json();

  if (dado1.success){
     setMessage(dado1.message);

  }else{
    setError(dado1.message||"Não foi possivel revogar");

  }


}catch (error){
console.error(error);
setError("Erro ao revogar")
}



}
  


return(
<C.Container>
      <Title>Revogação de Conta</Title>

      <C.Content>
        <C.Form onSubmit={revogarConta}>
          <Input type="email" placeholder="Email" value={email}
            onChange={(event) => {
              setEmail(event.target.value);
              setError('');
            }}
          />
         
          {error && (<C.labelError>{error}</C.labelError>)}

          <Button type="submit">Revogar</Button>
        </C.Form>
        {message && <p>{message}</p>}

      
        <Link to="/consultar">{' '}
                <Button  type="submit"> Voltar para tela de consultar</Button>
                 </Link> 
       
        

       
          
         
      </C.Content>

      

    </C.Container>



);


}

export default revogacao;