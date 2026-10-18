import { LuSquareCheckBig } from "react-icons/lu";
import { CardContainer, CardHeader, List, ListItem, ItemHeader, ItemSubtext} from './styles';


const Pending_Card = ({ tasks = [], loading = false, error = null }) => {
  return (
    <CardContainer>
        <CardHeader>
            <LuSquareCheckBig size={20} color="#333" />
            <h2>Pendências</h2>
        </CardHeader>
        <List>
            {loading ? (
                <ItemSubtext>Carregando pendências...</ItemSubtext>
            ) : error ? (
                <ItemSubtext role="alert">Não foi possível carregar as pendências.</ItemSubtext>
            ) : tasks.length > 0 ? (
                tasks.map((task) => (
                    <ListItem key={task.assinatura_id}>
                        <ItemHeader>{task.paciente_nome}</ItemHeader>
                        <ItemSubtext>{task.descricao_alta} — Leito {task.leito_numero}</ItemSubtext>
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

export default Pending_Card;