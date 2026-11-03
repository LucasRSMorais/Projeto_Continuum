import { BedMapCard } from '../UTI_Bed_Map';

function EnfermariaBedMapCard(props) {
  return (
    <BedMapCard
      {...props}
      title="Mapa Rápido de Leitos (Enfermaria)"
      areaName="Enfermaria"
    />
  );
}

export default EnfermariaBedMapCard;
