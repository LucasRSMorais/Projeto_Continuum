import styled from 'styled-components'

export const MyButton = styled.button`
  padding: 16px 20px;
  border: ${({ $variant }) => ($variant === 'secondary' ? '1px solid #046ee5' : 'none')};
  border-radius: 5px;
  width: 100%;
  cursor: pointer;
  font-size: 16px;
  background-color: ${({ $variant }) => ($variant === 'secondary' ? '#fff' : '#046ee5')};
  color: ${({ $variant }) => ($variant === 'secondary' ? '#046ee5' : '#fff')};
  max-width: 350px;

  &:disabled {
    cursor: not-allowed;
    opacity: 0.65;
  }
`;