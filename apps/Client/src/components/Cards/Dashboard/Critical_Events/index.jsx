import { FaTriangleExclamation } from 'react-icons/fa6';
import {
  Card,
  CardHeader,
  EmptyState,
  EventRow,
  EventTable,
  TableHeader,
} from './styles';

const investigationLabels = {
  pendente: 'Pendente',
  em_investigacao: 'Em investigação',
  concluido: 'Concluído',
};

const eventLabels = {
  deterioracao_clinica: 'Deterioração Clínica',
  lesao_pressao: 'Lesão por Pressão',
  movimentacao_leito: 'Movimentação de Leito',
  identificacao_incorreta: 'Identificação Incorreta',
  atraso_atendimento: 'Atraso no Atendimento',
  atraso_tratamento: 'Atraso no Tratamento',
};

function formatEventLabel(eventType) {
  if (eventLabels[eventType]) {
    return eventLabels[eventType];
  }
  return eventType
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (letter) => letter.toLocaleUpperCase('pt-BR'));
}

function CriticalEventsCard({ events = [], loading = false, error = null }) {
  return (
    <Card>
      <CardHeader>
        <FaTriangleExclamation aria-hidden="true" />
        <h2>Eventos Adversos Críticos (Grau Alto)</h2>
      </CardHeader>
      {loading ? (
        <EmptyState>Carregando eventos críticos...</EmptyState>
      ) : error ? (
        <EmptyState role="alert">Não foi possível carregar os eventos críticos.</EmptyState>
      ) : events.length === 0 ? (
        <EmptyState>Nenhum evento adverso crítico encontrado neste setor.</EmptyState>
      ) : (
        <EventTable>
          <thead>
            <tr>
              <TableHeader>Data/Hora</TableHeader>
              <TableHeader>Tipo</TableHeader>
              <TableHeader>Paciente</TableHeader>
              <TableHeader>Leito</TableHeader>
              <TableHeader>Status</TableHeader>
            </tr>
          </thead>
          <tbody>
            {events.map((event) => (
              <EventRow key={event.event_id}>
                <td>{event.data_hora}</td>
                <td>{formatEventLabel(event.tipo_evento)}</td>
                <td>{event.paciente_nome}</td>
                <td>{event.leito_numero || '—'}</td>
                <td>
                  <span className={`status status-${event.status_investigacao}`}>
                    {investigationLabels[event.status_investigacao] || event.status_investigacao}
                  </span>
                </td>
              </EventRow>
            ))}
          </tbody>
        </EventTable>
      )}
    </Card>
  );
}

export default CriticalEventsCard;
