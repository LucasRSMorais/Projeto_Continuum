import styled from 'styled-components';

export const MainContainer = styled.main`
  width: 100%;
  padding: 24px;
`;

export const KPISection = styled.section`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
`;