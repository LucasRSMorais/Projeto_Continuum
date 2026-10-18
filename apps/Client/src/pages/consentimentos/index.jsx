import { useEffect, useState } from 'react';
import { useAuth } from '../../services/utils/auth';
import * as C from './styles';
import { FaShieldAlt, FaFileAlt, FaCheckCircle, FaEye, FaTimes } from 'react-icons/fa';
import HeaderPaciente from '../../components/layout/HeaderPaciente';
import SidebarPaciente from '../../components/layout/SidebarPaciente';

const formatarDataCadastro = (valor) => {
  if (!valor) return 'Indisponível';

  const data = String(valor).match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (data) return `${data[3]}/${data[2]}/${data[1]}`;

  const dataConvertida = new Date(valor);
  return Number.isNaN(dataConvertida.getTime())
    ? 'Indisponível'
    : dataConvertida.toLocaleDateString('pt-BR');
};

const dataLocalDeHoje = () => {
  const hoje = new Date();
  const ano = hoje.getFullYear();
  const mes = String(hoje.getMonth() + 1).padStart(2, '0');
  const dia = String(hoje.getDate()).padStart(2, '0');
  return `${dia}/${mes}/${ano}`;
};

function Consentimento() {
  const { user } = useAuth();
  const [detalhe, setDetalhe] = useState(null);
  const [dataCadastro, setDataCadastro] = useState('');
  const [erroData, setErroData] = useState('');

  useEffect(() => {
    const dataVindaDoServidor = user?.created_at || user?.data_cadastro;
    if (dataVindaDoServidor) {
      setDataCadastro(formatarDataCadastro(dataVindaDoServidor));
      return;
    }

    const identificador = user?.id || user?.email;
    if (!identificador) return;

    const chaveData = `continuum:consentimento:data-cadastro:${identificador}`;
    try {
      const dataSalva = window.localStorage.getItem(chaveData);
      if (dataSalva) {
        setDataCadastro(dataSalva);
        return;
      }

      const dataHoje = dataLocalDeHoje();
      window.localStorage.setItem(chaveData, dataHoje);
      setDataCadastro(dataHoje);
    } catch (error) {
      console.error('Não foi possível salvar a data de cadastro do consentimento:', error);
      setErroData('Não foi possível salvar a data deste consentimento neste dispositivo.');
    }
  }, [user?.created_at, user?.data_cadastro, user?.email, user?.id]);

  const consentimentos = [
    {
      data: '25/10/2026',
      tipo: 'Consetimentos e Autorizações Clinicas.',
      finalidade: '(Cirurgia, Anestesia, Exames, etec) .',
      versao: '1.0',
      status: 'Registrado',
    },
    {
      data: dataCadastro,
      tipo: 'Dados e imagem',
      finalidade: '(Compartilhamento de dados , Uso de imagem).',
      versao: '1.0',
      status: user?.aceite_termo_uso === 'sim'
        ? 'Registrado'
        : user?.aceite_termo_uso === 'nao'
          ? 'Não registrado'
          : 'Indisponível',
    },
    {
      data: '25/10/2026',
      tipo: 'Recusas e Responsabilidades',
      finalidade: '(Recusa de tratamento , Termo de Internação)',
      versao: '1.0',
      status: 'Registrado',
    },
    {
      data: '25/10/2026',
      tipo: 'Altas',
      finalidade: 'Altas hospitalar , Alta a pedido.',
      versao: '1.0',
      status: 'Registrado',
    },
  ];

  return (
    <C.PageShell>
      <HeaderPaciente />
      <C.PageLayout>
        <SidebarPaciente />
        <C.Container>
          <C.Title>Meus consentimentos</C.Title>
          <C.Content>
            <C.Heading>
              <C.HeadingIcon><FaShieldAlt /></C.HeadingIcon>
              <div>
                <h2>Consentimentos de privacidade</h2>
                <p>
                  Olá, {user?.nome || 'paciente'}. Consulte os termos relacionados aos seus dados.
                </p>
              </div>
            </C.Heading>

            <C.Toolbar>
              <strong>Documentos registrados</strong>
              <C.Count>{consentimentos.length} {consentimentos.length === 1 ? 'documento' : 'documentos'}</C.Count>
            </C.Toolbar>
            {erroData && <C.Error role="alert">{erroData}</C.Error>}

            <C.TableWrapper>
              <table>
                <thead>
                  <tr>
                    <th>Data</th>
                    <th>Consentimento</th>
                    <th>Versão</th>
                    <th>Status</th>
                    <th><span className="sr-only">Ação</span></th>
                  </tr>
                </thead>
                <tbody>
                  {consentimentos.map((item) => (
                    <tr key={item.tipo}>
                      <td data-label="Data">{item.data}</td>
                      <td data-label="Consentimento">
                        <C.DocumentName><FaFileAlt /> {item.tipo}</C.DocumentName>
                      </td>
                      <td data-label="Versão">{item.versao}</td>
                      <td data-label="Status">
                        <C.Status $status={item.status}>
                          {item.status === 'Registrado' && <FaCheckCircle />}
                          {item.status}
                        </C.Status>
                      </td>
                      <td data-label="Ação">
                        <C.ActionButton
                          type="button"
                          onClick={() => setDetalhe(item)}
                          aria-label={`Ver detalhes: ${item.tipo}`}
                        >
                          <FaEye /> Detalhes
                        </C.ActionButton>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </C.TableWrapper>

            {detalhe && (
              <C.Details role="region" aria-label={`Detalhes de ${detalhe.tipo}`}>
                <div>
                  <h3>{detalhe.tipo}</h3>
                  <C.CloseButton
                    type="button"
                    onClick={() => setDetalhe(null)}
                    aria-label="Fechar detalhes"
                  >
                    <FaTimes />
                  </C.CloseButton>
                </div>
                <dl>
                  <dt>Paciente</dt>
                  <dd>{user?.nome || 'Paciente'}</dd>
                  <dt>Data do registro</dt>
                  <dd>{detalhe.data}</dd>
                  <dt>Versão</dt>
                  <dd>{detalhe.versao}</dd>
                  <dt>Finalidade</dt>
                  <dd>{detalhe.finalidade}</dd>
                </dl>
              </C.Details>
            )}
          </C.Content>
        </C.Container>
      </C.PageLayout>
    </C.PageShell>
  );
}

export default Consentimento;
