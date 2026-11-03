import { CardContainer, CardHeader, List, ListItem, ItemHeader, ItemSubtext} from './styles';
import { FaClockRotateLeft } from "react-icons/fa6";


const BedMovements_Card = ({ tasks = [], loading = false, error = null }) => {
  return (
    <CardContainer>
        <CardHeader>
            <FaClockRotateLeft size={20} color="#333" />
            <h2>Últimas Movimentações</h2>
        </CardHeader>
        <List>
            {loading ? (
                <ItemSubtext>Carregando movimentações...</ItemSubtext>
            ) : error ? (
                <ItemSubtext role="alert">Não foi possível carregar as movimentações.</ItemSubtext>
            ) : tasks.length > 0 ? (
                tasks.map((task) => (
                    <ListItem key={task.movement_id}>
                        <ItemHeader>{task.ultima_atualizacao} — {task.movimentacao_leito}</ItemHeader>
                        <ItemSubtext>{task.paciente_nome} — Leito {task.leito_numero}</ItemSubtext>
                    </ListItem>
                ))
            ) : (
                <ItemSubtext style={{ marginTop: '1rem', textAlign: 'center' }}>
                    Nenhuma pendência encontrada.
                </ItemSubtext>
            )}
        </List>
    </CardContainer>
  );
}

export default BedMovements_Card;