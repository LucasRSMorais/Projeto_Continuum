import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';
import Input from '../../components/Input';
import { buildApiUrl } from '../../config/api';
import * as C from './styles';
import { Title } from './styles';

// Página do inicio recuperar a senha do sistema.
// Aqui o usuário informa e-mail 
// Precisa o email para indetificar o codigo de acesso.
function Login() {
  const navigate = useNavigate();
  const {checkSession} = useAuth();

  // Estado dos campos do formulário e das mensagens de erro.
  const [email, setEmail] = useState('');
 
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

    if (!normalizedEmail ) {
      setError('Preencha a email .');
      return;
    }

    try {
      const response = await fetch(
        buildApiUrl('email.php'),
        {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: normalizedEmail,
            
          }),
        }
      );

      const data = await response.json();
      if (!response.ok) {
        setError(data.message || 'Email inválidos.');
        return;
      }

      // email correto, mas ainda precisa do 2FA
      if (data.requires_2fa) {
        setCodigoTeste(data.codigo_teste);
        setRequires2FA(true);
        return;
      }

      navigate('/pass');

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
        buildApiUrl('verificar.php'),
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

      navigate('/pass');

    } catch (error) {
      console.error(error);
      setError('Não foi possível conectar ao servidor.');
    }
  };

  // Quando o backend exige 2FA, no gmail terá o codigo de acesso.
  if (requires2FA) {
    return (
      <C.Container>
        <Title>VERIFICAÇÃO DE SEGURANÇA</Title>
        <C.Content>
          <C.Form onSubmit={handleVerify2FA}>
            <p>
              Confira o codigo enviado pelo seu email .
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

          
        </C.Content>
      </C.Container>
    );
  }

// Aqui é renderizado inicio do formulário de recuperar a senha, com campo para e-mail. 
  return (
    <C.Container>
      <Title>Recuperação de Senha</Title>

      <C.Content>
        <C.Form onSubmit={handleSubmit}>
          <Input type="email" placeholder="Email" value={email}
            onChange={(event) => {
              setEmail(event.target.value);
              setError('');
            }}
          />
         
          {error && (<C.labelError>{error}</C.labelError>)}

          <Button type="submit"> Recuperar</Button>
        </C.Form>
      </C.Content>
    </C.Container>
  );
}

export default Login;