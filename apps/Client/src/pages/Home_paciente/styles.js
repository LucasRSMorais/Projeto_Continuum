// Página Principal do site
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

export const Title = styled.h1`
  font-size: 2rem;
  color: #222;
`;


