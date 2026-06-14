import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { HashRouter } from 'react-router-dom';
import App from './App';
import { AuthProvider } from './contexts/AuthContext';
import { publicAsset } from './lib/publicAsset';
import './index.css';
import './legacy-styles.css';

document.documentElement.style.setProperty(
  '--img-header-texture',
  `url("${publicAsset('img/header-texture.svg')}")`,
);

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <HashRouter>
      <AuthProvider>
        <App />
      </AuthProvider>
    </HashRouter>
  </StrictMode>,
);
