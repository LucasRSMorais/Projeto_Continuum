import { Container, Heading, Menu } from './styles';


function SidebarPaciente() {
  return (
    <Container>
      <Heading>Menu</Heading>
      <Menu aria-label="Navegação principal">
        <ul>
          <li><a href="/Paciente/home">Inicio</a></li>
          <li><a href="">Exames</a></li>
          <li><a href="/Paciente/consentimentos">Consentimentos</a></li>
        </ul>
      </Menu>
    </Container>
  );
}

export default SidebarPaciente;