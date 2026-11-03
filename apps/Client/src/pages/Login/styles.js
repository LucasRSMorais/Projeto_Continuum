import styled from 'styled-components'


// Estilos específicos para a página de login, aplicados apenas nesta página

export const Container = styled.div`
  box-sizing: border-box;
  min-height: 100vh;
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
  gap: 18px;
  box-sizing: border-box;
  width: min(100%, 460px);
  padding: 32px;
  background-color: #fff;
  border: 1px solid #e5eaf2;
  border-radius: 16px;
  box-shadow: 0 16px 40px rgba(25, 55, 95, 0.12);

  > p {
    margin: 0;
    color: #61738a;
    text-align: center;
    line-height: 1.5;
  }

  > p button {
    padding: 9px 18px;
    border: 1px solid #c9d9ed;
    border-radius: 8px;
    background: #fff;
    color: #1769c2;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
  }

  > p button:hover {
    background: #f2f7fd;
  }

  @media (max-width: 600px) {
    padding: 24px 18px;
    border-radius: 12px;
  }
`;

export const Title = styled.h1`
  margin: 0;
  color: #17365c;
  font-size: clamp(22px, 4vw, 28px);
  font-weight: 700;
  text-align: center;
`;

export const LabelFirstAcess = styled.label`
  font-size: 14px;
  color: #61738a;
`;

export const LabelSignup = styled.label`
  font-size: 16px;
  color: #61738a;
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

  a:hover {
    text-decoration: underline;
  }
`;

export const Form = styled.form`
  display: flex;
  flex-direction: column;
  gap: 14px;
  width: 100%;

  > div:not(:last-child) {
    width: 100%;
  }

  input {
    box-sizing: border-box;
    width: 100%;
    min-height: 46px;
    padding: 11px 13px;
    border: 1px solid #d6deea;
    border-radius: 8px;
    background: #fff;
    color: #24364b;
    font: inherit;
    transition: border-color 150ms ease, box-shadow 150ms ease;
  }

  input:focus {
    outline: none;
    border-color: #3982d6;
    box-shadow: 0 0 0 3px rgba(57, 130, 214, 0.15);
  }

  > button {
    box-sizing: border-box;
    width: 100%;
    max-width: none;
    min-height: 46px;
    border-radius: 8px;
    font-weight: 600;
    transition: background-color 150ms ease, transform 150ms ease;
  }

  > button:hover {
    background-color: #035bbf;
  }

  > button:active {
    transform: translateY(1px);
  }

  p {
    margin: 0;
    color: #61738a;
    line-height: 1.5;
  }
`;

export const PasswordRow = styled.div`
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;

  > div {
    flex: 1;
    min-width: 0;
  }

  input {
    width: 100%;
  }

  > button {
    display: flex;
    flex: 0 0 46px;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    padding: 0;
    border: 1px solid #d6deea;
    border-radius: 8px;
    background: #f5f8fc;
    color: #52657d;
    cursor: pointer;
  }

  > button:hover {
    background: #eaf2fc;
  }
`;