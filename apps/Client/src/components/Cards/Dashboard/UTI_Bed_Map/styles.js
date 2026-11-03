import styled from 'styled-components';

const statusColors = {
  disponivel: { background: '#198754', border: '#146c43' },
  ocupado: { background: '#c62828', border: '#a61f1f' },
  sujo: { background: '#f2b91b', border: '#d49f08' },
  em_higienizacao: { background: '#f2b91b', border: '#d49f08' },
  manutencao: { background: '#667085', border: '#475467' },
  bloqueado: { background: '#667085', border: '#475467' },
};

export const Card = styled.section`
  min-width: 0;
  min-height: 192px;
  padding: 14px;
  background: #fff;
  border: 1px solid #e5e9ee;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  font-family: Arial, sans-serif;
`;

export const CardHeader = styled.header`
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  color: #27313b;

  > svg {
    flex: 0 0 auto;
    color: #475467;
  }

  h2 {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 700;
  }

  .occupancy {
    margin-left: auto;
    color: #56616d;
    font-size: 0.74rem;
    white-space: nowrap;
  }
`;

export const BedGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 6px;

  @media (max-width: 420px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
`;

export const Bed = styled.div`
  position: relative;
  display: flex;
  min-width: 0;
  min-height: 42px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5px 14px 5px 5px;
  overflow: hidden;
  color: ${({ $status }) => ($status === 'em_higienizacao' || $status === 'sujo' ? '#362900' : '#fff')};
  background: ${({ $status }) => statusColors[$status]?.background || '#667085'};
  border: 1px solid ${({ $status }) => statusColors[$status]?.border || '#475467'};
  border-radius: 5px;
  text-align: center;

  .bed-label {
    font-size: 0.76rem;
    font-weight: 700;
    line-height: 1.15;
  }

  .bed-status {
    font-size: 0.68rem;
    line-height: 1.2;
  }

  .patient-name {
    max-width: 100%;
    overflow: hidden;
    font-size: 0.64rem;
    line-height: 1.15;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .warning-icon {
    position: absolute;
    top: 5px;
    right: 4px;
    color: ${({ $status }) => ($status === 'em_higienizacao' || $status === 'sujo' ? '#7a5100' : '#ffe16a')};
    font-size: 0.72rem;
  }
`;

export const Legend = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: 6px 12px;
  margin-top: 10px;
  color: #56616d;
  font-size: 0.66rem;

  span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }

  .available {
    background: #198754;
  }

  .occupied {
    background: #c62828;
  }

  .cleaning {
    background: #f2b91b;
  }

  .unavailable {
    background: #667085;
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
