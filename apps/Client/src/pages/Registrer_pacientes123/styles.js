import styled from 'styled-components';
import {IMaskInput} from 'react-imask';
// Estilos específicos para a página de registro, aplicados apenas nesta página

export const Container = styled.div`
  min-height: 100vh;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 18px;
  padding: 40px 20px;
  background: linear-gradient(135deg, #eef5ff 0%, #f7f9fc 55%, #e9f2ff 100%);
`;


export const Content = styled.div`
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
  flex-direction: column;
  gap: 14px;
  width: min(100%, 620px);
  box-sizing: border-box;
  box-shadow: 0 16px 40px rgba(25, 55, 95, 0.12);
  padding: 32px;
  background-color: #fff;
  border: 1px solid #e5eaf2;
  border-radius: 16px;

  > a {
    width: 100%;
    text-decoration: none;
  }

  > a button {
    max-width: none;
    background: #fff;
    color: #1769c2;
    border: 1px solid #c9d9ed;
  }

  > a button:hover {
    background: #f2f7fd;
  }

  @media (max-width: 600px) {
    padding: 22px 18px;
    border-radius: 12px;
  }
`;

export const IMaskInput1 = styled(IMaskInput)`
  box-sizing: border-box;
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #d6deea;
  border-radius: 8px;
  font-size: 16px;
  background-color: #fff;
`;

export const LabelLogin = styled.label`
  font-size: 16px;
  color: #676767;
`;

export const Title2 = styled.h3`
  margin: 0;
  font-size: 15px;
  color: #52657d;
`;



export const Title = styled.h2`
  max-width: 620px;
  margin: 0;
  color: #17365c;
  font-size: clamp(22px, 4vw, 28px);
  font-weight: 700;
  text-align: center;
`;


export const labelError = styled.label`
  font-size: 14px;
  color: #c62828;
`;

export const Strong = styled.strong`
  cursor: pointer;

  a {
    text-decoration: none;
    color: #1769c2;
  }
`;

export const Form = styled.form`
  display: flex;
  flex-direction: column;
  gap: 14px;
  width: 100%;

  h3 {
    margin: 2px 0;
    color: #263c59;
    font-size: 17px;
  }

  > div {
    width: 100%;
  }

  input:not([type='checkbox']),
  select {
    box-sizing: border-box;
    width: 100%;
    min-height: 46px;
    padding: 11px 13px;
    border: 1px solid #d6deea;
    border-radius: 8px;
    background-color: #fff;
    color: #24364b;
    font: inherit;
    transition: border-color 150ms ease, box-shadow 150ms ease;
  }

  input:not([type='checkbox']):focus,
  select:focus {
    outline: none;
    border-color: #3982d6;
    box-shadow: 0 0 0 3px rgba(57, 130, 214, 0.15);
  }

  hr {
    width: 100%;
    margin: 4px 0;
    border: 0;
    border-top: 1px solid #e7edf4;
  }

  a {
    color: #1769c2;
    font-weight: 600;
    text-decoration: none;
  }

  a:hover {
    text-decoration: underline;
  }

  label {
    color: #40536b;
    font-size: 14px;
    line-height: 1.5;
  }

  label input[type='checkbox'] {
    margin-right: 8px;
    accent-color: #1769c2;
  }

  p {
    margin: 0;
    color: #68788d;
    font-size: 13px;
  }

  button {
    max-width: none;
    min-height: 46px;
    border-radius: 8px;
    font-weight: 600;
    transition: background-color 150ms ease, transform 150ms ease;
  }

  button:hover {
    background-color: #035bbf;
  }

  button:active {
    transform: translateY(1px);
  }
`;

export const Select = styled.select`
  color: #40536b;
`;
