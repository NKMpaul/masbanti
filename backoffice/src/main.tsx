import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import 'antd/dist/reset.css'
import App from './App'

window.Pusher = Pusher

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: 'masbanti-key',
  wsHost: 'localhost',
  wsPort: 8080,
  wssPort: 8080,
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
})

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>
)