import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { IMaskInput1 } from './styles';

// Página de cadastro de novos usuários.
// Em ambiente de teste, ela registra um usuário com perfil padrão e redireciona para o login.
function Register_Pacientes() {
  const navigate = useNavigate();

  const [nome, setNome] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [cpf, setCpf] = useState('');
  const [endereco, setEndereco] = useState('');
  const [sexo, setSexo] = useState('');
  const [raça, setRaça] = useState('');
  const [doença, setDoença] = useState('');
  const [dados_consetimento, setDados_consetimento] = useState("");
  const [erro, setErro] = useState("");
  const [erro_dados , setDados] = useState('');
  const [assinado, setAssinado] = useState('');
  const [data_assinatura, setData_assinatura] = useState('');

  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);


  const handleSubmit = async (event) => {
    event.preventDefault();
    setMessage('');
    const normalizedEmail = email.trim().toLowerCase();

    
    const normalizedPassword = password;
    if (!nome.trim() || !normalizedEmail || !normalizedPassword || !cpf.trim() || !endereco.trim() ){
      setMessage('Preencha todos os campos de dados pessoais.');
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedEmail)) {
      setMessage('Digite um email válido.');
      return;
    }
    if (!sexo || !raça || !doença) {
      setDados('Preencha todos os campos de dados sensíveis.');
      return;
    }

    if (normalizedPassword.length < 6) {
      setMessage('A senha deve ter pelo menos 6 caracteres.');
      return;
    }

    if (!dados_consetimento) {
      setMessage('Você deve aceitar a política de privacidade.');
      return;
    }

    try {
      setLoading(true);
      const response = await fetch(
        'http://localhost:8000/api/register_paciente.php',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nome: nome.trim(),
            email: normalizedEmail,
            senha: normalizedPassword,
            cpf: cpf.trim(),
            endereco: endereco.trim(),
            sexo: sexo,
            raça: raça,
            doença: doença,
            
          
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
         
          <IMaskInput1
            mask="000.000.000-00"
            placeholder="CPF"
            value={cpf}
            onChange={(event) => setCpf(event.target.value)}
          />
          <Input
            type="text"
            placeholder="Endereço"
            value={endereco}
            onChange={(event) => setEndereco(event.target.value)}
          />
          <hr />
          <h3>Dados Sensíveis</h3>
          <select 
            value={sexo}
            onChange={(event) => setSexo(event.target.value)}
          >
            <option value="">Selecione o sexo</option>
            <option value="masculino">Masculino</option>
            <option value="feminino">Feminino</option>
          </select>

            <select  value={raça} onChange={(event) => setRaça(event.target.value)}>
            <option value="">Selecione a raça</option>
            <option value="branco">Branco</option>
            <option value="negro">Negro</option>
            <option value="pardo">Pardo</option>
          </select>
          <select  value={doença} onChange={(event) => setDoença(event.target.value)}>
            <option value="">Selecione a doença</option>
            <option value="hipertensao">Hipertensão</option>
            <option value="diabetes">Diabetes</option>
             <option value="cancer">Câncer</option>
          </select>
          <hr />
            <h3>Politica de Privacidade</h3>
            <a href="/politica_privacidade.pdf" target="_blank" rel="noopener noreferrer">
            Ver Política de Privacidade
          </a>
          
          

          <label >
            <input
              type="checkbox"
              
              checked={dados_consetimento}
              onChange={(event) => {
                setDados_consetimento(event.target.checked);
                setErro("");
               
              }}
            />
            
              Concordo com a Política de Privacidade
          </label>
        

         

          {message && <p>{message}</p>}

          <Button
            Type="submit"
            Text={loading ? 'Cadastrando...' : 'Cadastrar'}
          />

        </C.Form>

        <p>
          Já tem conta? <Link to="/login_pacientes">Entrar</Link>
        </p>
      </C.Content>
    </C.Container>
  );
}

export default Register_Pacientes;