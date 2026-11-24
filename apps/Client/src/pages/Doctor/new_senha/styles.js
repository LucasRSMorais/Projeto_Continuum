import styled from 'styled-components';

export const Container = styled.div`
  box-sizing: border-box;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 40px 20px;
  background: linear-gradient(135deg, #eef5ff 0%, #f7f9fc 55%, #e9f2ff 100%);
`;

export const Content = styled.div`
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
  flex-direction: column;
  gap: 20px;
  box-sizing: border-box;
  width: min(100%, 440px);
  padding: 32px;
  background: #fff;
  border: 1px solid #e5eaf2;
  border-radius: 16px;
  box-shadow: 0 16px 40px rgba(25, 55, 95, 0.12);

  > p {
    margin: 0;
    color: #61738a;
    line-height: 1.6;
    text-align: center;
  }

  @media (max-width: 600px) {
    padding: 24px 20px;
    border-radius: 12px;
  }
`;

export const Title = styled.h1`
  margin: 0;
  color: #17365c;
  font-size: clamp(24px, 5vw, 30px);
  font-weight: 700;
  text-align: center;
`;

export const LabelFirstAcess = styled.label`
  font-size: 14px;
  color: #676767;
`;

export const LabelSignup = styled.label`
  font-size: 16px;
  color: #676767;
`;

export const labelError = styled.label`
  padding: 11px 13px;
  border: 1px solid #f3c7c7;
  border-radius: 8px;
  background: #fff5f5;
  color: #b42318;
  font-size: 14px;
  line-height: 1.45;
`;

export const Strong = styled.strong`
  cursor: pointer;

  a {
    text-decoration: none;
    color: #676767;
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
    min-height: 48px;
    padding: 12px 14px;
    border: 1px solid #d6deea;
    border-radius: 9px;
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
    min-height: 48px;
    border-radius: 9px;
    font-weight: 600;
    transition: background-color 150ms ease, transform 150ms ease;
  }

  > button:last-child:hover {
    background-color: #035bbf;
  }

  > button:last-child:active {
    transform: translateY(1px);
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

  > button {
    display: flex;
    flex: 0 0 48px;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    padding: 0;
    border: 1px solid #d6deea;
    border-radius: 9px;
    background: #f5f8fc;
    color: #52657d;
    cursor: pointer;
    transition: background-color 150ms ease;
  }

  > button:hover {
    background: #eaf2fc;
  }
`;
