import { FaClipboardList } from 'react-icons/fa';
import { CardContainer, CardHeader, List, ListItem, ItemHeader, ItemSubtext} from './styles';


const WaitingLine_Card = ({ patients = [] }) => {
  return (
    <CardContainer>
      <CardHeader>
        <FaClipboardList size={20} color="#333" />
        <h2>Fila de Aguardando (Admissões)</h2>
      </CardHeader>
      <List>
        {patients.length > 0 ? (
          // Se houver pacientes, iteramos sobre o array usando o .map()
          patients.map((patient, index) => (
            // O React exige uma 'key' única para cada elemento de uma lista. 
            // Usamos 'fila_id' que vem diretamente da nossa VIEW do banco de dados.
            <ListItem key={patient.fila_id}>
              <ItemHeader>
                {/* Numeração do paciente na fila (1., 2., 3...) */}
                <span>{index + 1}.</span>
                <span>
                  {patient.paciente_nome} 
                  {patient.setor_nome && ` - ${patient.setor_nome}`}
                </span>
              </ItemHeader>
              <ItemSubtext>
                Gravidade: {patient.gravidade} | Origem: {patient.origem}
              </ItemSubtext>
              
            </ListItem>
          ))
        ) : (
          // Fallback Visual: Caso a lista (patients) esteja vazia, exibe esta mensagem.
          // É uma boa prática de UX para mostrar ao médico que o sistema carregou, mas a fila realmente está vazia.
          <ItemSubtext style={{ marginTop: '1rem', textAlign: 'center' }}>
            Não há utentes a aguardar admissão neste setor.
          </ItemSubtext>
        )}
      </List>
    </CardContainer>
  );
}

export default WaitingLine_Card;