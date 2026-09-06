import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';

// Página de login do sistema.
// Aqui o usuário informa e-mail e senha e, se tudo estiver certo,
// ele entra no app ou precisa confirmar um código de segurança (2FA).
function Login() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();

  // Estado dos campos do formulário e das mensagens de erro.
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  // Estados do 2FA: indica se o usuário precisa validar o código
  // e guarda o valor do código informado.
  const [requires2FA, setRequires2FA] = useState(false);
  const [codigo, setCodigo] = useState('');
  const [codigoTeste, setCodigoTeste] = useState('');

  // Envia os dados de login para o backend.
  // Se a resposta indicar que o 2FA é obrigatório, a tela muda para a etapa de validação.
  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');

    const normalizedEmail = email.trim().toLowerCase();

    if (!normalizedEmail || !password) {
      setError('Preencha email e senha.');
      return;
    }

    try {
      const response = await fetch(
        'http://localhost:8000/api/login.php',
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: normalizedEmail,
            senha: password,
          }),
        }
      );

      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Email ou senha inválidos.');
        return;
      }

      // Login correto, mas ainda precisa do 2FA
      if (data.requires_2fa) {
        setCodigoTeste(data.codigo_teste);
        setRequires2FA(true);
        return;
      }

      navigate('/home');

    } catch (error) {
      console.error(error);
      setError('Não foi possível conectar ao servidor.');
    }
  };

  // Valida o código de segurança enviado pelo usuário.
  // Se o código estiver correto, a sessão é confirmada e o app redireciona para a home.
  const handleVerify2FA = async (event) => {
    event.preventDefault();
    setError('');

    if (!codigo.trim()) {
      setError('Digite o código de verificação.');
      return;
    }

    try {
      const response = await fetch(
        'http://localhost:8000/api/verify-2fa.php',
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            codigo: codigo.trim(),
          }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        setError(data.message || 'Código inválido.');
        return;
      }

      await checkSession();

      navigate('/home');

    } catch (error) {
      console.error(error);
      setError('Não foi possível conectar ao servidor.');
    }
  };

  // Quando o backend exige 2FA, a tela de login muda para esta etapa.
  if (requires2FA) {
    return (
      <C.Container>
        <Title>VERIFICAÇÃO DE SEGURANÇA</Title>
        <C.Content>
          <C.Form onSubmit={handleVerify2FA}>
            <p>
              Digite o código de verificação enviado.
            </p>
            {/* TEMPORÁRIO: apenas para testes */}
            <p>
              Código de teste: <strong>{codigoTeste}</strong>
            </p>
            <Input
              type="text"
              placeholder="Código de 6 dígitos"
              value={codigo}
              onChange={(event) => {
                setCodigo(event.target.value);
                setError('');
              }}
            />

            {error && (<C.labelError>{error}</C.labelError>)}

            <Button type="submit">Verificar código</Button>
          </C.Form>

          <p><button type="button" onClick={() => {
                setRequires2FA(false);
                setCodigo('');
                setCodigoTeste('');
                setError('');
              }}>Voltar
            </button></p>
        </C.Content>
      </C.Container>
    );
  }

// Aqui é renderizado o formulário de login, com campos para e-mail e senha, além de um botão para enviar os dados
  return (
    <C.Container>
      <Title>SISTEMA DE LOGIN</Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>
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

          <Button type="submit"> Login</Button>
        </C.Form>

        <C.LabelFirstAcess>
          Primeiro acesso?
          <C.Strong>
            <Link to="/first-access">{' '}Clique aqui</Link>
          </C.Strong>
        </C.LabelFirstAcess>
        <C.LabelSignup>
          Não tem uma conta?
          <C.Strong>
            <Link to="/register">{' '}Clique aqui</Link>
          </C.Strong>
        </C.LabelSignup>
      </C.Content>
    </C.Container>
  );
}

export default Login;