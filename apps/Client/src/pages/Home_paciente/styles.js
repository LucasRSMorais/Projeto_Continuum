// Página Principal do site
import styled from 'styled-components';

export const Divisao = styled.div`
top : 100%
position: absolute;

right : 0 ;

display : none;
flex-direction : column ;
padding:10px;

min-width : 180px

background : green ;


`;


export const DIV = styled.div`
&:hover >div{
display : flex ;
}

position : relative;

`;

export const Menu = styled.nav`
display : flex;
gap :30px;
`;

export const MenuLink = styled.a`

text-decoration : none;
color : #fff;
font-size : 16px;


`;

export const Conteiner = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  gap: 16px;
  padding: 24px;
`;

export const Logo = styled.img`
width:125px ;


`; 

export const Title = styled.h1`
  font-size: 2rem;
  color: #222;
`;

export const HeaderContainer = styled.header`
align-items : center;

padding : 15px 30px;

display:   flex;
background : linear-gradient(to left , green , white);
justify-content : space-between ;

`;

export const DivisaoLink = styled.a`

padding : 10px
text-decoration : none ;


color : #fff ;


&:hover{
background : gray;
}

`;


