import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { useNavigate } from 'react-router-dom';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';
import { FaShieldAlt, FaTrash, FaEye, FaEyeSlash } from 'react-icons/fa';
import { buildApiUrl } from '../../config/api';

function Configuracao() {
  const navigate = useNavigate();
  const { user } = useAuth();

  // Estados do formulário, mensagens de retorno e indicadores de carregamento.
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
const [message, setMessage] = useState('');
const [exibirSenha, setExibir] = useState(false);

const [erroRevogacao , seterroRevogacao] = useState('');
const [messagemRevogacao , setmessagemRevogacao] = useState('');

const [carregado , setcarregado] = useState(false);
const [carregandoRevogacao , setCarregandoRevogacao] = useState(false);

// Solicita confirmação antes de enviar a revogação do consentimento à API.
const revogarConsetimento = async (event) => {
  event.preventDefault();
  event.stopPropagation();
  seterroRevogacao('');
  setmessagemRevogacao('');

  const confirmar = window.confirm('Tem certeza que deseja revogar o consentimento? Esta ação não pode ser desfeita.');
  if (!confirmar) return;

  setCarregandoRevogacao(true);
  try {
    const response = await fetch(buildApiUrl('revoga_conta.php'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: user?.email }),
    });
    const dados = await response.json();
    if (dados.success) {
      setmessagemRevogacao('Consentimento revogado com sucesso.');
    } else {
      seterroRevogacao(dados.message || 'Não foi possível revogar o consentimento.');
    }
  } catch (error) {
    console.error(error);
    seterroRevogacao('Ocorreu um erro ao tentar revogar o consentimento.');
  } finally {
    setCarregandoRevogacao(false);
  }
};

// Valida a senha e confirma a ação antes de solicitar a exclusão da conta.
const deletarConta = async (event) => {
  event.preventDefault();
  setMessage('');
  setError('');

  if (!user?.email || !password) {
    setError('Digite sua senha para excluir a conta.');
    return;
  }

const confirmar = window.confirm('Tem certeza que deseja deletar sua conta? Esta ação não pode ser desfeita.');
if (!confirmar) return;

setcarregado(true);
try {
  const response = await fetch(buildApiUrl('confirmar_deleta.php'), {
    method: 'DELETE',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: user.email,
      senha: password,
    }),
  });
  const data = await response.json();

  if (response.ok && data.success) {
    setMessage('Conta deletada com sucesso.');
    setPassword('');
    setTimeout(() => navigate('/login_pacientes'), 2000);
  } else {
    setError(data.message || 'Não foi possível deletar a conta.');
  }
} catch (error) {
  console.error(error);
  setError('Erro ao deletar a conta.');
} finally {
  setcarregado(false);
}
};






  return (
    <C.Container>
      <Title>Configuração</Title>
      <C.Content>
        <C.Form onSubmit={deletarConta}>
          <label htmlFor="email">E-mail</label>
          <input id="email" type="email" value={user?.email || ''} readOnly />

          <C.Section>
            <C.SectionTitle><FaShieldAlt /> Consentimento de LGPD</C.SectionTitle>
            <button type="button" onClick={revogarConsetimento} disabled={carregandoRevogacao}>
              {carregandoRevogacao ? 'Revogando...' : 'Revogar consentimento'}
            </button>
            {messagemRevogacao && <C.Success role="status">{messagemRevogacao}</C.Success>}
            {erroRevogacao && <C.Error role="alert">{erroRevogacao}</C.Error>}
          </C.Section>

          <C.Section>
            <C.SectionTitle><FaTrash /> Deletar conta</C.SectionTitle>
            <p>Excluir sua conta permanentemente. Esta ação não pode ser desfeita.</p>
            <C.PasswordRow>
              <Input
                type={exibirSenha ? 'text' : 'password'}
                placeholder="Digite sua senha"
                value={password}
                onChange={(event) => {
                  setPassword(event.target.value);
                  setError('');
                }}
              />
              {/* Permite conferir a senha digitada sem alterar seu valor. */}
              <button
                type="button"
                aria-label={exibirSenha ? 'Ocultar senha' : 'Mostrar senha'}
                onClick={() => setExibir(!exibirSenha)}
              >
                {exibirSenha ? <FaEyeSlash /> : <FaEye />}
              </button>
            </C.PasswordRow>
            {error && <C.Error role="alert">{error}</C.Error>}
            <button type="submit" disabled={carregado}>
              {carregado ? 'Excluindo...' : 'Excluir conta'}
            </button>
          </C.Section>
        </C.Form>
        {message && <C.Confirmacao role="status">{message}</C.Confirmacao>}
      </C.Content>
    </C.Container>
  );
}

export default Configuracao;