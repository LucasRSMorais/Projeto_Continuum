import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import * as C from './styles';
import { useAuth } from '../../services/utils/auth';
import { Title } from './styles';

// Esta página é responsável por renderizar o formulário de login e lidar com a autenticação do usuário
// Aqui o usuário insere seu e-mail e senha, que são verificados com base nos dados armazenados no LocalStorage
function Login() {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = (event) => {
    event.preventDefault();
    const result = login(email, password);

    if (result) {
      setError(result);
      return;
    }

    navigate('/home');
  };

  // Aqui é renderizado o formulário de login, com campos para e-mail e senha, além de um botão para enviar os dados
  return (
    <C.Container>
      <Title>Login</Title>
      <C.Content>
        <form onSubmit={handleSubmit}>
          <Input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(event) => [setEmail(event.target.value), setError('')]}
          />
          <Input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(event) => [setPassword(event.target.value), setError('')]}
          />
          {error && <C.labelError>{error}</C.labelError>}
          <Button type="submit">Login</Button>
        </form>
        {/* Link para o formulário de primeiro acesso */}
        <C.LabelFirstAcess>
          Primeiro acesso ? 
            <C.Strong>
              <Link to="/first-access">&mbsp;Clique aqui</Link>
            </C.Strong>
        </C.LabelFirstAcess>
      </C.Content>
    </C.Container>
  );
}

export default Login;