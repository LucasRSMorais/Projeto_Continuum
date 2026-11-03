import styled from 'styled-components';


export const CardContainer = styled.div`
  min-width: 0;
`;

export const KpiCard = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  height: 104px;
  padding: 10px 12px 8px;
  overflow: hidden;
  color: #f8fafc;
  background-color: ${({ $backgroundColor }) => $backgroundColor || '#027373'};
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 7px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.16);

  > img {
    position: absolute;
    top: 6px;
    right: 7px;
    width: 18px;
    height: 18px;
    padding: 2px;
    background: #f1f5f9;
    border-radius: 3px;
  }

  > h3 {
    margin: 0;
    padding: 0 18px;
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.1;
    text-align: center;
  }

  .subtitle {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.68rem;
    line-height: 1.1;
  }

  .value {
    margin-top: 5px;
    font-size: 1.8rem;
    font-weight: 500;
    line-height: 1;
  }

  .detail {
    margin-top: 4px;
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.68rem;
    line-height: 1.1;
    text-align: center;
  }

  .gauge {
    position: relative;
    width: 92px;
    height: 49px;
    margin-top: 1px;
  }

  .gauge svg {
    display: block;
    width: 100%;
    height: 100%;
    overflow: visible;
  }

  .gauge path {
    fill: none;
    stroke-width: 10;
  }

  .gauge-track {
    stroke: rgba(255, 255, 255, 0.32);
  }

  .gauge-value {
    stroke: #f8fafc;
    stroke-linecap: butt;
  }

  .gauge strong {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    font-size: 1.3rem;
    line-height: 1;
    text-align: center;
  }
`;