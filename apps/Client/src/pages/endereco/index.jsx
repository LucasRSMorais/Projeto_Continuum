import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { buildApiUrl } from '../../config/api';


const formatcep = (value) => {

  const digitsOnly = value.replace(/\D/g, '').slice(0, 8  ); // Remove tudo que não é dígito


if (digitsOnly.length > 5) {

return digitsOnly.replace(/(\d{5})(\d{1,3})/, '$1-$2'); // Adiciona o hífen após os 5 primeiros dígitos


}

return digitsOnly;
};


// Página de cadastro de novos usuários.
// Em ambiente de teste, ela registra um usuário com perfil padrão e redireciona para o login.
function Endereco() {
  const navigate = useNavigate();

  const [cep, setcep] = useState('');
   const [cidade, setCidade] = useState('');
   const [estado, setEstado] = useState('');
    const[rua, setRua] = useState('');
    const[numero, setNumero] = useState('');

  

  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);



  // Recebe o formulário de cadastro, valida as informações e envia para a API.
  // Também verifica se o e-mail é válido e se a senha atende ao mínimo necessário.
  const handleSubmit = async (event) => {
    event.preventDefault();
    setMessage('');

    
    if (!cep|| !cidade || !estado || !rua || !numero) {
      setMessage('Preencha todos os campos.');
      return;
    }

   


    

    try {
      setLoading(true);
      const response = await fetch(
        buildApiUrl('endereco.php'),
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          credentials: 'include',
          body: JSON.stringify({
          cep: cep.replace(/\D/g, ''), // Remove qualquer caractere que não seja dígito
          cidade: cidade.trim(),
          estado: estado.trim(),
          rua: rua.trim(),
          numero: numero.trim()
          }),
        }
      );
      



       
        
      

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || 'Não foi possível realizar o endereço.');
        return;
      }

      setMessage('Endereço cadastrado com o sucesso!');
      navigate('/login_pacientes', { replace: true });

    } catch (error) {
      console.error(error);
      setMessage(
        'Não foi possível conectar ao servidor.'
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <C.Container>
      <C.Title>Informe o seu endereço</C.Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>

          <Input
            type="text"
            placeholder="CEP"
            maxLength={8}
            value={cep}
            onChange={(event) => setcep(formatcep(event.target.value))}
          />

          <Input
            type="text"
            placeholder="Cidade"
            value={cidade}
            onChange={(event) => setCidade(event.target.value)}
          />

          <Input
            type="text"
            placeholder="Estado"
            value={estado}
            onChange={(event) => setEstado(event.target.value)}
          />

         
         <Input 
            type="text"
            placeholder="Rua"
            value={rua}
            onChange={(event) => setRua(event.target.value)}

          />
           
           <Input
            type="text"
            placeholder="Número"
            value={numero}
            onChange={(event) => setNumero(event.target.value)}
          />



          {message && <p>{message}</p>}

          <Button
            Type="submit"
            Text={loading ? 'Cadastrando...' : 'Cadastrar'}
          />

        </C.Form>

        
      </C.Content>
    </C.Container>
  );
}

export default Endereco;