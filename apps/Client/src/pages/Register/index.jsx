import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';

// Pagina de registro de usuário, onde novos usuários podem se cadastrar no sistema
// APENAS PARA TESTE, NÃO É UMA PÁGINA DE PRODUÇÃO
function Register() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  // Função que lida com o envio do formulário de registro
  // Formatação do email e senha, validação de campos, verificação de usuário existente e armazenamento no LocalStorage
  const handleSubmit = (event) => {
    event.preventDefault();

    const normalizedEmail = email.trim().toLowerCase();
    const normalizedPassword = password.trim();

    if (!normalizedEmail || !normalizedPassword) {
      setMessage('Preencha email e senha.');
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

    let usersStorage = [];

    try {
      const storedUsers = localStorage.getItem('users_db');
      usersStorage = storedUsers ? JSON.parse(storedUsers) : [];
    } catch {
      usersStorage = [];
    }

    const userExists = usersStorage.some(
      (user) => user.email?.toLowerCase() === normalizedEmail
    );

    if (userExists) {
      setMessage('Usuário já cadastrado!');
      return;
    }

    usersStorage.push({
      email: normalizedEmail,
      password: normalizedPassword,
    });

    localStorage.setItem('users_db', JSON.stringify(usersStorage));
    setMessage('Cadastro realizado com sucesso!');

    setTimeout(() => {
      navigate('/');
    }, 500);
  };

  return (
    <C.Container>
      <C.Title>SISTEMA DE CADASTRO</C.Title>
      <C.Content>
        <C.Form onSubmit={handleSubmit}>
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
          <Button Type="submit" Text="Cadastrar" onClick={handleSubmit} />
        </C.Form>
        <p>
          Já tem conta? <Link to="/">Entrar</Link>
        </p>
      </C.Content>
    </C.Container>
  );
}

export default Register;