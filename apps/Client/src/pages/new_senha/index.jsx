import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import { buildApiUrl } from '../../config/api';
import * as C from './styles';
import { FaEye, FaEyeSlash } from 'react-icons/fa';

function nova_() {
  const navigate = useNavigate();
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [exibirSenha, setExibir] = useState(false);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');

    if (password.length < 6) {
      setError('A senha deve ter pelo menos 6 caracteres.');
      return;
    }

    try {
      const response = await fetch(
        buildApiUrl('senha.php'),
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            nova_senha: password,
          
          }),
        }
      );

      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Não foi possível atualizar a senha.');
        return;
      }

      navigate('/register');

    } catch (error) {
      console.error(error);
      setError('Não foi possível conectar ao servidor.');
    }
  };

  return (
    <C.Container>
      <C.Title>Redefinir sua senha</C.Title>

      <C.Content>
        <p>Crie uma senha segura para proteger sua conta.</p>
        <C.Form onSubmit={handleSubmit}>
          <C.PasswordRow>
            <Input
              type={exibirSenha ? 'text' : 'password'}
              placeholder="Nova senha"
              value={password}
              onChange={(event) => {
                setPassword(event.target.value);
                setError('');
              }}
            />
            <button
              type="button"
              aria-label={exibirSenha ? 'Ocultar senha' : 'Mostrar senha'}
              onClick={() => setExibir(!exibirSenha)}
            >
              {exibirSenha ? <FaEyeSlash /> : <FaEye />}
            </button>
          </C.PasswordRow>
          {error && <C.labelError>{error}</C.labelError>}

          <Button type="submit">Salvar nova senha</Button>
        </C.Form>
      </C.Content>
    </C.Container>
  );
}

export default nova_;