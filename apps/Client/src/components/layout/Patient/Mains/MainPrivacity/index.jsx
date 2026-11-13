import { useState } from 'react';
import {
  Typography, Accordion, AccordionSummary, AccordionDetails,
  Button, Divider, Dialog, DialogTitle, DialogContent,
  DialogContentText, DialogActions, TextField, Alert
} from '@mui/material';
import { FaChevronDown, FaDownload, FaTrashAlt } from 'react-icons/fa';
import { Link as RouterLink } from 'react-router-dom';
import { CardContainer, Title, SubTitle } from './styles';
import { buildApiUrl } from '../../../../../config/api';

function MainPrivacity() {
  const [modalExclusaoOpen, setModalExclusaoOpen] = useState(false);
  const [senhaConfirmacao, setSenhaConfirmacao] = useState('');
  const [exportandoDados, setExportandoDados] = useState(false);
  const [erroExportacao, setErroExportacao] = useState('');

  const handleExportarDados = async () => {
    setExportandoDados(true);
    setErroExportacao('');

    try {
      const response = await fetch(buildApiUrl('patients/exportar.php'), {
        method: 'GET',
        credentials: 'include',
        headers: { Accept: 'application/json' },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Não foi possível exportar seus dados.');
      }

      const exportFile = await response.blob();
      const downloadUrl = URL.createObjectURL(exportFile);
      const downloadLink = document.createElement('a');
      downloadLink.href = downloadUrl;
      downloadLink.download = 'meus-dados-continuum.json';
      document.body.appendChild(downloadLink);
      downloadLink.click();
      downloadLink.remove();
      window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
    } catch (error) {
      console.error('Erro ao exportar dados do paciente:', error);
      setErroExportacao(error.message || 'Não foi possível exportar seus dados. Tente novamente.');
    } finally {
      setExportandoDados(false);
    }
  };

  const handleExcluirConta = () => {
    alert('Conta excluída logicamente. Dados clínicos retidos por obrigação legal.');
    setModalExclusaoOpen(false);
  };

  return (
    <CardContainer>
      <Title>Central de Privacidade</Title>
      <SubTitle>
        Gerencie como o Projeto Continuum trata suas informações de saúde, em conformidade com a Lei Geral de Proteção de Dados (LGPD).
      </SubTitle>

      <Divider sx={{ my: 4 }} />

      <Typography variant="h6" gutterBottom fontWeight="bold">
        1. Como utilizamos seus dados
      </Typography>

      <Accordion>
        <AccordionSummary expandIcon={<FaChevronDown />}>
          <Typography fontWeight="bold">Dados Cadastrais e de Contato</Typography>
        </AccordionSummary>
        <AccordionDetails>
          <Typography variant="body2">
            <strong>O que coletamos:</strong> Nome, E-mail, CPF, Endereço e Telefone.<br />
            <strong>Finalidade:</strong> Identificação, contato emergencial e prontuário demográfico.<br />
            <strong>Retenção:</strong> Permanente para CPF/Endereço (Guarda de Prontuário Médico). E-mail e telefone são retidos até a exclusão da conta.
          </Typography>
        </AccordionDetails>
      </Accordion>

      <Accordion>
        <AccordionSummary expandIcon={<FaChevronDown />}>
          <Typography fontWeight="bold">Informações Clínicas Sensíveis</Typography>
        </AccordionSummary>
        <AccordionDetails>
          <Typography variant="body2">
            <strong>O que coletamos:</strong> Sinais Vitais, Alergias, Medicações e Eventos Adversos.<br />
            <strong>Finalidade:</strong> Continuidade do cuidado médico durante a passagem de plantões.<br />
            <strong>Retenção:</strong> Permanente, conforme exigência do Conselho Federal de Medicina.
          </Typography>
        </AccordionDetails>
      </Accordion>

      <Divider sx={{ my: 4 }} />

      <Typography variant="h6" gutterBottom fontWeight="bold">
        2. Meus Consentimentos
      </Typography>
      <Typography variant="body2" color="text.secondary" gutterBottom>
        Consulte os documentos, versões e decisões registradas. As autorizações opcionais
        podem ser revogadas na tela de consentimentos.
      </Typography>
      <Button component={RouterLink} to="/consentimentos" variant="outlined" sx={{ mt: 2 }}>
        Ver documentos e gerenciar consentimentos
      </Button>

      <Divider sx={{ my: 4 }} />

      <Typography variant="h6" gutterBottom fontWeight="bold">
        3. Portabilidade de Dados
      </Typography>
      <Typography variant="body2" color="text.secondary" gutterBottom>
        Faça o download de todas as suas informações armazenadas em nosso sistema em um formato estruturado.
      </Typography>
      <Button
        variant="outlined"
        startIcon={<FaDownload />}
        onClick={handleExportarDados}
        disabled={exportandoDados}
      >
        {exportandoDados ? 'Preparando exportação...' : 'Exportar Meus Dados (JSON)'}
      </Button>
      {erroExportacao && (
        <Alert severity="error" sx={{ mt: 2 }} role="alert">
          {erroExportacao}
        </Alert>
      )}

      <Divider sx={{ my: 4 }} />

      <Typography variant="h6" gutterBottom fontWeight="bold" color="error">
        4. Exclusão de Conta
      </Typography>
      <Typography variant="body2" color="text.secondary" gutterBottom>
        Encerre seu acesso ao sistema. O bloqueio é imediato.
      </Typography>
      <Button
        variant="contained"
        color="error"
        startIcon={<FaTrashAlt />}
        onClick={() => setModalExclusaoOpen(true)}
      >
        Solicitar Exclusão
      </Button>

      <Dialog open={modalExclusaoOpen} onClose={() => setModalExclusaoOpen(false)}>
        <DialogTitle fontWeight="bold" color="error">Confirmar Exclusão da Conta</DialogTitle>
        <DialogContent>
          <Alert severity="warning" sx={{ mb: 2 }}>
            <strong>Aviso de Retenção Legal:</strong> A sua conta será bloqueada e seus dados de contato serão excluídos. No entanto, por exigência do Ministério da Saúde e do CFM (Art. 16, I da LGPD), <strong>seus dados clínicos e histórico médico serão retidos</strong> nos registros do hospital de forma segura e criptografada.
          </Alert>
          <DialogContentText gutterBottom>
            Para confirmar a exclusão e a revogação permanente dos seus acessos, digite sua senha abaixo:
          </DialogContentText>
          <TextField
            autoFocus
            margin="dense"
            label="Senha atual"
            type="password"
            fullWidth
            variant="outlined"
            value={senhaConfirmacao}
            onChange={(event) => setSenhaConfirmacao(event.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setModalExclusaoOpen(false)} color="inherit">
            Cancelar
          </Button>
          <Button
            onClick={handleExcluirConta}
            color="error"
            variant="contained"
            disabled={!senhaConfirmacao}
          >
            Excluir Minha Conta
          </Button>
        </DialogActions>
      </Dialog>
    </CardContainer>
  );
}

export default MainPrivacity;