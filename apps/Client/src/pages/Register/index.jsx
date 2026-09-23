import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { buildApiUrl } from '../../config/api';

const formatCRM = (value) => value.replace(/\D/g, '').slice(0, 6);


// Página de cadastro de novos usuários.
// Em ambiente de teste, ela registra um usuário com perfil padrão e redireciona para o login.
function Register() {
  const navigate = useNavigate();

  const [nome, setNome] = useState('');
  const [crm, setCrm] = useState('');
  const [crmUf, setCrmUf] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);

  // Recebe o formulário de cadastro, valida as informações e envia para a API.
  // Também verifica se o e-mail é válido e se a senha atende ao mínimo necessário.
  const handleSubmit = async (event) => {
    event.preventDefault();
    setMessage('');
    const normalizedEmail = email.trim().toLowerCase();

    // Não usamos trim() na senha.
    // Espaços podem fazer parte de uma senha.
    const normalizedPassword = password;
    if (!nome.trim() || !crm || !crmUf || !normalizedEmail || !normalizedPassword) {
      setMessage('Preencha nome completo, CRM, UF do CRM, e-mail e senha.');
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedEmail)) {
      setMessage('Digite um email válido.');
      return;
    }

    if (normalizedPassword.length < 6) {
      setMessage('A senha deve ter pelo menos 6 caracteres.');
      return;
    }

    try {
      setLoading(true);
      const response = await fetch(
        buildApiUrl('register.php'),
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nome_completo: nome.trim(),
            email: normalizedEmail,
            senha: normalizedPassword,
            crm : crm,
            crm_uf: crmUf,
            cargo: 'medico',
          }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || 'Não foi possível realizar o cadastro.');
        return;
      }

      setMessage('Cadastro realizado com sucesso!');
      navigate('/', { replace: true });

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
      <C.Title>SISTEMA DE CADASTRO</C.Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>

          <Input
            type="text"
            placeholder="Nome"
            value={nome}
            onChange={(event) => setNome(event.target.value)}
          />

          <Input
            type="text"
            placeholder="Número do CRM"
            value={crm}
            maxLength={6}
            onChange={(event) => setCrm(formatCRM(event.target.value))}
          />
          
          <select value={crmUf} onChange={(e) => setCrmUf(e.target.value)}>
            <option value="">UF do CRM</option>
            <option value="AC">Acre</option>
            <option value="AL">Alagoas</option>
            <option value="AP">Amapá</option>
            <option value="AM">Amazonas</option>
            <option value="BA">Bahia</option>
            <option value="CE">Ceará</option>
            <option value="DF">Distrito Federal</option>
            <option value="ES">Espírito Santo</option>
            <option value="GO">Goiás</option>
            <option value="MA">Maranhão</option>
            <option value="MT">Mato Grosso</option>
            <option value="MS">Mato Grosso do Sul</option>
            <option value="MG">Minas Gerais</option>
            <option value="PA">Pará</option>
            <option value="PB">Paraíba</option>
            <option value="PR">Paraná</option>
            <option value="PE">Pernambuco</option>
            <option value="PI">Piauí</option>
            <option value="RJ">Rio de Janeiro</option>
            <option value="RN">Rio Grande do Norte</option>
            <option value="RS">Rio Grande do Sul</option>
            <option value="RO">Rondônia</option>
            <option value="RR">Roraima</option>
            <option value="SC">Santa Catarina</option>
            <option value="SP">São Paulo</option>
            <option value="SE">Sergipe</option>
            <option value="TO">Tocantins</option>
          </select>

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

          {message && <p>{message}</p>}

          <Button
            Type="submit"
            Text={loading ? 'Cadastrando...' : 'Cadastrar'}
          />

        </C.Form>

        <p>
          Já tem conta? <Link to="/">Entrar</Link>
        </p>
      </C.Content>
    </C.Container>
  );
}

export default Register;