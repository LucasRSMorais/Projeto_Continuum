import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import * as C from './styles';
import { buildApiUrl } from '../../../../config/api';

const documentos = {
  privacidade: {
    titulo: 'Política de Privacidade',
    url: '/politica_privacidade.pdf',
  },
  servico: {
    titulo: 'Termo de Uso e Serviço - Portal do Paciente',
    url: '/document/terms/patient/Termos%20de%20Uso%20e%20Servi%C3%A7o%20-%20Portal%20do%20Paciente.pdf',
  },
  dadosSensiveis: {
    titulo: 'Termo de Consentimento para Tratamento de Dados Sensíveis',
    url: '/document/terms/patient/Termo_Consentimento_Dados_Sensiveis.pdf',
  },
  imagem: {
    titulo: 'Termo de Autorização de Uso de Imagem',
    url: '/document/terms/patient/Termo_Autorizacao_Imagem.pdf',
  },
};

function PrimeiroAcessoPaciente() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [codigo, setCodigo] = useState('');
  const [senha, setSenha] = useState('');
  const [confirmacaoSenha, setConfirmacaoSenha] = useState('');
  const [codigoSolicitado, setCodigoSolicitado] = useState(false);
  const [aceitaTermo, setAceitaTermo] = useState(false);
  const [mensagem, setMensagem] = useState('');
  const [erro, setErro] = useState('');
  const [carregando, setCarregando] = useState(false);

  const solicitarCodigo = async (event) => {
    event.preventDefault();
    setErro('');
    setMensagem('');
    setCarregando(true);

    try {
      const response = await fetch(buildApiUrl('patients/primeiro_acesso_paciente.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          acao: 'solicitar_codigo',
          email: email.trim().toLowerCase(),
        }),
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Não foi possível solicitar o código.');
      }

      setCodigoSolicitado(true);
      setMensagem(data.message);
    } catch (requestError) {
      console.error('Erro ao solicitar código de primeiro acesso:', requestError);
      setErro(requestError.message || 'Não foi possível solicitar o código. Tente novamente.');
    } finally {
      setCarregando(false);
    }
  };

  const ativarAcesso = async (event) => {
    event.preventDefault();
    setErro('');
    setMensagem('');

    if (senha !== confirmacaoSenha) {
      setErro('As senhas informadas não coincidem.');
      return;
    }
    if (!aceitaTermo) {
      setErro('Leia e aceite o Termo de Uso e Serviço para ativar o acesso.');
      return;
    }

    setCarregando(true);
    try {
      const response = await fetch(buildApiUrl('patients/primeiro_acesso_paciente.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          acao: 'ativar',
          email: email.trim().toLowerCase(),
          codigo: codigo.trim(),
          senha,
          aceita_termo_uso_servico: aceitaTermo,
        }),
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Não foi possível ativar o acesso.');
      }

      setMensagem(data.message);
      window.setTimeout(() => navigate('/login_pacientes'), 1200);
    } catch (requestError) {
      console.error('Erro ao ativar primeiro acesso do paciente:', requestError);
      setErro(requestError.message || 'Não foi possível ativar o acesso. Tente novamente.');
    } finally {
      setCarregando(false);
    }
  };

  return (
    <C.Container>
      <C.Title>PRIMEIRO ACESSO DO PACIENTE</C.Title>
      <C.Content>
        <C.Form onSubmit={codigoSolicitado ? ativarAcesso : solicitarCodigo}>
          <p>
            Esta etapa ativa uma conta já criada pela instituição. Ela não cria
            um novo cadastro de paciente.
          </p>

          <label htmlFor="email-primeiro-acesso">E-mail informado pela instituição</label>
          <input
            id="email-primeiro-acesso"
            type="email"
            autoComplete="email"
            required
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            readOnly={codigoSolicitado}
          />

          {!codigoSolicitado ? (
            <button type="submit" disabled={carregando}>
              {carregando ? 'Solicitando...' : 'Enviar código de ativação'}
            </button>
          ) : (
            <>
              <p>
                Se houver uma conta elegível, o código será enviado ao e-mail
                cadastrado. Ele expira em 10 minutos.
              </p>
              <label htmlFor="codigo-primeiro-acesso">Código de seis dígitos</label>
              <input
                id="codigo-primeiro-acesso"
                type="text"
                inputMode="numeric"
                autoComplete="one-time-code"
                pattern="[0-9]{6}"
                maxLength={6}
                required
                value={codigo}
                onChange={(event) => setCodigo(event.target.value.replace(/\D/g, ''))}
              />

              <label htmlFor="senha-primeiro-acesso">Crie uma senha (mínimo 12 caracteres)</label>
              <input
                id="senha-primeiro-acesso"
                type="password"
                autoComplete="new-password"
                minLength={12}
                required
                value={senha}
                onChange={(event) => setSenha(event.target.value)}
              />
              <label htmlFor="confirmacao-senha">Confirme a senha</label>
              <input
                id="confirmacao-senha"
                type="password"
                autoComplete="new-password"
                minLength={12}
                required
                value={confirmacaoSenha}
                onChange={(event) => setConfirmacaoSenha(event.target.value)}
              />

              <hr />
              <h3>Documentos e autorizações</h3>
              <p>
                Consulte o conteúdo integral do termo antes de aceitar. O registro
                eletrônico guarda a versão do PDF (v.1.0), seu hash e o momento do aceite.
              </p>
              <label>
                <input
                  type="checkbox"
                  checked={aceitaTermo}
                  onChange={(event) => setAceitaTermo(event.target.checked)}
                  required
                />
                Li e aceito o{' '}
                <a href={documentos.servico.url} target="_blank" rel="noopener noreferrer">
                  {documentos.servico.titulo} (v.1.0)
                </a>
                . O aceite é necessário para ativar o acesso ao portal.
              </label>
              <p>
                O PDF do termo ainda contém os campos de modelo [Local] e [Data].
                O sistema registra a data e hora UTC do aceite; o campo de local permanece
                sem preenchimento e deve ser revisto antes da publicação definitiva.
              </p>

              <p>
                As autorizações opcionais de dados sensíveis e de imagem são separadas
                do termo de serviço e não condicionam o acesso. Os documentos atuais
                ainda contêm campos de modelo (como instituição, paciente ou finalidade);
                por isso, não são apresentados para aceite até serem substituídos por
                versões finais.
              </p>
              <p>
                Consulte os modelos atuais:{' '}
                <a href={documentos.dadosSensiveis.url} target="_blank" rel="noopener noreferrer">
                  dados sensíveis
                </a>
                {' '}e{' '}
                <a href={documentos.imagem.url} target="_blank" rel="noopener noreferrer">
                  imagem e dados de prontuário
                </a>
                .
              </p>

              <p>
                A{' '}
                <a href={documentos.privacidade.url} target="_blank" rel="noopener noreferrer">
                  {documentos.privacidade.titulo}
                </a>
                {' '}explica como os dados são tratados; sua leitura não é registrada como
                consentimento.
              </p>

              <button type="submit" disabled={carregando}>
                {carregando ? 'Ativando acesso...' : 'Ativar acesso'}
              </button>
              <button
                type="button"
                className="secondary"
                disabled={carregando}
                onClick={() => {
                  setCodigoSolicitado(false);
                  setCodigo('');
                  setMensagem('');
                  setErro('');
                }}
              >
                Solicitar outro código
              </button>
            </>
          )}

          {erro && <C.labelError role="alert">{erro}</C.labelError>}
          {mensagem && <p role="status">{mensagem}</p>}
        </C.Form>

        <p>
          Já ativou sua conta? <C.Strong><Link to="/login_pacientes">Entrar</Link></C.Strong>
        </p>
      </C.Content>
    </C.Container>
  );
}

export default PrimeiroAcessoPaciente;
