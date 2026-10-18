import styled from 'styled-components';

export const MainContainer = styled.main`
  width: 100%;
  padding: 0 24px 24px;
`;

export const ShiftToolbar = styled.section`
  display: flex;
  align-items: center;
  gap: 28px;
  min-height: 48px;
  margin: 0 -24px 10px;
  padding: 7px 24px;
  background: #f8fafc;
  border-bottom: 1px solid #d8dee5;
  color: #30363d;
  font-size: 0.84rem;

  label {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  select {
    padding: 4px 18px 4px 5px;
    color: inherit;
    background: transparent;
    border: 0;
    font: inherit;
  }

  .shift-time {
    white-space: nowrap;
  }

  button {
    margin-left: auto;
    padding: 7px 14px;
    color: #fff;
    background: #087f8c;
    border: 1px solid #066c77;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    font: inherit;
    font-size: 0.76rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
  }

  button:hover {
    background: #066c77;
  }

  @media (max-width: 600px) {
    flex-wrap: wrap;
    gap: 6px 14px;
    padding: 8px 16px;

    button {
      margin-left: 0;
    }
  }
`;

export const KPISection = styled.section`
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;

  @media (max-width: 900px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @media (max-width: 480px) {
    grid-template-columns: minmax(0, 1fr);
  }
`;

export const HighPrioritySection = styled.section`
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  align-items: stretch;
  gap: 20px;
  margin-top: 24px;

  @media (max-width: 1100px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @media (max-width: 700px) {
    grid-template-columns: minmax(0, 1fr);
  }
`;