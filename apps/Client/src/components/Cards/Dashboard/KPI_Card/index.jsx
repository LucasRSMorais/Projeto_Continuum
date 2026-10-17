 import { CardContainer, KpiCard } from "./styles";


const KPI_Card = ({ title, value, unit, subtitle, icon, backgroundColor }) => (
  <CardContainer>
    <KpiCard $backgroundColor={backgroundColor}>
        <img src={icon} alt={`icon ${title}`} />
        <h3>{title}</h3>{subtitle && <span>{subtitle}</span>}
        <p>
            {value}
            {unit ? ` ${unit}` : ''}
        </p>
    </KpiCard>
  </CardContainer>
);

export default KPI_Card;