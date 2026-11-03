import styled from 'styled-components';

export const Container = styled.main`
flex: 1;
min-width: 0;
min-height: calc(100vh - 70px);
display: flex;
padding: 20px 40px;
background-color:  $gray ;
flex-direction: column;
align-items: center;
justify-content: center;
`;


export const Title = styled.h1`
width: 100%;
font-size: 2rem;
max-width: 800px;
margin-bottom: 20px;
color : black;
text-align: center;
`;


export const Content = styled.div`

width: 100%;
max-width: 800px;
background-color: white;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
padding: 20px;
@media (max-width: 600px) {
  padding: 10px;
}
`;


export const Form = styled.form`
display: flex;
flex-direction: column;
gap: 20px;
label {
  font-weight: bold;
  margin-bottom: 5px;
}

input {
width: 100%;
box-sizing: border-box;
padding: 10px;
border: 1px solid #ccc;
border-radius: 4px;
font-size: 1rem;
}

input[readonly] {
  background-color: #f5f5f5;
  color: #999;
  cursor: not-allowed;
}


button {
  cursor: pointer;
  
}

button:disabled {
opacity: 0.6;
cursor: not-allowed;
}


`;

export const Section = styled.div`

padding: 20px;
border: 1px solid #ccc;
border-radius: 4px;
display: flex;
flex-direction: column;
gap: 10px;
background-color: #f9f9f9;


p{
margin: 0;
color: #333;
font-size: 0.9rem;
line-height: 1.4;
}




button {
  padding: 10px  18px;
    background-color: #007bff;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 1rem;
    font-weight: bold;
    transition: background-color 0.3s ease;
}



button:hover:not(:disabled) {
  background-color: #0056b3;
}


@media (max-width: 600px) {
  padding: 10px;
}
`;


export const SectionTitle = styled.h2`
display: flex;
align-items: center;
gap: 10px;
margin : 0;
color: #333;
font-size: 1.2rem;
svg {
  flex-shrink: 0;
  font-size: 1.5rem;
}
`;


export const PasswordRow = styled.div`
display: flex;
align-items: center;
gap: 10px;


input {
  flex: 1;
  min-width: 0;
}


button {
display: flex;
flex: 0 0 30px;
width: 30px;
height: 30px;
align-items: center;
justify-content: center;
padding: 0;
border : 1px solid #ccc;
border-radius: 4px;
background-color: #f5f5f5;
color: #333;
font-size: 0.8rem;
}
`;


export const Success = styled.p`



color: green  !important;
font-weight: bold;


`;





export const Error = styled.p`
color: red !important;
font-size : 14px;
font-weight: bold;
`;


export const Confirmacao = styled.div`

margin-top: 20px;
padding: 15px;
border: 1px solid #ccc;
border-radius: 4px;
background-color: #f9f9f9;
color: #333;
text-align: center;


`;




export const PageShell = styled.div`
  display: block;
`;

export const PageLayout = styled.div`
  display: flex;
  min-height: calc(100vh - 70px);
`;
