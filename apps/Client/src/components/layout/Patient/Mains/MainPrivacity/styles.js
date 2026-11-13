import styled from 'styled-components';

export const CardContainer = styled.main`
  background-color: #fff;
  padding: 2.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
  text-align: left;
  width: calc(100% - 40px);
  max-width: 1000px;
  margin: 20px auto;

  .consent-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    padding: 16px;
    background-color: #f5f5f5;
    border-radius: 8px;
  }

  @media (max-width: 600px) {
    padding: 1.5rem;

    .consent-row {
      align-items: flex-start;
      gap: 8px;
    }
  }
`;

export const Title = styled.h1`
  margin: 0 0 1rem;
  color: #045959;
  font-size: 1.75rem;
`;

export const SubTitle = styled.p`
  margin: 0;
  color: #5f6b7a;
  font-size: 1rem;
  line-height: 1.6;
`;
