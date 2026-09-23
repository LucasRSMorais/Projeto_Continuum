import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { buildApiUrl } from '../../config/api';
import { IMaskInput1 } from './styles';

// Página de cadastro de novos usuários.
// Em ambiente de teste, ela registra um usuário com perfil padrão e redireciona para o login.
function Register_Pacientes() {
  const navigate = useNavigate();

  const [nome, setNome] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [dataNascimento, setDataNascimento] = useState('');
  const [sexo, setSexo] = useState('');
  const [etnia, setEtnia] = useState('');
  const [telefone, setTelefone] = useState('');
  const [alergias, setAlergias] = useState('');
  const [endereco, setEndereco] = useState('');
  const [aceiteTermo, setAceiteTermo] = useState(false);
  const [erro, setErro] = useState("");
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setMessage('');
    const normalizedEmail = email.trim().toLowerCase();

    
    const normalizedPassword = password;
    if (!nome.trim() || !normalizedEmail || !normalizedPassword || !sexo || !endereco.trim() || !telefone || dataNascimento){
      setMessage('Preencha nome, e-mail, senha, sexo , endereço , telefone e data nascimento.');
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedEmail)) {
      setMessage('Digite um email válido.');
      return;
    }
    if (!sexo) {
      setErro('Selecione o sexo do paciente.');
      return;
    }

    if (normalizedPassword.length < 6) {
      setMessage('A senha deve ter pelo menos 6 caracteres.');
      return;
    }

    if (!aceiteTermo) {
      setMessage('Você deve aceitar a política de privacidade.');
      return;
    }

    try {
      setLoading(true);
      const response = await fetch(
         buildApiUrl('register_paciente.php'),
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nome_completo: nome.trim(),
            email: normalizedEmail,
             data_nascimento : dataNascimento ,
            senha: normalizedPassword,
            sexo: sexo,
            etnia: etnia || null,
            telefone : telefone,
            aceite_termo: aceiteTermo ? 'sim' : 'nao',
            alergias: alergias || null,
            endereco: endereco.trim(),

            //fk_medic_id: null,
           
          }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || 'Não foi possível realizar o cadastro.');
        return;
      }

      setMessage('Cadastro realizado com sucesso!');

      setTimeout(() => {
        navigate('/login_pacientes');
      }, 1000);

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
      <C.Title>SISTEMA DE CADASTRO PACIENTES</C.Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>
              <h3>Dados Pessoais</h3>

          <Input
            type="text"
            placeholder="Nome"
            value={nome}
            onChange={(event) => setNome(event.target.value)}
          />

          <Input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
          />

          <Input
            type="password"
            placeholder="Senha"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
          />
         
         <Input
            type="tel"
            placeholder="(11) 99999-9999"
            value={telefone}
            maxLength={15}
            onChange={(event) => setTelefone(event.target.value)}
          />
          
          <Input
            type="data"
            placeholder="data de nascimento"
            value={FormData.dataNascimento}
            
            onChange={(event) => setDataNascimento(event.target.value)}
          />
          






          <Input
            type="text"
            placeholder="Endereço"
            value={endereco}
            onChange={(event) => setEndereco(event.target.value)}
          />
          <hr />
          <h3>Dados complementares</h3>
          <select 
            value={sexo}
            onChange={(event) => setSexo(event.target.value)}
          >
            <option value="">Selecione o sexo</option>
            <option value="masculino">Masculino</option>
            <option value="feminino">Feminino</option>
            <option value="outro">Outro</option>
            <option value="nao_informado">Não informado</option>
          </select>

          <Input
            type="text"
            placeholder="Etnia"
            value={etnia}
            onChange={(event) => setEtnia(event.target.value)}
          />

          <Input
            type="text"
            placeholder="Alergias"
            value={alergias}
            onChange={(event) => setAlergias(event.target.value)}
          />
          <hr />
            <h3>Política de Privacidade</h3>
            <a href="/politica_privacidade.pdf" target="_blank" rel="noopener noreferrer">
            Ver Política de Privacidade
          </a>

          <label >
            <input
              type="checkbox"
              checked={aceiteTermo}
              onChange={(event) => {
                setAceiteTermo(event.target.checked);
                setErro("");
              }}
            />
              Concordo com a Política de Privacidade
          </label>

          <p>Versão do Consetimento V1.0</p>

          {message && <p>{message}</p>}

          <Button
            Type="submit"
            Text={loading ? 'Cadastrando...' : 'Cadastrar'}
          />

        </C.Form>

        <Link to="/login_pacientes">{' '}
                <Button  type="submit"> Você ja tem conta ?</Button>
                 </Link> 
       

      </C.Content>
    </C.Container>
  );
}

export default Register_Pacientes;