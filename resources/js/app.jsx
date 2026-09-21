import React from 'react';
import { createRoot } from 'react-dom/client';
import CustomerApp from './CustomerApp';
import { initAutoUpdater } from './utils/autoUpdater';

// Inisialisasi background auto-update checker (hanya aktif di production)
initAutoUpdater();

const rootElement = document.getElementById('app');

if (rootElement) {
    createRoot(rootElement).render(
        <React.StrictMode>
            <CustomerApp />
        </React.StrictMode>
    );
}