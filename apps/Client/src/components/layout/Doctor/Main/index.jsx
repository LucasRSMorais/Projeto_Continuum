import { useState } from 'react';
import { MainContainer, ShiftToolbar, KPISection, HighPrioritySection } from './styles';
import KPI_Card from '../../../Cards/Dashboard/KPI_Card';
import WaitingLine_Card from '../../../Cards/Dashboard/Waiting_line/index.jsx';
import Pending_Card from '../../../Cards/Dashboard/Pending/index.jsx';
import BedMovements_Card from '../../../Cards/Dashboard/Bed_movements/index.jsx';
import { useDashboardCards } from '../../../../hooks/useDashboardCards';
import { useDashboardKpis } from '../../../../hooks/useDashboardKpis';
import { buildApiUrl } from '../../../../config/api';

function Main() {
  const [sectorId, setSectorId] = useState('4');
  const { patients, pending, movements, loading, error } = useDashboardCards(
    buildApiUrl(`Cards/get_dashboard_cards.php?id_setor=${sectorId}`)
  );
  const {
    kpis,
    loading: kpisLoading,
    error: kpisError,
  } = useDashboardKpis(
    buildApiUrl(`KPIs/get_dashboard_kpis.php?id_setor=${sectorId}`)
  );

  return (
    <MainContainer>
      <ShiftToolbar aria-label="Informações do plantão">
        <label>
          Setor:
          <select
            aria-label="Selecionar setor"
            value={sectorId}
            onChange={(event) => setSectorId(event.target.value)}
          >
            <option value="1">Clínica Geral</option>
            <option value="2">Cirúrgico</option>
            <option value="3">Obstétrico</option>
            <option value="4">UTI-Cardiologia</option>
            <option value="5">Neurologia</option>
            <option value="6">Isolamento</option>
            <option value="7">Enfermaria</option>
          </select>
        </label>
        <span className="shift-time">Plantão Atual: 19:00 - 07:00</span>
        <button type="button">Assumir Plantão</button>
      </ShiftToolbar>
      <KPISection>
        <KPI_Card
          title="Taxa de Ocupação"
          value={kpis.occupancy.percentage}
          unit="%"
          detail={`${kpis.occupancy.occupied} de ${kpis.occupancy.total} leitos ocupados`}
          gauge
          icon="/src/assets/icons/group-svgrepo-com.svg"
          backgroundColor="#087f5b"
          loading={kpisLoading}
          error={kpisError}
        />
        <KPI_Card
          title="Eventos Adversos"
          subtitle="(Últ. Turno)"
          value={kpis.adverseEventsLastShift}
          detail={`${kpis.adverseEventsLastShift} ${
            kpis.adverseEventsLastShift === 1 ? 'evento' : 'eventos'
          } nas últimas 12 horas`}
          icon="/src/assets/icons/chart.svg"
          backgroundColor="#d97706"
          loading={kpisLoading}
          error={kpisError}
        />
        <KPI_Card
          title="Alertas Críticos"
          subtitle="hoje"
          value={kpis.criticalAlertsToday}
          icon="/src/assets/icons/alert.svg"
          backgroundColor="#e83e4b"
          loading={kpisLoading}
          error={kpisError}
        />
        <KPI_Card
          title="Pacientes Alto Risco"
          value={kpis.highRiskPatients}
          detail={`${kpis.highRiskPatients} ${
            kpis.highRiskPatients === 1 ? 'paciente' : 'pacientes'
          }`}
          icon="/src/assets/icons/hhourglass-svgrepo-com.svg"
          backgroundColor="#087f83"
          loading={kpisLoading}
          error={kpisError}
        />
      </KPISection>
      {/* <HighPrioritySection>
        
      </HighPrioritySection> */}
      <HighPrioritySection>
        <WaitingLine_Card patients={patients} loading={loading} error={error} />
        <Pending_Card tasks={pending} loading={loading} error={error} />
        <BedMovements_Card tasks={movements} loading={loading} error={error} />
      </HighPrioritySection>
    </MainContainer>
  );
}

export default Main;