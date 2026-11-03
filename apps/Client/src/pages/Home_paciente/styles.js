// Página principal do paciente.
// Este arquivo define os estilos base da tela inicial e os blocos visuais
// que compõem o layout da área do usuário.
import styled from 'styled-components';

export const PageShell = styled.div`
  display: block;
`;

export const PageLayout = styled.div`
  display: flex;
  min-height: calc(100vh - 70px);
`;

export const Conteiner = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 0;
  min-height: 0;
  gap: 16px;
  padding: 24px;
`;

export const Logo = styled.img`
  width: 125px;
`;

export const Title = styled.h1`
  font-size: 2rem;
  color: #222;
`;

export const HeaderContainer = styled.header`
  align-items: center;
  padding: 15px 30px;
  display: flex;
  background: linear-gradient(to left, green, white);
  justify-content: space-between;
`;

export const DivisaoLink = styled.a`
  padding: 10px;
  text-decoration: none;
  color: #fff;

  &:hover {
    background: gray;
  }
`;


