 import { CardContainer, KpiCard } from './styles';

 const KPI_Card = ({
   title,
   value,
   unit,
   subtitle,
   detail,
   icon,
   backgroundColor,
   gauge = false,
   loading = false,
   error = null,
 }) => (
   <CardContainer>
     <KpiCard $backgroundColor={backgroundColor} $hasGauge={gauge}>
       <img src={icon} alt="" aria-hidden="true" />
       <h3>{title}</h3>
       {subtitle && <span className="subtitle">{subtitle}</span>}
       {loading ? (
         <strong className="value">...</strong>
       ) : error ? (
         <strong className="value" role="alert">—</strong>
       ) : gauge ? (
         <div
           className="gauge"
           role="img"
           aria-label={`${value}% de ocupação, ${detail}`}
         >
           <svg viewBox="0 0 120 68" aria-hidden="true">
             <path className="gauge-track" d="M 10 60 A 50 50 0 0 1 110 60" />
             <path
               className="gauge-value"
               d="M 10 60 A 50 50 0 0 1 110 60"
               pathLength="100"
               strokeDasharray={`${value} 100`}
             />
           </svg>
           <strong>{value}{unit}</strong>
         </div>
       ) : (
         <strong className="value">{value}{unit ? ` ${unit}` : ''}</strong>
       )}
       <span className="detail">
         {error ? 'Não foi possível carregar' : loading ? 'Carregando...' : detail}
       </span>
     </KpiCard>
   </CardContainer>
 );

export default KPI_Card;