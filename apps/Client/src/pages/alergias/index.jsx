import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { buildApiUrl } from '../../config/api';


function Alergias() {
  const navigate = useNavigate();



      const [tipo , setTipo] = useState('');
      const [descricao, setDescricao] = useState('');
      const [gravidade, setGravidade] = useState('');
      const [contra_medida, setcontra_medida] = useState('');

  

  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);



  // Recebe o formulário de cadastro, valida as informações e envia para a API.
  // Também verifica se o e-mail é válido e se a senha atende ao mínimo necessário.
  const handleSubmit = async (event) => {
    event.preventDefault();
    setMessage('');

    
    if (!tipo || !gravidade || !contra_medida) {
      setMessage('Preencha todos os campos.');
      return;
    }

   


    

    try {
      setLoading(true);
      const response = await fetch(
        buildApiUrl('alergia.php'),
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          credentials: 'include',
          body: JSON.stringify({
            tipo: tipo.trim(),
           
            gravidade: gravidade.trim(),
            contra_medida: contra_medida.trim()
          }),
        }
      );
      



       
        
      

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || 'Não foi possível enviar alergias.');
        return;
      }

      setMessage('Alergia cadastrada com o sucesso!');
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
      <C.Title>  </C.Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>

          

         

          <select value={tipo} onChange={(event) => setTipo(event.target.value)}>
            <option value="">Selecione o tipo de alergia</option>
            <option value="Alergias Respiratórias">Alergias Respiratórias</option>
            <option value="Alergias de Pele (Dermatológicas)">Alergias de Pele (Dermatológicas)</option>
            <option value="Alergias a Medicamentos">Alergias a medicamentos</option>
          
          </select>



       
        

          <select value={gravidade} onChange={(event) => setGravidade(event.target.value)}>
            <option value="">Selecione a gravidade da alergia</option>
            <option value="baixa">Leve</option>
            <option value="media">Moderada</option>
            <option value="alta">Grave</option>
          </select>



          <Input
            type="text"
            placeholder="Contra-Medida"
            value={contra_medida}
            onChange={(event) => setcontra_medida(event.target.value)}
          />
        

         


          {message && <p>{message}</p>}

          <Button
            Type="submit"
            Text={loading ? 'Enviando...' : 'Enviar'}
          />

        </C.Form>

        
      </C.Content>
    </C.Container>
  );
}

export default Alergias;