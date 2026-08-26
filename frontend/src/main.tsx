import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import '@fontsource-variable/quicksand'
import './styles/tokens.css'
import './styles/base.css'
import './styles/glass.css'
import './styles/forms.css'
import './styles/sidebar.css'
import './styles/kit.css'
import './styles/catalog.css'
import './styles/utils.css'
import App from './App.tsx'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
