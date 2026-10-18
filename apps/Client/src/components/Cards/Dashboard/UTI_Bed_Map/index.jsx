import { FaBed, FaCircleExclamation } from 'react-icons/fa6';
import {
  Bed,
  BedGrid,
  Card,
  CardHeader,
  EmptyState,
  Legend,
} from './styles';

const bedStatusLabels = {
  disponivel: 'Disponível',
  ocupado: 'Ocupado',
  sujo: 'Aguardando limpeza',
  em_higienizacao: 'Higienização',
  manutencao: 'Manutenção',
  bloqueado: 'Bloqueado',
};

export function BedMapCard({
  beds = [],
  loading = false,
  error = null,
  title,
  areaName,
}) {
  const occupied = beds.filter((bed) => bed.status_leito === 'ocupado').length;

  return (
    <Card>
      <CardHeader>
        <FaBed aria-hidden="true" />
        <h2>{title}</h2>
        {!loading && !error && beds.length > 0 && (
          <span className="occupancy">
            {occupied}/{beds.length} ocupados
          </span>
        )}
      </CardHeader>
      {loading ? (
        <EmptyState>Carregando leitos da {areaName}...</EmptyState>
      ) : error ? (
        <EmptyState role="alert">Não foi possível carregar o mapa de leitos.</EmptyState>
      ) : beds.length === 0 ? (
        <EmptyState>
          Não há leitos cadastrados para {areaName === 'UTI' ? 'a UTI' : 'a enfermaria'}.
        </EmptyState>
      ) : (
        <>
          <BedGrid aria-label={`Leitos da ${areaName}`}>
            {beds.map((bed) => {
              const statusLabel = bedStatusLabels[bed.status_leito] || bed.status_leito;
              return (
                <Bed
                  key={bed.bed_id}
                  $status={bed.status_leito}
                  title={`${bed.leito_numero}: ${statusLabel}${bed.paciente_nome ? ` — ${bed.paciente_nome}` : ''}`}
                  aria-label={`${bed.leito_numero}, ${statusLabel}${bed.paciente_nome ? `, paciente ${bed.paciente_nome}` : ''}${Number(bed.alerta_critico) ? ', alerta de risco crítico' : ''}`}
                >
                  <span className="bed-label">{bed.leito_numero}</span>
                  <span className="bed-status">{statusLabel}</span>
                  {bed.paciente_nome && <span className="patient-name">{bed.paciente_nome}</span>}
                  {Number(bed.alerta_critico) > 0 && (
                    <FaCircleExclamation className="warning-icon" aria-hidden="true" />
                  )}
                </Bed>
              );
            })}
          </BedGrid>
          <Legend aria-label="Legenda dos status dos leitos">
            <span><i className="available" />Disponível</span>
            <span><i className="occupied" />Ocupado</span>
            <span><i className="cleaning" />Higienização</span>
            <span><i className="unavailable" />Indisponível</span>
          </Legend>
        </>
      )}
    </Card>
  );
}

function UtiBedMapCard(props) {
  return (
    <BedMapCard
      {...props}
      title="Mapa Rápido de Leitos (UTI)"
      areaName="UTI"
    />
  );
}

export default UtiBedMapCard;
