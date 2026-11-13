import { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import { buildApiUrl } from '../../config/api';
import * as C from './styles';
import { FaShieldAlt, FaFileAlt, FaCheckCircle, FaTimes } from 'react-icons/fa';
import HeaderPaciente from '../../components/layout/Patient/HeaderPaciente';
import Footer from '../../components/layout/Footer';

const formatarDataUtc = (valor) => {
  if (!valor) return 'Sem registro';

  const normalizado = String(valor).includes('T')
    ? String(valor)
    : `${String(valor).replace(' ', 'T')}Z`;
  const comPrecisaoJavaScript = normalizado.replace(/(\.\d{3})\d+Z$/, '$1Z');
  const data = new Date(comPrecisaoJavaScript);
  return Number.isNaN(data.getTime())
    ? 'Indisponível'
    : `${new Intl.DateTimeFormat('pt-BR', {
      dateStyle: 'short',
      timeStyle: 'short',
      timeZone: 'UTC',
    }).format(data)} UTC`;
};

const textosStatus = {
  aceito: 'Aceito',
  revogado: 'Revogado',
  sem_registro: 'Sem aceite registrado',
  informativo: 'Documento informativo',
  pendente_documento: 'Aguardando termo final',
};

const orientacaoDocumento = {
  politica_privacidade: 'Aviso sobre tratamento de dados; sua consulta não é registrada como consentimento.',
  termo_uso_servico: 'Aceite necessário para ativar o acesso ao Portal do Paciente.',
  dados_sensiveis: 'O PDF atual contém campos de modelo para paciente, CPF e instituição; não está habilitado para aceite.',
  uso_imagem: 'O PDF atual contém campos de modelo e opções de finalidade não preenchidas; não está habilitado para aceite.',
  consentimento_procedimento: 'Documento-modelo para procedimento específico, com campos a preencher no atendimento.',
  recusa_tratamento: 'Documento-modelo para recusa específica, com campos a preencher no atendimento.',
};

function Consentimento() {
  const { user } = useAuth();
  const [documentos, setDocumentos] = useState([]);
  const [carregando, setCarregando] = useState(true);
  const [documentoEmAtualizacao, setDocumentoEmAtualizacao] = useState('');
  const [erro, setErro] = useState('');
  const [mensagem, setMensagem] = useState('');

  const buscarDocumentos = useCallback(async () => {
    const response = await fetch(buildApiUrl('patients/consentimentos_paciente.php'), {
      credentials: 'include',
      headers: { Accept: 'application/json' },
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || 'Não foi possível carregar os documentos.');
    }
    return data.documentos;
  }, []);

  useEffect(() => {
    let cancelado = false;
    buscarDocumentos()
      .then((dados) => {
        if (!cancelado) setDocumentos(dados);
      })
      .catch((requestError) => {
        if (cancelado) return;
        console.error('Erro ao carregar documentos e consentimentos:', requestError);
        setErro(requestError.message || 'Não foi possível carregar seus documentos.');
      })
      .finally(() => {
        if (!cancelado) setCarregando(false);
      });

    return () => {
      cancelado = true;
    };
  }, [buscarDocumentos]);

  const atualizarConsentimento = async (documento, acao) => {
    const textoAcao = acao === 'revogar' ? 'revogação' : 'autorização';
    const confirmado = window.confirm(
      `Confirma a ${textoAcao} para "${documento.titulo}", versão v.${documento.versao}? A decisão será registrada com data e hora UTC.`
    );
    if (!confirmado) return;

    setDocumentoEmAtualizacao(documento.chave);
    setErro('');
    setMensagem('');
    try {
      const response = await fetch(buildApiUrl('patients/consentimentos_paciente.php'), {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ documento: documento.chave, acao }),
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Não foi possível atualizar este consentimento.');
      }
      setMensagem(data.message);
      try {
        setDocumentos(await buscarDocumentos());
      } catch (refreshError) {
        console.error('A revogação foi registrada, mas a lista não pôde ser atualizada:', refreshError);
        setErro('A revogação foi registrada, mas não foi possível atualizar a lista. Recarregue a página.');
      }
    } catch (requestError) {
      console.error('Erro ao atualizar consentimento:', requestError);
      setErro(requestError.message || 'Não foi possível registrar esta decisão.');
    } finally {
      setDocumentoEmAtualizacao('');
    }
  };

  return (
    <C.PageShell>
      <HeaderPaciente />
      <C.PageLayout>
        <C.Container>
          <C.Title>Meus documentos e consentimentos</C.Title>
          <C.Content>
            <C.Heading>
              <C.HeadingIcon><FaShieldAlt /></C.HeadingIcon>
              <div>
                <h2>Documentos, versões e decisões</h2>
                <p>
                  Olá, {user?.nome || 'paciente'}. Consulte os documentos, versões e
                  decisões registradas na sua conta.
                </p>
              </div>
            </C.Heading>

            <C.TransparencyNotice role="note">
              <strong>Como interpretar os registros</strong>
              <p>
                Cada aceite ou revogação é associado à versão e ao hash do PDF apresentado,
                com data e hora UTC. A revogação não apaga o histórico nem registros clínicos.
                Ela registra sua decisão sobre o consentimento específico; a aplicação da
                revogação e a existência de outras bases legais devem ser avaliadas para cada uso.
                Os termos de dados sensíveis, imagem, procedimento e recusa ainda contêm campos
                de modelo e não podem ser aceitos aqui como autorizações finais.
              </p>
            </C.TransparencyNotice>

            {erro && <C.Error role="alert">{erro}</C.Error>}
            {mensagem && <C.Success role="status">{mensagem}</C.Success>}

            <C.Toolbar>
              <strong>Documentos disponíveis</strong>
              <C.Count>
                {carregando ? 'Carregando...' : `${documentos.length} documentos`}
              </C.Count>
            </C.Toolbar>

            <C.TableWrapper>
              <table>
                <thead>
                  <tr>
                    <th>Documento e finalidade</th>
                    <th>Versão</th>
                    <th>Status</th>
                    <th>Data do evento</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {carregando ? (
                    <tr><td colSpan="5">Carregando documentos...</td></tr>
                  ) : documentos.length === 0 ? (
                    <tr><td colSpan="5">Nenhum documento disponível.</td></tr>
                  ) : documentos.map((documento) => (
                    <tr key={documento.chave}>
                      <td data-label="Documento e finalidade">
                        <C.DocumentName>
                          <FaFileAlt />
                          <span>
                            <strong>{documento.titulo}</strong>
                            <small>{orientacaoDocumento[documento.chave]}</small>
                            <small className="document-hash">SHA-256: {documento.hash}</small>
                          </span>
                        </C.DocumentName>
                      </td>
                      <td data-label="Versão">v.{documento.versao}</td>
                      <td data-label="Status">
                        <C.Status $status={documento.status}>
                          {documento.status === 'aceito' && <FaCheckCircle />}
                          {documento.status === 'revogado' && <FaTimes />}
                          {textosStatus[documento.status]}
                        </C.Status>
                      </td>
                      <td data-label="Data do evento">{formatarDataUtc(documento.data_hora_utc)}</td>
                      <td data-label="Ações">
                        <C.RowActions>
                          <C.PolicyLink
                            href={documento.url}
                            target="_blank"
                            rel="noopener noreferrer"
                          >
                            Abrir PDF
                          </C.PolicyLink>
                          {documento.revogavel && documento.status === 'aceito' && (
                            <C.ActionButton
                              type="button"
                              disabled={documentoEmAtualizacao === documento.chave}
                              onClick={() => atualizarConsentimento(documento, 'revogar')}
                              aria-label={`Revogar consentimento: ${documento.titulo}`}
                            >
                              {documentoEmAtualizacao === documento.chave
                                ? 'Registrando...'
                                : 'Revogar'}
                            </C.ActionButton>
                          )}
                          {documento.revogavel
                            && documento.aceitavel
                            && documento.status !== 'aceito' && (
                            <C.ActionButton
                              type="button"
                              disabled={documentoEmAtualizacao === documento.chave}
                              onClick={() => atualizarConsentimento(documento, 'aceitar')}
                              aria-label={`Autorizar: ${documento.titulo}`}
                            >
                              {documentoEmAtualizacao === documento.chave
                                ? 'Registrando...'
                                : 'Autorizar'}
                            </C.ActionButton>
                          )}
                        </C.RowActions>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </C.TableWrapper>
          </C.Content>
        </C.Container>
      </C.PageLayout>
      <Footer />
    </C.PageShell>
  );
}

export default Consentimento;
