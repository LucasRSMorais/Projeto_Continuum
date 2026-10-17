import styled from 'styled-components';

export const HeaderContainer = styled.header`
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    height: 70px;
    padding: 0 40px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
`

export const Logo = styled.img`
    height: 100px;
    width: auto;
`

export const Nav = styled.nav`
    > ul {
        display: flex;
        align-items: center;
        list-style: none;
    }

    > ul > li > .nav-link img {
        display: block;
        width: 23px;
        height: 23px;
        object-fit: contain;
    }

    > ul > li > .nav-link[title='Perfil'] img {
        width: 35px;
        height: 35px;
        border: 2px solid #045959;
        border-radius: 50%; 
    }

    > ul > li {
        display: flex;
        align-items: center;
        padding: 0 28px;
        border-right: 1px solid #d5d5d5;
    }

    > ul > li:last-child {
        padding-right: 0;
        border-right: 0;
    }
`;
export const NavLink = styled.a`
    text-decoration: none;
    display: flex;
    align-items: center;

    img {
        display: block;
        width: 23px;
        height: 23px;
    }

    &#perfil img{
        width: 35px;
        height: 35px;
    }
`

// ESTILOS DO DROPDOWN MENU

export const Dropdown = styled.div`
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 15px;
    background: #ffffff;
    border: 1px solid #e0e6ed;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 230px;
    z-index: 1000;
    
    ul {
        display: block;
        padding: 0;
        margin: 0;
        list-style: none;
    }
`

export const MenuSection = styled.div`
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #e0e6ed;

    &:last-child {
        border-bottom: none;
    }

    .section-title {
        background-color: #eef2f7;
        color: #5c7596;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }

    ul {
        padding: 8px 0;
    }


  a {
   
    text-decoration: none;
    color : black ;
  }


`

export const MenuItem = styled.li`
    && {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        cursor: pointer;
        border: 0;
        width: 100%;
        transition: background-color 0.2s ease;
        
        &:hover {
            background-color: #f4f6f9;
        }

        .icon {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            opacity: 0.7;
        }

        span {
            font-size: 14px;
            color: #334155;
        }
    }
`

export const Badge = styled.span`
    margin-left: auto;
    background-color: ${props => props.color || '#94a3b8'};
    color: #fff !important;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px !important;
    font-weight: bold;
    line-height: 1;
`