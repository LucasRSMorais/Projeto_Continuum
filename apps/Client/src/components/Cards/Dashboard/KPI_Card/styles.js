import styled from 'styled-components';


export const CardContainer = styled.div`
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
` 

export const KpiCard = styled.div`
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 230px;
    height: 120px;
    padding: 16px;
    background-color: ${({ $backgroundColor }) => $backgroundColor || '#fff'};
    border: 1px solid #d5d5d5;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);

    > img {
        position: absolute;
        top: 8px;
        right: 10px;
        width: 25px;
        height: 25px;
        background-color: #f3f3f3;
        color: black;
        border-radius: 4px;
    }

    > h3 {
        font-size: solid 0.875rem;
        color: #f1f1f1;
    }

    > span {
        font-size: 0.9rem;
        color: #f2f2f2;
    }

    > p {
        position: absolute;
        bottom: 16px;
        left: 16px;
        margin: 0;
        padding: 3px 12px;
        min-width: 10px;
        text-align: center;
        font-size: 0.8rem;
        background-color: #f3f3f3;
        color: black;
        border-radius: 4px;
    }
`;