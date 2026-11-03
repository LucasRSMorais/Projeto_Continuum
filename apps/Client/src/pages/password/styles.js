import styled from 'styled-components'


// Estilos específicos para a página de login, aplicados apenas nesta página

export const Container = styled.div`
  box-sizing: border-box;
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 24px;
  padding: 48px 20px;
  background:
    radial-gradient(ellipse at 10% 10%, rgba(71, 139, 218, 0.12), transparent 35%),
    radial-gradient(ellipse at 90% 90%, rgba(71, 139, 218, 0.1), transparent 35%),
    #f3f7fc;
`;

export const Content = styled.div`
  position: relative;
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
  flex-direction: column;
  gap: 22px;
  box-sizing: border-box;
  width: min(100%, 460px);
  padding: 36px;
  background-color: #fff;
  border: 1px solid #e2eaf4;
  border-radius: 18px;
  box-shadow: 0 24px 60px rgba(25, 55, 95, 0.13);
  overflow: hidden;

  &::before {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    height: 4px;
    background: linear-gradient(90deg, #1769c2, #57a1ec);
    content: '';
  }

  > p {
    margin: 0;
    color: #61738a;
    font-size: 15px;
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
    transition: background-color 160ms ease;
  }

  > p button:hover {
    background: #f2f7fd;
  }

  @media (max-width: 600px) {
    gap: 18px;
    padding: 28px 20px;
    border-radius: 14px;
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
  gap: 16px;
  width: 100%;

  > div {
    width: 100%;
  }

  input {
    box-sizing: border-box;
    width: 100%;
    min-height: 48px;
    padding: 12px 14px;
    border: 1px solid #d4deea;
    border-radius: 8px;
    background: #fff;
    color: #24364b;
    font-family: inherit;
    font-size: 15px;
    transition: border-color 160ms ease, box-shadow 160ms ease;
  }

  input::placeholder {
    color: #8998aa;
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
    border-radius: 8px;
    font-weight: 600;
    transition: background-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
  }

  > button:hover {
    background-color: #035bbf;
    box-shadow: 0 5px 14px rgba(3, 91, 191, 0.22);
  }

  > button:active {
    transform: translateY(1px);
  }

  p {
    margin: 0;
    color: #61738a;
    font-size: 14px;
    line-height: 1.5;
  }

  strong {
    color: #1769c2;
  }

  > button:focus-visible,
  input:focus-visible,
  a:focus-visible {
    outline: 3px solid rgba(57, 130, 214, 0.4);
    outline-offset: 3px;
  }
`;