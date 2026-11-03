import styled from 'styled-components';

export const Card = styled.section`
  min-width: 0;
  min-height: 192px;
  padding: 14px;
  overflow: hidden;
  background: #fff;
  border: 1px solid #e5e9ee;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
`;

export const CardHeader = styled.header`
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  color: #27313b;

  svg {
    flex: 0 0 auto;
    color: #b42318;
  }

  h2 {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 700;
  }
`;

export const EventTable = styled.table`
  width: 100%;
  border-collapse: collapse;
  table-layout: auto;
  text-align: left;
  font-size: 0.76rem;

  th,
  td {
    padding: 8px 5px;
    border-bottom: 1px solid #e5e9ee;
    vertical-align: middle;
  }

  th {
    color: #34404b;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
  }

  td {
    color: #39434d;
  }

  tbody tr:last-child td {
    border-bottom: 0;
  }

  @media (max-width: 650px) {
    display: block;
    overflow-x: auto;

    th,
    td {
      white-space: nowrap;
    }
  }
`;

export const TableHeader = styled.th``;

export const EventRow = styled.tr`
  .status {
    display: inline-block;
    padding: 3px 7px;
    border-radius: 999px;
    font-size: 0.68rem;
    white-space: nowrap;
  }

  .status-pendente {
    color: #8a4b08;
    background: #fff3d6;
  }

  .status-em_investigacao {
    color: #175cd3;
    background: #eff8ff;
  }

  .status-concluido {
    color: #067647;
    background: #ecfdf3;
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
