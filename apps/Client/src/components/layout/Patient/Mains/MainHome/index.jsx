import { useAuth } from '../../../../../services/utils/auth';
import { CardContainer, Title, SubTitle } from './styles';

function Main() {
    const { user, loading } = useAuth();

    return (
        <CardContainer>
            <Title>
                Bem-vindo, {loading ? '...' : user?.nome || 'Usuário'}
            </Title>
            <SubTitle>
                Transparência e segurança para você. Visualize seus dados e acompanhe seu atendimento de forma rápida e intuitiva.
            </SubTitle>
        </CardContainer>
    );
}

export default Main;