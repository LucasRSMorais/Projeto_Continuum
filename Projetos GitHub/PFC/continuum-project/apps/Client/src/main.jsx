import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import Login from './pages/Login'
import MyGlobalStyles from './styles/globalStyles'

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <MyGlobalStyles />
    <Login />
  </StrictMode>,
)