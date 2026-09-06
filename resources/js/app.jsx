import React from 'react';
import { createRoot } from 'react-dom/client';
import CustomerApp from './CustomerApp';

const rootElement = document.getElementById('app');

if (rootElement) {
    createRoot(rootElement).render(
        <React.StrictMode>
            <CustomerApp />
        </React.StrictMode>
    );
}
