import { HeaderContainer } from './styles';

function Header() {
  return (
    <HeaderContainer>
        <ul>
            <li><img src="/logo.png" alt="Logo" /></li>
            <li>Download</li>
            <li>Perfil</li>
        </ul>
    </HeaderContainer>
  );
}