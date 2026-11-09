import styled from 'styled-components';
import { Link } from 'react-router-dom';

// Estiliza um Link como botão para manter a navegação interna do React Router.
export const MyHeaderButton = styled(Link)`
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 14px;
  border-radius: 3px;
  text-decoration: none;
  font-size: 18px;
  color: #027373;
  white-space: nowrap;

  &:hover {
    background-color: #F2f2f2;
  }
`;