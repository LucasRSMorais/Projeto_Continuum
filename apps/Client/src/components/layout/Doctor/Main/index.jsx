import { MainContainer, KPISection, HighPrioritySection } from './styles';
import KPI_Card from '../../../Cards/Dashboard/KPI_Card';
import { KPI_CardHosp } from '../../../Cards/Dashboard/Info_Hosp/KPI_Card';
import WaitingLine_Card from '../../../Cards/Dashboard/Waiting_line/index.jsx';

function Main() {
  return (
    <MainContainer>
      <KPISection>
        <KPI_Card title="Taxa de Ocupação" value={0} icon="/src/assets/icons/group-svgrepo-com.svg" backgroundColor="#059669" />
        <KPI_Card title="Eventos Adversos" subtitle="Últ. Turno" value={0} icon="/src/assets/icons/chart.svg" backgroundColor="#D97706" />
        <KPI_Card title="Alertas Críticos" subtitle="hoje" value={18} icon="/src/assets/icons/alert.svg" backgroundColor="#EF4444" />
        <KPI_Card title="Pacientes Alto Risco" subtitle="hoje" value={4} unit="min" icon="/src/assets/icons/hhourglass-svgrepo-com.svg" backgroundColor="#027373" />
      </KPISection>
      {/* <HighPrioritySection>
        
      </HighPrioritySection> */}
      <HighPrioritySection>
        <WaitingLine_Card />
      </HighPrioritySection>
    </MainContainer>
  );
}

export default Main;