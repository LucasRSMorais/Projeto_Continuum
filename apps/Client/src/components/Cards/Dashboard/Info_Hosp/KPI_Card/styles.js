import styled from 'styled-components';


export const HospCardContainer = styled.div`
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  background-color: #ffffff;
  width: 300px;
  font-family: sans-serif;
`;

export const HospTitle = styled.h3`
  margin: 0 0 16px 0;
  color: #4b5563;
  text-align: center;
  font-size: 16px;
`;

export const ChartWrapper = styled.div`
  position: relative;
  height: 150px;
  display: flex;
  justify-content: center;
`;

export const ValueContainer = styled.div`
  position: absolute;
  bottom: 10px;
  text-align: center;
`;

export const Value = styled.span`
  font-size: 36px;
  font-weight: bold;
  color: #111827;
  display: block;
`;

export const MaxValue = styled.span`
  font-size: 12px;
  color: #6b7280;
`;

export const LoadingText = styled.div`
  padding-top: 40px;
  color: #9ca3af;
`;