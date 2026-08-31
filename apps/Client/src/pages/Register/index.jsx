import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';

// Página de cadastro de novos usuários.
// Em ambiente de teste, ela registra um usuário com perfil padrão e redireciona para o login.
function Register() {
  const navigate = useNavigate();

  const [nome, setNome] = useState('');
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
    if (!nome.trim() || !normalizedEmail || !normalizedPassword) {
      setMessage('Preencha todos os campos.');
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
        'http://localhost:8000/api/register.php',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nome: nome.trim(),
            email: normalizedEmail,
            senha: normalizedPassword,
            // Para testes, usamos um perfil padrão.
            // Não permitimos que o usuário escolha "admin".
            perfil: 'medico',
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
        navigate('/');
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