import styled from 'styled-components';
import backgroundImage from '../../../../../assets/background/doctor2.jpg';

export const Title = styled.h1`
  font-size: 2rem;
  font-weight: 700;
  color: #045959;
  margin-bottom: 1rem;
  max-width: 50%;
`;

export const SubTitle = styled.p`
    font-size: 1.2rem;
    font-weight: 400;
    color: #045959;
    line-height: 1.5;
    max-width: 50%;
` 

export const CardContainer = styled.div`
    background-image: url(${backgroundImage});
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    padding: 2.5rem;
    border-radius: 0px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    text-align: left;
    
    /* Responsividade: largura máxima e margem de segurança */
    height: 100%;
    width: 100%;
    max-width: 94%;
    margin: 20px;
`;
