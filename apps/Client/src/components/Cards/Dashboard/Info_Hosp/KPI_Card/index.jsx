import { Chart as ChartJS, ArcElement, Tooltip } from 'chart.js';
import { Doughnut } from 'react-chartjs-2';
// Retorna 5 níveis até src/ e entra em hooks/
import { useKpiData } from '../../../../../hooks/useKpiData';
// Retorna 4 níveis até components/ e entra em layout/Doctor/Main/styles
import { HospCardContainer, HospTitle, ChartWrapper, ValueContainer, Value, MaxValue, LoadingText} from './styles';
import { buildApiUrl } from '../../../../../config/api';

ChartJS.register(ArcElement, Tooltip);

export const KPI_CardHosp = () => {
  // Consumindo o custom hook
// const { kpiData, loading, error } = useKpiData('http://localhost:8000/kpi_plantoes.php');
const { kpiData, loading, error } = useKpiData(buildApiUrl('KPIs/kpi_plantoes.php'));

  const chartData = {
    labels: ['Concluído', 'Pendente'],
    datasets: [{
      data: [kpiData.value, kpiData.max - kpiData.value],
      backgroundColor: ['#3b82f6', '#e5e7eb'],
      borderWidth: 0,
      circumference: 180, // Transforma em semicírculo
      rotation: 270,      // Posiciona a base para baixo
    }],
  };

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '80%',
    plugins: { tooltip: { enabled: false } },
  };

  return (
    <HospCardContainer>
      <HospTitle>Plantões Concluídos</HospTitle>
      <ChartWrapper>
        {loading ? (
          <LoadingText>Carregando...</LoadingText>
        ) : error ? (
          <LoadingText style={{ color: '#ef4444' }}>{error}</LoadingText>
        ) : (
          <>
            <Doughnut data={chartData} options={chartOptions} />
            <ValueContainer>
              <Value>{kpiData.value}</Value>
              <MaxValue>de {kpiData.max}</MaxValue>
            </ValueContainer>
          </>
        )}
      </ChartWrapper>
    </HospCardContainer>
  );
};