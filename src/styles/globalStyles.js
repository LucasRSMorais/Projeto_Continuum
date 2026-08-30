import {createGlobalStyle} from 'styled-components'

// Estilos globais para o programa, aplicados em toda a aplicação

const MyGlobalStyle = createGlobalStyle`
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    width: 100vw;
    height: 100vh;
    background-color: #f5f5f5;
    font-family: 'Roboto', sans-serif;
  }
`

export default MyGlobalStyle