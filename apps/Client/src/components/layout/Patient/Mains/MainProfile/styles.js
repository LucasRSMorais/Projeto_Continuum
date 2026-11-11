import styled from 'styled-components';


export const Title = styled.h1`
  font-size: 1.5rem;
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
` 

export const CardContainer = styled.div`
    background-color: white;

    padding: 2.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    text-align: left;
    
    /* Responsividade: largura máxima e margem de segurança */
    height: 100%;
    width: 100%;
    max-width: 94%;
    margin: 20px;
`;

export const SubCardContainer = styled.div`
    background-color: white;
    padding: 2.5rem;
    border-radius: 10px;
    text-align: left;
    border: 2px solid #dbdddd;
    margin: 30px 0;
`

export const SubCardHeader = styled.div`
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 1rem;
    margin: -1rem 0 1.5rem -1rem;

    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 16px;
`

export const ProfileForm = styled.form`
    display: flex;
    flex-direction: column;
    gap: 24px;
`

export const ActionButtons = styled.div`
    display: flex;
    justify-content: flex-end;
    gap: 12px;

    button {
        width: auto;
        min-width: 110px;
    }

    @media (max-width: 480px) {
        button {
            flex: 1;
            min-width: 0;
        }
    }
`

export const ProfileMessage = styled.p`
    margin: 0;
    color: ${({ $error }) => ($error ? '#b42318' : '#067647')};
`

export const DataCard = styled.div`
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
    gap: 24px 20px;
    align-items: start;

    p {
        margin: 0;
    }
`
