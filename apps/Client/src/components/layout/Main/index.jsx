import { MainContainer, KPISection } from './styles';
import KPI_Card from '../../Cards/Dashboard/KPI_Card';

function Main() {
  return (
    <MainContainer>
      <KPISection>
        <KPI_Card title="CARD 1" value={0} icon="/src/assets/icons/group-svgrepo-com.svg" backgroundColor="#059669" />
        <KPI_Card title="CARD 2" value={0} icon="/src/assets/icons/chart.svg" backgroundColor="#D97706" />
        <KPI_Card title="Alertas Críticos" subtitle="hoje" value={18} icon="/src/assets/icons/alert.svg" backgroundColor="#EF4444" />
        <KPI_Card title="Média de Espera" subtitle="hoje" value={4} unit="min" icon="/src/assets/icons/hhourglass-svgrepo-com.svg" backgroundColor="#027373" />
      </KPISection>
      <section>

      </section>
      <section>

      </section>
    </MainContainer>
  );
}

export default Main;