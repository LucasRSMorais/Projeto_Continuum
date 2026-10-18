import styled from 'styled-components';

export const PageShell = styled.div`
  min-height: 100vh;
`;

export const PageLayout = styled.div`
  display: flex;
  min-height: calc(100vh - 70px);
`;

export const Container = styled.main`
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 32px;
  background: #f4f7fb;

  @media (max-width: 640px) {
    padding: 22px 14px;
  }
`;

export const Title = styled.h1`
  width: 100%;
  max-width: 1100px;
  margin: 0;
  color: #17365c;
  font-size: clamp(24px, 3vw, 32px);
`;

export const Content = styled.section`
  box-sizing: border-box;
  width: 100%;
  max-width: 1100px;
  padding: 28px;
  border: 1px solid #e2eaf4;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 12px 32px rgba(25, 55, 95, 0.08);

  @media (max-width: 640px) {
    padding: 18px 14px;
    border-radius: 12px;
  }
`;

export const Heading = styled.div`
  display: flex;
  align-items: center;
  gap: 16px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e8edf4;

  h2 {
    margin: 0 0 6px;
    color: #213c5b;
    font-size: 20px;
  }

  p {
    margin: 0;
    color: #68798e;
    line-height: 1.5;
  }

  @media (max-width: 480px) {
    align-items: flex-start;
  }
`;

export const HeadingIcon = styled.span`
  display: grid;
  flex: 0 0 48px;
  width: 48px;
  height: 48px;
  place-items: center;
  border-radius: 14px;
  background: #eaf3ff;
  color: #1769c2;
  font-size: 21px;
`;

export const Toolbar = styled.div`
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 22px 0 16px;

  strong {
    color: #324a66;
    font-size: 15px;
    font-weight: 600;
  }

  > div {
    flex: 1;
    min-width: 180px;
  }

  input {
    box-sizing: border-box;
    width: 100%;
    min-height: 42px;
    padding: 10px 12px;
    border: 1px solid #d4deea;
    border-radius: 8px;
    background: #fff;
    color: #24364b;
    font: inherit;
  }

  input:focus {
    outline: none;
    border-color: #3982d6;
    box-shadow: 0 0 0 3px rgba(57, 130, 214, 0.15);
  }

  @media (max-width: 700px) {
    align-items: stretch;
    flex-direction: column;
    gap: 10px;

    > div {
      min-width: 0;
    }
  }
`;

export const Count = styled.span`
  flex: 0 0 auto;
  color: #718198;
  font-size: 13px;
`;

export const TableWrapper = styled.div`
  width: 100%;
  overflow-x: auto;
  border: 1px solid #e3eaf3;
  border-radius: 10px;

  table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
  }

  th,
  td {
    padding: 14px 16px;
    border-bottom: 1px solid #e8edf4;
    text-align: left;
    white-space: nowrap;
  }

  th {
    background: #f3f7fc;
    color: #36516f;
    font-size: 13px;
    font-weight: 700;
  }

  td {
    color: #455a72;
    font-size: 14px;
  }

  tbody tr:last-child td {
    border-bottom: 0;
  }

  tbody tr:hover {
    background: #fafcff;
  }

  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    clip-path: inset(50%);
  }

  @media (max-width: 720px) {
    border: 0;
    overflow: visible;

    table,
    tbody,
    tr,
    td {
      display: block;
      width: 100%;
      box-sizing: border-box;
    }

    thead {
      position: absolute;
      width: 1px;
      height: 1px;
      overflow: hidden;
      clip-path: inset(50%);
      white-space: nowrap;
    }

    tbody {
      display: grid;
      gap: 12px;
    }

    tbody tr {
      padding: 8px 14px;
      border: 1px solid #e3eaf3;
      border-radius: 10px;
      background: #fff;
    }

    td {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 9px 0;
      border-bottom: 1px solid #eef1f6;
      white-space: normal;
      text-align: right;
    }

    td::before {
      flex: 0 0 auto;
      color: #718198;
      content: attr(data-label);
      font-weight: 600;
      text-align: left;
    }

    td:last-child {
      border-bottom: 0;
    }

    td[colspan] {
      display: block;
      text-align: center;
    }

    td[colspan]::before {
      content: none;
    }
  }
`;

export const DocumentName = styled.span`
  display: inline-flex;
  align-items: center;
  gap: 9px;
  color: #294a70;

  svg {
    flex: 0 0 auto;
    color: #5486bf;
  }
`;

export const Status = styled.span`
  display: inline-flex;
  align-items: center;
  gap: 7px;
  color: ${({ $status }) => (
    $status === 'Registrado'
      ? '#258053'
      : $status === 'Não registrado'
        ? '#b54747'
        : '#718198'
  )};
  font-size: 13px;
  font-weight: 600;
`;

export const ActionButton = styled.button`
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 8px 11px;
  border: 1px solid #c9d9ed;
  border-radius: 7px;
  background: #fff;
  color: #1769c2;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 150ms ease, border-color 150ms ease;

  &:hover {
    border-color: #9bbce2;
    background: #f2f7fd;
  }

  &:focus-visible {
    outline: 3px solid rgba(57, 130, 214, 0.35);
    outline-offset: 2px;
  }
`;

export const EmptyState = styled.p`
  margin: 0;
  padding: 14px;
  color: #718198;
  text-align: center;
`;

export const Details = styled.section`
  margin-top: 20px;
  padding: 20px;
  border: 1px solid #dce8f5;
  border-radius: 10px;
  background: #f8fbff;

  > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  h3 {
    margin: 0;
    color: #213c5b;
    font-size: 17px;
  }

  dl {
    display: grid;
    grid-template-columns: minmax(130px, 190px) 1fr;
    gap: 10px 16px;
    margin: 18px 0 0;
  }

  dt {
    color: #718198;
    font-size: 14px;
    font-weight: 600;
  }

  dd {
    margin: 0;
    color: #344b66;
    font-size: 14px;
    line-height: 1.5;
  }

  @media (max-width: 480px) {
    dl {
      grid-template-columns: 1fr;
      gap: 5px;
    }

    dd:not(:last-child) {
      margin-bottom: 8px;
    }
  }
`;

export const CloseButton = styled.button`
  display: grid;
  flex: 0 0 34px;
  width: 34px;
  height: 34px;
  place-items: center;
  border: 1px solid #d4deea;
  border-radius: 7px;
  background: #fff;
  color: #52657d;
  cursor: pointer;

  &:hover {
    background: #edf4fd;
    color: #1769c2;
  }

  &:focus-visible {
    outline: 3px solid rgba(57, 130, 214, 0.35);
    outline-offset: 2px;
  }
`;
