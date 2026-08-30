import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import Input from '../../components/Input';
import Button from '../../components/Button';
import useAuth from '../../services/utils/auth';


function Register() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  const handleSubmit = (event) => {
    event.preventDefault();

    const usersStorage = JSON.parse(localStorage.getItem('users_db') ?? '[]');
    const userExists = usersStorage.some((user) => user.email === email);

    if (userExists) {
      setMessage('Usuário já cadastrado!');
      return;
    }

    const newUser = { email, password };
    usersStorage.push(newUser);
    localStorage.setItem('users_db', JSON.stringify(usersStorage));
    setMessage('Cadastro realizado com sucesso!');
    navigate('/');
  };

  return (
    <div>
      <h1>Cadastro</h1>
      <form onSubmit={handleSubmit}>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
        />
        <input
          type="password"
          placeholder="Senha"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
        />
        {message && <p>{message}</p>}
        <button type="submit">Cadastrar</button>
      </form>
      <p>
        Já tem conta? <Link to="/">Entrar</Link>
      </p>
    </div>
  );
}

export default Register;
