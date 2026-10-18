import styled from 'styled-components';

export const Container = styled.aside`
  flex: 0 0 240px;
  padding: 28px 16px;
  background-color: #045959;
  color: #fff;

  @media (max-width: 640px) {
    flex-basis: 150px;
    padding: 20px 10px;
  }
`;

export const Heading = styled.h2`
  margin: 0 12px 20px;
  font-size: 1.1rem;
  border-bottom: 2px solid #fff;
`;

export const Menu = styled.nav`
  ul {
    display: grid;
    gap: 8px;
    padding: 0;
    list-style: none;
  }

  a {
    display: block;
    padding: 12px;
    border-radius: 6px;
    color: inherit;
    text-decoration: none;
  }

  a:hover,
  a:focus-visible {
    background-color: rgba(255, 255, 255, 0.16);
  }
`;
