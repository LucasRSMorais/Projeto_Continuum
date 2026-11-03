import { useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { useNavigate } from 'react-router-dom';
import Input from '../../components/Input';
import * as C from './styles';
import { Title } from './styles';
import { FaShieldAlt, FaTrash, FaEye, FaEyeSlash } from 'react-icons/fa';
import { buildApiUrl } from '../../config/api';
import HeaderPaciente from '../../components/layout/HeaderPaciente';
import SidebarPaciente from '../../components/layout/SidebarPaciente';

const formatTelefone = (value) => {
  const digits = value.replace(/\D/g, '').slice(0, 11);
  if (digits.length <= 2) return digits;
  const prefix = `(${digits.slice(0, 2)}) `;
  const number = digits.slice(2);
  if (digits.length < 10) return `${prefix}${number}`;

  return `${prefix}${number.slice(0, -4)}-${number.slice(-4)}`;
};

function Configuracao() {
  const navigate = useNavigate();
  const { user } = useAuth();

  // Estados do formulário, mensagens de retorno e indicadores de carregamento.
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
const [message, setMessage] = useState('');
const [exibirSenha, setExibir] = useState(false);
const [exibirSenhaAtual, setExibirSenhaAtual] = useState(false);
const [exibirNovaSenha, setExibirNovaSenha] = useState(false);
const [exibirConfirmacaoSenha, setExibirConfirmacaoSenha] = useState(false);
const [senhaAtual, setSenhaAtual] = useState('');
const [novaSenha , setnovaSenha] = useState ('');
const [confirmarNovaSenha , setConfirmarNovaSenha] = useState('');




const [erroSenha , setErroSenha] = useState('');
const [messagemSenha , setMensagemSenha] = useState ('');
const [carregandoSenha , setCarregandoSenha] = useState(false);



const [erroRevogacao , seterroRevogacao] = useState('');
const [messagemRevogacao , setmessagemRevogacao] = useState('');

const [carregado , setcarregado] = useState(false);
const [carregandoRevogacao , setCarregandoRevogacao] = useState(false);
const [novoTelefone, setNovoTelefone] = useState('');
const [telefoneAtual, setTelefoneAtual] = useState(user?.telefone || '');
const [erroTelefone, setErroTelefone] = useState('');
const [mensagemTelefone, setMensagemTelefone] = useState('');
const [carregandoTelefone, setCarregandoTelefone] = useState(false);

// Solicita confirmação antes de enviar a revogação do consentimento à API.


const alterarSenha = async (event) => {
  event.preventDefault();
  setErroSenha('');
  setMensagemSenha('');

  if (!senhaAtual || !novaSenha || !confirmarNovaSenha) {
    setErroSenha('Preencha todos os campos.');
    return;
  }

  if (novaSenha.length < 8) {
    setErroSenha('A nova senha deve ter pelo menos 8 caracteres.');
    return;
  }

  if (novaSenha !== confirmarNovaSenha) {
    setErroSenha('A confirmação da senha não corresponde.');
    return;
  }

  setCarregandoSenha(true);
  try {
    const response = await fetch(buildApiUrl('alteraSenha.php'), {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        senha_atual: senhaAtual,
        nova_senha: novaSenha,
      }),
    });
    const dados = await response.json();

    if (response.ok && dados.success) {
      setMensagemSenha(dados.message || 'Senha alterada com sucesso.');
      setSenhaAtual('');
      setnovaSenha('');
      setConfirmarNovaSenha('');
    } else {
      setErroSenha(dados.message || 'Não foi possível alterar a senha.');
    }
  } catch (error) {
    console.error(error);
    setErroSenha('Ocorreu um erro ao tentar alterar a senha.');
  } finally {
    setCarregandoSenha(false);
  }
};

const alterarTelefone = async (event) => {
  event.preventDefault();
  setErroTelefone('');
  setMensagemTelefone('');

  const telefoneSemMascara = novoTelefone.replace(/\D/g, '');
  if (telefoneSemMascara.length !== 10 && telefoneSemMascara.length !== 11) {
    setErroTelefone('Digite um telefone válido com DDD.');
    return;
  }

  setCarregandoTelefone(true);
  try {
    const response = await fetch(buildApiUrl('atualizartelefone.php'), {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: user?.email,
        telefone: novoTelefone,
      }),
    });
    const dados = await response.json();

    if (response.ok && dados.success) {
      setTelefoneAtual(novoTelefone);
      setNovoTelefone('');
      setMensagemTelefone(dados.message || 'Telefone atualizado com sucesso.');
    } else {
      setErroTelefone(dados.message || 'Não foi possível atualizar o telefone.');
    }
  } catch (error) {
    console.error(error);
    setErroTelefone('Ocorreu um erro ao tentar atualizar o telefone.');
  } finally {
    setCarregandoTelefone(false);
  }
};










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
    <C.PageShell>
      <HeaderPaciente />
      <C.PageLayout>
        <SidebarPaciente />
        <C.Container>
          <Title>Configurações da Conta</Title>
          <C.Content>
            <C.Form onSubmit={deletarConta}>
              <label htmlFor="email">E-mail</label>
              <input id="email" type="email" value={user?.email || ''} readOnly />

              <label htmlFor="nome">Nome</label>
              <input id="nome" type="text" value={user?.nome || ''} readOnly />

              <label htmlFor="cpf">CPF</label>
              <input id="cpf" type="text" value={user?.cpf || ''} readOnly />

              <C.Section>
                <C.SectionTitle>Alterar telefone</C.SectionTitle>
                <label htmlFor="telefone-atual">Telefone atual</label>
                <Input
                  id="telefone-atual"
                  type="tel"
                  value={telefoneAtual || user?.telefone || ''}
                  readOnly
                />

                <label htmlFor="novo-telefone">Novo telefone</label>
                <Input
                  id="novo-telefone"
                  type="tel"
                  autoComplete="tel"
                  inputMode="numeric"
                  maxLength={15}
                  placeholder="(11) 99999-9999"
                  value={novoTelefone}
                  onChange={(event) => {
                    setNovoTelefone(formatTelefone(event.target.value));
                    setErroTelefone('');
                  }}
                />

                {erroTelefone && <C.Error role="alert">{erroTelefone}</C.Error>}
                {mensagemTelefone && <C.Success role="status">{mensagemTelefone}</C.Success>}
                <button type="button" onClick={alterarTelefone} disabled={carregandoTelefone}>
                  {carregandoTelefone ? 'Atualizando...' : 'Alterar telefone'}
                </button>
              </C.Section>





            
              <C.Section>
                <C.SectionTitle>Alterar senha</C.SectionTitle>
                <label htmlFor="senha-atual">Senha atual</label>
                <C.PasswordRow>
                  <Input
                    id="senha-atual"
                    type={exibirSenhaAtual ? 'text' : 'password'}
                    autoComplete="current-password"
                    placeholder="Digite sua senha atual"
                    value={senhaAtual}
                    onChange={(event) => setSenhaAtual(event.target.value)}
                  />
                  <button
                    type="button"
                    aria-label={exibirSenhaAtual ? 'Ocultar senha atual' : 'Mostrar senha atual'}
                    onClick={() => setExibirSenhaAtual(!exibirSenhaAtual)}
                  >
                    {exibirSenhaAtual ? <FaEyeSlash /> : <FaEye />}
                  </button>
                </C.PasswordRow>
                <label htmlFor="nova-senha">Nova senha</label>
                <C.PasswordRow>
                  <Input
                    id="nova-senha"
                    type={exibirNovaSenha ? 'text' : 'password'}
                    autoComplete="new-password"
                    placeholder="Digite a nova senha"
                    value={novaSenha}
                    onChange={(event) => setnovaSenha(event.target.value)}
                  />
                  <button
                    type="button"
                    aria-label={exibirNovaSenha ? 'Ocultar nova senha' : 'Mostrar nova senha'}
                    onClick={() => setExibirNovaSenha(!exibirNovaSenha)}
                  >
                    {exibirNovaSenha ? <FaEyeSlash /> : <FaEye />}
                  </button>
                </C.PasswordRow>
                <label htmlFor="confirmar-nova-senha">Confirmar nova senha</label>
                <C.PasswordRow>
                  <Input
                    id="confirmar-nova-senha"
                    type={exibirConfirmacaoSenha ? 'text' : 'password'}
                    autoComplete="new-password"
                    placeholder="Confirme a nova senha"
                    value={confirmarNovaSenha}
                    onChange={(event) => setConfirmarNovaSenha(event.target.value)}
                  />
                  <button
                    type="button"
                    aria-label={exibirConfirmacaoSenha ? 'Ocultar confirmação da senha' : 'Mostrar confirmação da senha'}
                    onClick={() => setExibirConfirmacaoSenha(!exibirConfirmacaoSenha)}
                  >
                    {exibirConfirmacaoSenha ? <FaEyeSlash /> : <FaEye />}
                  </button>
                </C.PasswordRow>
                {erroSenha && <C.Error role="alert">{erroSenha}</C.Error>}
                {messagemSenha && <C.Success role="status">{messagemSenha}</C.Success>}
                <button type="button" onClick={alterarSenha} disabled={carregandoSenha}>
                  {carregandoSenha ? 'Alterando...' : 'Alterar senha'}
                </button>
              </C.Section>

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
      </C.PageLayout>
    </C.PageShell>
  );
}

export default Configuracao;