import { Container, Heading, Menu } from './styles';


function SidebarPaciente() {



  return (
    <Container>
      <Heading>Menu</Heading>
      <Menu aria-label="Navegação principal">
        <ul>
          <li><a href="/home_paciente"> Aréa do Paciente </a></li>
          <li><a href = "">Inicio</a></li>
          <li><a href="">Exames</a></li>
        </ul>
      </Menu>
    </Container>
  );
}

export default SidebarPaciente;