import styled from 'styled-components';

export const CardContainer = styled.div`
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  padding: 16px;
  width: 100%;
  max-width: 400px;
  font-family: Arial, sans-serif;
`; 

export const CardHeader = styled.div`
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;

  h2 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: #1a1a1a;
  }
`;

export const List = styled.ul`
  list-style: none;
  padding: 0;
  margin: 0;
`;

export const ListItem = styled.li`
  padding: 12px 0;
  border-bottom: 1px solid #eaeaea;

  &:first-child {
    padding-top: 0;
  }

  &:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
`;

export const ItemHeader = styled.div`
  display: flex;
  align-items: baseline;
  gap: 4px;
  font-size: 14px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 4px;
`;

export const ItemSubtext = styled.div`
  font-size: 13px;
  color: #555555;
  margin-left: 16px;
`;