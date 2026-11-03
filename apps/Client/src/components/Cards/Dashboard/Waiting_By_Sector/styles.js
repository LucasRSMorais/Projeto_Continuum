import styled from 'styled-components';

export const Card = styled.section`
  display: flex;
  min-width: 0;
  min-height: 192px;
  max-height: 450px;
  flex-direction: column;
  padding: 14px;
  overflow: hidden;
  background: #fff;
  border: 1px solid #e5e9ee;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  font-family: Arial, sans-serif;

  > header {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    color: #27313b;
  }

  > header svg {
    flex: 0 0 auto;
    color: #475467;
  }

  h2 {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 700;
  }
`;

export const PatientList = styled.div`
  overflow-y: auto;
`;

export const Patient = styled.article`
  display: grid;
  gap: 2px;
  padding: 5px 0 5px 8px;
  border-left: 2px solid #d97706;

  & + & {
    margin-top: 5px;
  }

  strong {
    color: #202833;
    font-size: 0.78rem;
  }

  span {
    color: #56616d;
    font-size: 0.69rem;
    line-height: 1.4;
  }
`;

export const EmptyState = styled.p`
  display: grid;
  min-height: 110px;
  margin: 0;
  place-items: center;
  color: #697480;
  font-size: 0.85rem;
  text-align: center;
`;
