import { Container, Heading, Menu } from './styles';


function Sidebar() {
  return (
    <Container>
      <Heading>Menu</Heading>
      <Menu aria-label="Navegação principal">
        <ul>
          <li><a href="/home">Principal / Dashboard </a></li>
          <li><a href="/">Relatórios</a></li>
          <li><a href="/">Pacientes</a></li>
          <li><a href="/">Equipe</a></li>
          <li><a href="/">KPIs</a></li>
          <li><a href="/">Analítico</a></li>
        </ul>
      </Menu>
    </Container>
  );
}

export default Sidebar;