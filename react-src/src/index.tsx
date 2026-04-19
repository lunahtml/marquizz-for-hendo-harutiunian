import React from 'react';
import { createRoot } from '@wordpress/element';
import App from './App';
import './styles/global.css';

// Админка — рендерим только если есть контейнеры админки
const adminContainers = [
    'survey-sphere-editor',
    'survey-sphere-questions-root',
    'survey-sphere-surveys-root'
];

for (const id of adminContainers) {
    const container = document.getElementById(id);
    if (container) {
        const root = createRoot(container);
        root.render(<App />);
        break;
    }
}