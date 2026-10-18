import { FaClipboardList } from 'react-icons/fa';
import {
  Card,
  EmptyState,
  Patient,
  PatientList,
} from './styles';

const severityLabels = {
  alta: 'Alta',
  media: 'Média',
  baixa: 'Baixa',
};

function formatLabel(value) {
  return value
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function WaitingBySectorCard({ patients = [], loading = false, error = null }) {
  return (
    <Card>
      <header>
        <FaClipboardList aria-hidden="true" />
        <h2>Pacientes aguardando</h2>
      </header>
      {loading ? (
        <EmptyState>Carregando pacientes aguardando atendimento...</EmptyState>
      ) : error ? (
        <EmptyState role="alert">Não foi possível carregar as filas de atendimento.</EmptyState>
      ) : patients.length === 0 ? (
        <EmptyState>Não há pacientes aguardando atendimento neste setor.</EmptyState>
      ) : (
        <PatientList aria-label="Pacientes aguardando atendimento neste setor">
          {patients.map((patient) => (
            <Patient key={patient.fila_id}>
              <strong>{patient.paciente_nome}</strong>
              <span>
                Gravidade: {severityLabels[patient.gravidade] || patient.gravidade}
                {' · '}
                Origem: {formatLabel(patient.origem)}
                {' · '}
                Espera: {patient.tempo_espera_minutos} min
              </span>
            </Patient>
          ))}
        </PatientList>
      )}
    </Card>
  );
}

export default WaitingBySectorCard;
