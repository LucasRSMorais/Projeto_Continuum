import styled from 'styled-components';

// Estilos específicos para a página de registro, aplicados apenas nesta página

export const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 20px;
  height: 100vh;
`;

export const Content = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 15px;
  width: 100%;
  box-shadow: 0 1px 2px #0003;
  padding: 20px;
  background-color: #fff;
  border-radius: 5px;
  max-width: 350px;
`;

export const LabelLogin = styled.label`
  font-size: 16px;
  color: #676767;
`;

export const Title = styled.h2`
  font-size: 18px;
  font-weight: 600;
  color: #676767;
`;


export const labelError = styled.label`
  font-size: 14px;
  color: red;
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
  gap: 12px;
`;