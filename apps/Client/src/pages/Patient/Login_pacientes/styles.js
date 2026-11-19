import styled from 'styled-components';

// Estilos específicos para a página de login, aplicados apenas nesta página

export const Container = styled.div`
  box-sizing: border-box;
  min-height: 100vh;
  display: grid;
  grid-template-columns: minmax(320px, 1fr) minmax(420px, 1fr);
  background: #f5f8fc;

  @media (max-width: 900px) {
    grid-template-columns: 38% 62%;
  }

  @media (max-width: 680px) {
    grid-template-columns: 1fr;
  }
`;

export const Visual = styled.div`
  position: relative;
  min-height: 100vh;
  overflow: hidden;
  background: #1d66bd;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 58% center;
  }

  @media (max-width: 680px) {
    min-height: 220px;
    height: 220px;
  }
`;

export const VisualOverlay = styled.div`
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    rgba(29, 102, 189, 0.12),
    rgba(18, 78, 150, 0.72)
  );
`;

export const VisualCaption = styled.div`
  position: absolute;
  right: 24px;
  bottom: 32px;
  left: 24px;
  color: #fff;
  text-align: center;
  text-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);

  strong,
  span {
    display: block;
  }

  strong {
    font-size: clamp(24px, 3vw, 36px);
  }

  span {
    margin-top: 6px;
    font-size: clamp(13px, 1.5vw, 16px);
  }

  @media (max-width: 680px) {
    bottom: 20px;
  }
`;

export const AuthPanel = styled.div`
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 40px clamp(24px, 6vw, 72px);
  background: #fff;

  @media (max-width: 680px) {
    min-height: calc(100vh - 220px);
    padding: 32px 20px;
  }
`;

export const Brand = styled.img`
  width: min(220px, 60%);
  max-height: 90px;
  margin-bottom: 24px;
  object-fit: contain;
`;

export const Content = styled.div`
  display: flex;
  align-items: stretch;
  justify-content: flex-start;
  flex-direction: column;
  gap: 18px;
  box-sizing: border-box;
  width: min(100%, 390px);
  padding: 0;
  background-color: #fff;

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
  position: relative;
  width: 100%;

  > div {
    width: 100%;
  }

  input {
    width: 100%;
    padding-right: 52px;
  }

  > button {
    position: absolute;
    top: 50%;
    right: 6px;
    display: grid;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 8px;
    transform: translateY(-50%);
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: #61738a;
    cursor: pointer;
  }

  > button:hover {
    background: #eaf2fc;
    color: #1769c2;
  }

  > button:focus-visible {
    outline: 2px solid #3982d6;
    outline-offset: 1px;
  }
`;